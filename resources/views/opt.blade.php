<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>{{ $subject }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 1.8em;
            color: #A3E635;
            margin: 0;
        }

        .greeting {
            font-size: 1.1em;
            margin-bottom: 20px;
        }

        .otp-box {
            text-align: center;
            background: linear-gradient(to right, #A3E635, #B6F05E, #C8FC80);
            color: #000;
            display: inline-block;
            padding: 12px 20px;
            font-size: 1.6em;
            border-radius: 8px;
            margin: 20px 0;
        }

        .message {
            font-size: 1em;
            line-height: 1.6;
        }

        .footer {
            font-size: 0.85em;
            color: #888;
            text-align: center;
            margin-top: 30px;
        }

        .email-info {
            text-align: center;
            font-size: 0.85em;
            color: #666;
            margin-top: 10px;
        }

        .email-info a {
            color: #A3E635;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>{{ $subject }}</h1>
    </div>

    <div class="greeting">
        <strong>Xin chào {{ $userName }},</strong>
    </div>

    <div class="message">
        <p>Chúng tôi đã nhận được yêu cầu đổi mật khẩu cho tài khoản của bạn.</p>
        <p>Vui lòng sử dụng mã xác minh (OTP) dưới đây để tiếp tục:</p>
    </div>

    <div class="otp-box">{{$otp}}</div>

    <div class="message">
        <p><strong>Lưu ý:</strong> Mã OTP có hiệu lực trong vòng <strong>5 phút</strong>.</p>
        <p>Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email này.</p>
        <p><strong>Không chia sẻ mã này với bất kỳ ai.</strong></p>
        <p>Trân trọng cảm ơn bạn đã sử dụng.</p>
{{--        <p>Thân ái,<br><strong>[Tên Công Ty]</strong></p>--}}
    </div>

    <div class="footer">
        <p>Đây là email tự động, vui lòng không phản hồi lại.</p>
{{--        <p>Để biết thêm thông tin, vui lòng truy cập <strong>[Tên Website]</strong>.</p>--}}
    </div>
</div>

<div class="email-info">
    <p>Email này được gửi đến: <a href="mailto:{{ env("MAIL_FROM_ADDRESS") }}">{{ env("MAIL_FROM_ADDRESS") }}</a></p>
{{--    <p><a href="/">[Tên Công Ty]</a> | [Địa chỉ] | [Phường/Xã, Quận/Huyện] - [Mã Bưu Chính], [Quốc Gia]</p>--}}
    <p>&copy; 2025. Mọi quyền được bảo lưu.</p>
</div>
</body>
</html>
