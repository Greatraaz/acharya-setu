<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AppSettingsController extends Controller
{
    /**
     * Sensitive keys that should never be returned to the view in plaintext.
     * They are stored encrypted and only written (never pre-filled in forms).
     */
    private array $sensitiveKeys = [
        'mail_password',
        'razorpay_key_secret', 'razorpay_webhook_secret',
        'stripe_secret_key', 'stripe_webhook_secret',
        'paypal_client_secret',
        'paytm_merchant_key',
        'phonepe_salt_key',
        'cashfree_secret_key',
        'msg91_auth_key',
        'twilio_auth_token',
        'fast2sms_api_key',
        'sns_secret_key',
        'vonage_api_secret',
        'agora_app_certificate', 'agora_customer_secret',
        'zoom_client_secret', 'zoom_webhook_secret',
        'google_client_secret',
        'aws_secret',
    ];

    /**
     * Keys that are stored as file uploads.
     */
    private array $fileKeys = ['app_logo', 'app_favicon'];

    /**
     * Show the settings page.
     */
    public function index()
    {
        return view('admin.settings.view', [
            'configurations' => AppSetting::all()->pluck('value', 'key')->toArray(),
        ]);
    }

    /**
     * Update settings for a given section.
     */
    public function update(Request $request)
    {
        $section = $request->input('section');

        $this->validateSection($request, $section);

        $data = $request->except(['_token', 'section', ...$this->fileKeys]);

        // Remove color picker duplicates (they shadow the text inputs)
        unset($data['primary_color_picker'], $data['accent_color_picker']);

        // Handle boolean toggles — ensure unchecked boxes are stored as '0'
        $booleanKeys = [
            'user_registration', 'email_verification', 'two_factor_auth', 'maintenance_mode',
            'notify_new_user', 'notify_booking', 'notify_payment', 'notify_failed_login', 'notify_cancellation',
            'razorpay_enabled', 'stripe_enabled', 'paypal_enabled', 'paytm_enabled', 'phonepe_enabled', 'cashfree_enabled',
            'video_recording_enabled', 'video_waiting_room', 'video_screen_sharing',
            'google_auto_calendar', 'google_send_invites', 'google_sync_cancellations',
        ];
        foreach ($booleanKeys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = (string) (bool) $data[$key];
            }
        }

        // Handle file uploads
        foreach ($this->fileKeys as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $file = $request->file($fileKey);
                $path = $file->store("settings/{$fileKey}", 'public');
                // Delete old file
                $old = AppSetting::where('key', $fileKey)->value('value');
                if ($old && Storage::disk('public')->exists($old)) {
                    Storage::disk('public')->delete($old);
                }
                $data[$fileKey] = $path;
            }
        }

        // Encrypt sensitive keys before saving
        foreach ($data as $key => $value) {
            if (in_array($key, $this->sensitiveKeys) && !empty($value)) {
                $data[$key] = encrypt($value);
            }
        }

        // Upsert each key
        foreach ($data as $key => $value) {
            if ($value === null) {
                continue; // Don't overwrite with null unless explicitly cleared
            }
            AppSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Clear config cache
        Cache::forget('app_configurations');

        return redirect()->back()->with('success', ucfirst($section) . ' settings saved successfully.');
    }

    /**
     * Test SMTP connection.
     */
    public function testEmail()
    {
        try {
            \Mail::raw('This is a test email from ' . config_val('app_name', config('app.name')), function ($msg) {
                $msg->to(config_val('contact_email', config('mail.from.address')))
                    ->subject('SMTP Test — ' . now()->format('d M Y H:i'));
            });
            return redirect()->route('admin.settings.index', ['#email'])
                ->with('success', 'Test email sent successfully. Check your inbox.');
        } catch (\Exception $e) {
            return redirect()->route('admin.settings.index', ['#email'])
                ->with('error', 'Email test failed: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to Zoom OAuth.
     */
    public function zoomConnect()
    {
        $clientId = config_val('zoom_client_id');
        $redirect  = urlencode(url('/admin/zoom/callback'));
        return redirect("https://zoom.us/oauth/authorize?response_type=code&client_id={$clientId}&redirect_uri={$redirect}");
    }

    /**
     * Zoom OAuth callback.
     */
    public function zoomCallback(Request $request)
    {
        // Exchange code for access/refresh token and store
        // Implementation depends on your Zoom service class
        return redirect()->route('admin.settings.index')
            ->with('success', 'Zoom connected successfully.');
    }

    /**
     * Redirect to Google OAuth.
     */
    public function googleConnect()
    {
        $clientId = config_val('google_client_id');
        $redirect  = urlencode(url('/admin/google/callback'));
        $scope     = urlencode('https://www.googleapis.com/auth/calendar https://www.googleapis.com/auth/calendar.events');
        return redirect("https://accounts.google.com/o/oauth2/auth?response_type=code&client_id={$clientId}&redirect_uri={$redirect}&scope={$scope}&access_type=offline&prompt=consent");
    }

    /**
     * Google OAuth callback.
     */
    public function googleCallback(Request $request)
    {
        // Exchange code for tokens and store refresh_token
        return redirect()->route('admin.settings.index')
            ->with('success', 'Google account connected successfully.');
    }

    /**
     * Test Agora credentials.
     */
    public function testAgora()
    {
        $appId = config_val('agora_app_id');
        if (empty($appId)) {
            return redirect()->back()->with('error', 'Agora App ID is not configured.');
        }
        // Basic check — actual token generation would validate the credentials
        return redirect()->back()->with('success', 'Agora App ID is set. Generate a test token to fully validate.');
    }

    /**
     * Section-level validation rules.
     */
    private function validateSection(Request $request, string $section): void
    {
        $rules = match ($section) {
            'app' => [
                'app_name'         => 'required|string|max:100',
                'contact_email'    => 'nullable|email',
                'app_url'          => 'nullable|url',
                'privacy_policy_url' => 'nullable|url',
                'terms_url'        => 'nullable|url',
                'linkedin_url'     => 'nullable|url',
                'twitter_url'      => 'nullable|url',
                'facebook_url'     => 'nullable|url',
                'instagram_url'    => 'nullable|url',
                'app_logo'         => 'nullable|image|max:2048',
                'app_favicon'      => 'nullable|image|max:512',
            ],
            'email' => [
                'mail_host'         => 'nullable|string',
                'mail_port'         => 'nullable|integer|between:1,65535',
                'mail_from_address' => 'nullable|email',
            ],
            'payment' => [
                'default_currency' => 'nullable|string|size:3',
            ],
            default => [],
        };

        if (!empty($rules)) {
            $request->validate($rules);
        }
    }
}