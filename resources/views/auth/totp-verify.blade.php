@extends('layouts.app')

@section('title', 'Xác thực TOTP - SSO')

@section('content')
    <h1>Xác thực TOTP</h1>
    <p class="subtitle">Nhập mã 6 chữ số từ Google Authenticator</p>

    <form id="totpForm">
        <div class="form-group">
            <label for="totp_code">Mã xác thực</label>
            <input type="text" id="totp_code" name="totp_code" maxlength="6" pattern="[0-9]{6}" required autocomplete="off">
            <div class="error" id="totpCodeError"></div>
        </div>

        <div class="error" id="formError"></div>

        <button type="submit" class="btn" id="submitBtn">Xác nhận</button>
        <div class="loading" id="loading">Đang xử lý...</div>
    </form>

    <div class="link">
        <a href="{{ route('totp.reset.request') }}">Quên mã Secret?</a>
    </div>

    <div class="link">
        <a href="{{ route('login') }}">Quay lại đăng nhập</a>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-focus vào ô nhập
            $('#totp_code').focus();

            // Chỉ cho phép nhập số
            $('#totp_code').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            $('#totpForm').on('submit', function(e) {
                e.preventDefault();

                hideError('#totpCodeError');
                hideError('#formError');

                $('#submitBtn').prop('disabled', true);
                $('#loading').show();

                $.ajax({
                    url: '/totp/verify',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false);
                        $('#loading').hide();
                        $('#totp_code').val('').focus();

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.totp_code) showError('#totpCodeError', errors.totp_code[0]);
                        } else if (xhr.status === 401 || xhr.status === 404) {
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
