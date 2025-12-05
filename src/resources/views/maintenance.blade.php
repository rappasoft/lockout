<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        h1 {
            color: #333;
            font-size: 32px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        p {
            color: #666;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .message {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 16px 20px;
            border-radius: 4px;
            margin-top: 20px;
            text-align: left;
        }
        .message strong {
            color: #667eea;
            display: block;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1>Application in Read-Only Mode</h1>
        <p>We're currently performing maintenance. The application is in read-only mode to protect your data.</p>
        @if(isset($message))
        <div class="message">
            <strong>Status:</strong>
            <span>{{ $message }}</span>
        </div>
        @endif
        <p style="margin-top: 30px; font-size: 14px; color: #999;">
            Please check back soon. We apologize for any inconvenience.
        </p>
    </div>
</body>
</html>

