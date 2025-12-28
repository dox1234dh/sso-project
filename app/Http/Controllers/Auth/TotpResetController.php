<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TotpResetController extends Controller
{
    protected TotpService $totpService;

    public function __construct(TotpService $totpService)
    {
        $this->totpService = $totpService;
    }

    public function showRequestForm()
    {
        return view('auth.totp-reset-request');
    }

    public function sendResetOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users,email',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user->hasTotpEnabled()) {
                return response()->json(['message' => 'Tài khoản này chưa kích hoạt TOTP'], 400);
            }

            // Xóa OTP cũ
            DB::table('totp_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Tạo OTP mới
            $otp = $this->totpService->generateOTP();

            DB::table('totp_reset_tokens')->insert([
                'email' => $request->email,
                'token' => Hash::make($otp),
                'created_at' => now(),
                'expires_at' => now()->addMinutes(10),
            ]);

            // Gửi email
            Mail::send('emails.totp-reset-otp', ['otp' => $otp], function ($message) use ($request) {
                $message->to($request->email)
                    ->subject('Mã OTP đặt lại TOTP');
            });

            session(['totp_reset_email' => $request->email]);

            return response()->json(['success' => true, 'message' => 'Mã OTP đã được gửi đến email của bạn'], 200);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra'], 500);
        }
    }

    public function showVerifyOtpForm()
    {
        if (!session('totp_reset_email')) {
            return redirect()->route('totp.reset.request');
        }

        return view('auth.totp-reset-verify');
    }

    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = session('totp_reset_email');
        if (!$email) {
            return response()->json(['message' => 'Session hết hạn'], 401);
        }

        $resetToken = DB::table('totp_reset_tokens')
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if (!$resetToken) {
            return response()->json(['message' => 'Mã OTP không hợp lệ hoặc đã hết hạn'], 400);
        }

        if (!Hash::check($request->otp, $resetToken->token)) {
            return response()->json(['message' => 'Mã OTP không chính xác'], 401);
        }

        // Xóa token đã sử dụng
        DB::table('totp_reset_tokens')
            ->where('email', $email)
            ->delete();

        // Tạo secret mới
        $user = User::where('email', $email)->first();
        $secret = $this->totpService->generateSecret();

        session([
            'totp_reset_verified' => true,
            'totp_reset_user_id' => $user->id,
            'totp_reset_secret' => $secret,
        ]);

        return response()->json(['success' => true], 200);
    }

    public function showResetForm()
    {
        if (!session('totp_reset_verified')) {
            return redirect()->route('totp.reset.request');
        }

        $user = User::find(session('totp_reset_user_id'));
        $secret = session('totp_reset_secret');

        $qrCode = $this->totpService->getQRCodeInline($user->email, $secret);

        return view('auth.totp-reset', compact('qrCode', 'secret'));
    }

    public function confirmReset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'totp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = session('totp_reset_user_id');
        $secret = session('totp_reset_secret');

        if (!session('totp_reset_verified') || !$userId || !$secret) {
            return response()->json(['message' => 'Session hết hạn'], 401);
        }

        if (!$this->totpService->verifyKey($secret, $request->totp_code)) {
            return response()->json(['message' => 'Mã xác thực không chính xác'], 401);
        }

        $user = User::find($userId);
        $user->enableTotp($secret);

        session()->forget(['totp_reset_email', 'totp_reset_verified', 'totp_reset_user_id', 'totp_reset_secret']);

        return response()->json([
            'success' => true,
            'message' => 'TOTP đã được đặt lại thành công!',
            'redirect' => route('login')
        ], 200);
    }
}
