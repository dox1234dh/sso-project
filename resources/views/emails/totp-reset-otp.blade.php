<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mã OTP đặt lại TOTP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: #f9f9f9;
            border-radius: 8px;
            padding: 30px;
        }
        h1 {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .otp-code {
            background: #667eea;
            color: white;
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 5px;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Mã OTP đặt lại TOTP</h1>

    <p>Xin chào,</p>

    <p>Bạn đã yêu cầu đặt lại mã TOTP cho tài khoản của mình. Vui lòng sử dụng mã OTP dưới đây để xác thực:</p>

    <div class="otp-code">
        {{ $otp }}
    </div>

    <div class="warning">
        <strong>Lưu ý:</strong> Mã này sẽ hết hạn sau 10 phút. Nếu bạn không yêu cầu đặt lại TOTP, vui lòng bỏ qua email này.
    </div>

    <p>Trân trọng,<br>{{ config('app.name') }}</p>

    <div class="footer">
        Đây là email tự động, vui lòng không trả lời email này.
    </div>
</div>
</body>
</html>
