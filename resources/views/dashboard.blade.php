@extends('layouts.app')

@section('title', 'Dashboard - SSO')

@section('content')
    <h1>Chào mừng!</h1>
    <p class="subtitle">Bạn đã đăng nhập thành công vào hệ thống SSO</p>

    <div style="background: #f5f5f5; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <p><strong>Tên:</strong> {{ Auth::user()->name }}</p>
        <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
        <p><strong>TOTP:</strong> {{ Auth::user()->hasTotpEnabled() ? 'Đã kích hoạt' : 'Chưa kích hoạt' }}</p>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="btn">Đăng xuất</button>
    </form>
@endsection
