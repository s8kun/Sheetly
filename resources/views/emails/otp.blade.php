<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 20px;
        }
        .header {
            font-size: 22px;
            color: #4a5568;
            margin-bottom: 20px;
        }
        .otp-code {
            font-size: 48px;
            font-weight: bold;
            letter-spacing: 10px;
            color: #3182ce;
            background-color: #ebf8ff;
            padding: 15px;
            border-radius: 8px;
            display: inline-block;
            margin: 20px 0;
        }
        .footer {
            font-size: 14px;
            color: #a0aec0;
            margin-top: 30px;
        }
        .expiry {
            color: #e53e3e;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">شيتاتي</div>
        <div class="header">رمز تأكيد الحساب</div>
        <p>مرحباً بك، استخدم الرمز التالي لتفعيل حسابك في شيتاتي:</p>
        <div class="otp-code">{{ $code }}</div>
        <p>هذا الرمز صالح لمدة <span class="expiry">5 دقائق</span> فقط.</p>
        <p>إذا لم تطلب هذا الرمز، يمكنك تجاهل هذا البريد الإلكتروني بأمان.</p>
        <div class="footer">
            &copy; {{ date('Y') }} شيتاتي. جميع الحقوق محفوظة.
        </div>
    </div>
</body>
</html>
