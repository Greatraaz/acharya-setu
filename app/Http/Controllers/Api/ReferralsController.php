<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Referral;
use Illuminate\Http\{Request, JsonResponse};

class ReferralsController extends Controller
{
    
    public function myCode(Request $request): JsonResponse
    {
        $u    = $request->user();
        $code = strtoupper(substr(preg_replace('/[^A-Z]/', '', $u->name), 0, 6) . rand(100, 999));
        Referral::firstOrCreate(
            ['referrer_id' => $u->id, 'referred_id' => null, 'code' => $code],
            ['bonus_amount' => 250, 'is_rewarded' => false]
        );
        return response()->json([
            'code'      => $code,
            'referrals' => Referral::where('referrer_id', $u->id)
                ->whereNotNull('referred_id')
                ->with('referred:id,name,created_at')
                ->get(),
        ]);
    }
}
