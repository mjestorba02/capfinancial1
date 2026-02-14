<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Approved</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; max-width: 520px; margin: 0 auto; padding: 20px; }
        .highlight { background: #f0f4f8; padding: 16px 20px; border-radius: 8px; margin: 20px 0; }
        .btn { display: inline-block; background: #2563eb; color: #fff !important; text-decoration: none; padding: 12px 24px; border-radius: 8px; margin-top: 12px; }
        .btn:hover { background: #1d4ed8; }
        .note { font-size: 14px; color: #666; margin-top: 24px; }
    </style>
</head>
<body>
    <p>Hello {{ $name }},</p>
    <p>Your account on <strong>{{ config('app.name') }}</strong> has been approved.</p>
    @if($portalType === 'employee')
        <p>You can now log in to the employee portal.</p>
    @else
        <p>You can now log in to the application.</p>
    @endif
    <div class="highlight">
        <a href="{{ $loginUrl }}" class="btn">Log in</a>
    </div>
    <p class="note">If you have any questions, please contact your administrator.</p>
    <p>— {{ config('app.name') }}</p>
</body>
</html>
