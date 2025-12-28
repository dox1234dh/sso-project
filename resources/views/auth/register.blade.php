@extends('layouts.app')

@section('title', 'Đăng ký - SSO')

@section('content')
    <h1>Đăng ký tài khoản</h1>
    <p class="subtitle">Tạo tài khoản mới để sử dụng hệ thống</p>

    <form id="registerForm">
        <div class="form-group">
            <label for="name">Họ và tên</label>
            <input type="text" id="name" name="name" required>
            <div class="error" id="nameError"></div>
        </div>

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

        <div class="form-group">
            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
            <div class="error" id="passwordConfirmationError"></div>
        </div>

        <div class="error" id="formError"></div>

        <button type="submit" class="btn" id="submitBtn">Đăng ký</button>
        <div class="loading" id="loading">Đang xử lý...</div>
    </form>

    <div class="link">
        Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#registerForm').on('submit', function(e) {
                e.preventDefault();

                hideError('#nameError');
                hideError('#emailError');
                hideError('#passwordError');
                hideError('#passwordConfirmationError');
                hideError('#formError');

                $('#submitBtn').prop('disabled', true);
                $('#loading').show();

                $.ajax({
                    url: '/register',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            window.location.href = '/totp/setup';
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false);
                        $('#loading').hide();

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.name) showError('#nameError', errors.name[0]);
                            if (errors.email) showError('#emailError', errors.email[0]);
                            if (errors.password) showError('#passwordError', errors.password[0]);
                        } else {
                            showError('#formError', 'Có lỗi xảy ra, vui lòng thử lại');
                        }
                    }
                });
            });
        });
    </script>
@endsection
