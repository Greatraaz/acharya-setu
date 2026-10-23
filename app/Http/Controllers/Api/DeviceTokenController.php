<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token'    => 'required|string|min:20|max:512',
            'platform' => 'nullable|in:web,android,ios',
        ]);

        $record = DeviceToken::register(
            $request->user(),
            $data['token'],
            $data['platform'] ?? DeviceToken::PLATFORM_WEB
        );

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Device registered for push notifications.',
            'device'     => [
                'id'       => $record->id,
                'platform' => $record->platform,
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|max:512',
        ]);

        DeviceToken::query()
            ->where('user_id', $request->user()->id)
            ->where('token', $data['token'])
            ->delete();

        return response()->json([
            'status'     => true,
            'statuscode' => 200,
            'message'    => 'Device unregistered.',
        ]);
    }
}
