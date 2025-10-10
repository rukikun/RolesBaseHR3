<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jetlouge Travels - Verification Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 12px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: #4285f4;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
        }
        .otp-code {
            background: #f8f9fa;
            border: 2px dashed #4285f4;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #4285f4;
            font-family: 'Courier New', monospace;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #4285f4;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">JT</div>
            <h1 style="margin: 0; font-size: 24px;">Jetlouge Travels</h1>
            <p style="margin: 5px 0 0; opacity: 0.9;">Admin Portal Security</p>
        </div>
        
        <div class="content">
            <h2 style="color: #333; margin-bottom: 10px;">Hello {{ $userName }}!</h2>
            <p style="color: #666; margin-bottom: 30px;">
                We received a request to sign in to your Jetlouge Travels admin account. 
                Please use the verification code below to complete your login:
            </p>
            
            <div class="otp-code">{{ $otpCode }}</div>
            
            <p style="color: #666; margin-bottom: 20px;">
                Enter this 6-digit code in the verification screen to access your admin dashboard.
            </p>
            
            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                • This code expires in <strong>10 minutes</strong><br>
                • Never share this code with anyone<br>
                • If you didn't request this code, please ignore this email
            </div>
            
            <p style="color: #666; font-size: 14px; margin-top: 30px;">
                Having trouble? Contact our support team or try logging in again.
            </p>
        </div>
        
        <div class="footer">
            <p style="margin: 0;">
                This is an automated security email from Jetlouge Travels Admin Portal.<br>
                Please do not reply to this email.
            </p>
            <p style="margin: 10px 0 0; font-size: 12px;">
                © {{ date('Y') }} Jetlouge Travels. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
