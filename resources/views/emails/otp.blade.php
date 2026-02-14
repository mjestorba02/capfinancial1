<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; max-width: 480px; margin: 0 auto; padding: 20px; }
        .code { font-size: 28px; font-weight: bold; letter-spacing: 8px; background: #f0f4f8; padding: 16px 24px; border-radius: 8px; text-align: center; margin: 24px 0; }
        .note { font-size: 14px; color: #666; margin-top: 24px; }
    </style>
</head>
<body>
    <p>Hello,</p>
    <p>Use this one-time code to complete your login:</p>
    <div class="code">{{ $otpCode }}</div>
    <p class="note">This code expires in 10 minutes. If you didn't request this, please ignore this email.</p>
    <p>— {{ config('app.name') }}</p>
</body>
</html>
