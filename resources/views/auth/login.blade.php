@extends('layouts.app')

@section('title', 'Đăng nhập - SSO')

@section('content')
    <h1>Đăng nhập</h1>
    <p class="subtitle">Nhập thông tin để đăng nhập vào hệ thống</p>

    <form id="loginForm">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <div class="error" id="emailError"></div>
        </div>

        <div class="form-group">
            <label for="password">Mật khẩu</label>
            <input type="password" id="password" name="password" required>
            <div class="error" id="passwordError"></div>
        </div>

        <div class="error" id="formError"></div>

        <button type="submit" class="btn" id="submitBtn">Đăng nhập</button>
        <div class="loading" id="loading">Đang xử lý...</div>
    </form>

    <div class="link">
        Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();

                hideError('#emailError');
                hideError('#passwordError');
                hideError('#formError');

                $('#submitBtn').prop('disabled', true);
                $('#loading').show();

                $.ajax({
                    url: '/login',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.totp_required) {
                            window.location.href = '/totp/verify';
                        } else if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false);
                        $('#loading').hide();

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.email) showError('#emailError', errors.email[0]);
                            if (errors.password) showError('#passwordError', errors.password[0]);
                        } else if (xhr.status === 401) {
                            showError('#formError', xhr.responseJSON.message);
                        } else {
                            showError('#formError', 'Có lỗi xảy ra, vui lòng thử lại');
                        }
                    }
                });
            });
        });
    </script>
@endsection
