<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected TotpService $totpService;

    public function __construct(TotpService $totpService)
    {
        $this->totpService = $totpService;
    }

    public function showLogin(Request $request)
    {
        $clientId = $request->query('client_id');
        $redirectUri = $request->query('redirect_uri');
        $state = $request->query('state');
        $codeChallenge = $request->query('code_challenge');
        $codeChallengeMethod = $request->query('code_challenge_method');

        session([
            'oauth_params' => [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'state' => $state,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => $codeChallengeMethod,
            ]
        ]);

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Thông tin đăng nhập không chính xác'], 401);
        }

        $user = Auth::user();

        if ($user->hasTotpEnabled()) {
            session(['totp_user_id' => $user->id]);
            Auth::logout();
            return response()->json(['totp_required' => true], 200);
        }

        return $this->completeAuthorization($user);
    }

    public function showTotpVerification()
    {
        if (!session('totp_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.totp-verify');
    }

    public function verifyTotp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'totp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = session('totp_user_id');
        if (!$userId) {
            return response()->json(['message' => 'Session hết hạn'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['message' => 'Người dùng không tồn tại'], 404);
        }

        $secret = $user->getTotpSecret();
        if (!$this->totpService->verifyKey($secret, $request->totp_code)) {
            return response()->json(['message' => 'Mã xác thực không chính xác'], 401);
        }

        session()->forget('totp_user_id');
        Auth::login($user);

        return $this->completeAuthorization($user);
    }

    protected function completeAuthorization($user)
    {
        $oauthParams = session('oauth_params');

        if (!$oauthParams) {
            return response()->json(['message' => 'Đăng nhập thành công', 'redirect' => '/dashboard'], 200);
        }

        $query = http_build_query([
            'client_id' => $oauthParams['client_id'],
            'redirect_uri' => $oauthParams['redirect_uri'],
            'response_type' => 'code',
            'scope' => '',
            'state' => $oauthParams['state'],
            'code_challenge' => $oauthParams['code_challenge'],
            'code_challenge_method' => $oauthParams['code_challenge_method'],
        ]);

        return response()->json([
            'redirect' => '/oauth/authorize?' . $query
        ], 200);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $secret = $this->totpService->generateSecret();
        session([
            'totp_setup_user_id' => $user->id,
            'totp_setup_secret' => $secret,
        ]);

        return response()->json(['success' => true], 200);
    }

    public function showTotpSetup()
    {
        if (!session('totp_setup_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('totp_setup_user_id'));
        $secret = session('totp_setup_secret');

        $qrCode = $this->totpService->getQRCodeInline($user->email, $secret);

        return view('auth.totp-setup', compact('qrCode', 'secret'));
    }

    public function confirmTotpSetup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'totp_code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = session('totp_setup_user_id');
        $secret = session('totp_setup_secret');

        if (!$userId || !$secret) {
            return response()->json(['message' => 'Session hết hạn'], 401);
        }

        if (!$this->totpService->verifyKey($secret, $request->totp_code)) {
            return response()->json(['message' => 'Mã xác thực không chính xác'], 401);
        }

        $user = User::find($userId);
        $user->enableTotp($secret);

        session()->forget(['totp_setup_user_id', 'totp_setup_secret']);

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công! Vui lòng đăng nhập.',
            'redirect' => route('login')
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
