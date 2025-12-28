@extends('layouts.app')

@section('title', 'Xác thực OTP - SSO')

@section('content')
    <h1>Xác thực OTP</h1>
    <p class="subtitle">Nhập mã OTP đã được gửi đến email của bạn</p>

    <form id="verifyOtpForm">
        <div class="form-group">
            <label for="otp">Mã OTP (6 chữ số)</label>
            <input type="text" id="otp" name="otp" maxlength="6" pattern="[0-9]{6}" required autocomplete="off">
            <div class="error" id="otpError"></div>
        </div>

        <div class="error" id="formError"></div>

        <button type="submit" class="btn" id="submitBtn">Xác nhận</button>
        <div class="loading" id="loading">Đang xử lý...</div>
    </form>

    <div class="link">
        <a href="{{ route('totp.reset.request') }}">Gửi lại mã OTP</a>
    </div>

    <div class="link">
        <a href="{{ route('login') }}">Quay lại đăng nhập</a>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#otp').focus();

            $('#otp').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            $('#verifyOtpForm').on('submit', function(e) {
                e.preventDefault();

                hideError('#otpError');
                hideError('#formError');

                $('#submitBtn').prop('disabled', true);
                $('#loading').show();

                $.ajax({
                    url: '/totp/reset/verify',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            window.location.href = '/totp/reset/confirm';
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false);
                        $('#loading').hide();
                        $('#otp').val('').focus();

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.otp) showError('#otpError', errors.otp[0]);
                        } else if (xhr.status === 400 || xhr.status === 401) {
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
