@extends('layouts.app')

@section('title', 'Đặt lại TOTP - SSO')

@section('content')
    <h1>Đặt lại TOTP</h1>
    <p class="subtitle">Quét mã QR mới bằng Google Authenticator</p>

    <div class="qr-container">
        <img src="{{ $qrCode }}" alt="QR Code">
    </div>

    <div class="secret-code">
        <strong>Secret Key mới:</strong><br>
        {{ $secret }}
    </div>

    <p style="text-align: center; color: #666; font-size: 13px; margin-bottom: 20px;">
        Sau khi quét mã QR, nhập mã 6 chữ số để xác nhận
    </p>

    <form id="resetConfirmForm">
        <div class="form-group">
            <label for="totp_code">Mã xác thực</label>
            <input type="text" id="totp_code" name="totp_code" maxlength="6" pattern="[0-9]{6}" required autocomplete="off">
            <div class="error" id="totpCodeError"></div>
        </div>

        <div class="error" id="formError"></div>
        <div class="success" id="formSuccess"></div>

        <button type="submit" class="btn" id="submitBtn">Xác nhận</button>
        <div class="loading" id="loading">Đang xử lý...</div>
    </form>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#totp_code').focus();

            $('#totp_code').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });

            $('#resetConfirmForm').on('submit', function(e) {
                e.preventDefault();

                hideError('#totpCodeError');
                hideError('#formError');

                $('#submitBtn').prop('disabled', true);
                $('#loading').show();

                $.ajax({
                    url: '/totp/reset/confirm',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showSuccess('#formSuccess', response.message);
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 2000);
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false);
                        $('#loading').hide();
                        $('#totp_code').val('').focus();

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.totp_code) showError('#totpCodeError', errors.totp_code[0]);
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
