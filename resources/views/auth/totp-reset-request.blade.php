@extends('layouts.app')

@section('title', 'Quên Secret - SSO')

@section('content')
    <h1>Quên mã Secret</h1>
    <p class="subtitle">Nhập email để nhận mã OTP xác thực</p>

    <form id="resetRequestForm">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
            <div class="error" id="emailError"></div>
        </div>

        <div class="error" id="formError"></div>
        <div class="success" id="formSuccess"></div>

        <button type="submit" class="btn" id="submitBtn">Gửi mã OTP</button>
        <div class="loading" id="loading">Đang xử lý...</div>
    </form>

    <div class="link">
        <a href="{{ route('login') }}">Quay lại đăng nhập</a>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('#resetRequestForm').on('submit', function(e) {
                e.preventDefault();

                hideError('#emailError');
                hideError('#formError');

                $('#submitBtn').prop('disabled', true);
                $('#loading').show();

                $.ajax({
                    url: '/totp/reset',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showSuccess('#formSuccess', response.message);
                            setTimeout(function() {
                                window.location.href = '/totp/reset/verify';
                            }, 2000);
                        }
                    },
                    error: function(xhr) {
                        $('#submitBtn').prop('disabled', false);
                        $('#loading').hide();

                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            if (errors.email) showError('#emailError', errors.email[0]);
                        } else if (xhr.status === 400) {
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
