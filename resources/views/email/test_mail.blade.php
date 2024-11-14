<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Billbae</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo img {
            max-width: 200px;
        }

        .content {
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <img src="https://yourwebsite.com/logo.png" alt="Billbae Logo">
        </div>
        <div class="content">
            <span>
                Dear {{ $details['user'] }},<br><br>

                Welcome to Billbae! Your account has been successfully created.<br><br>

                Below are your login credentials:<br><br>

                <strong>Username:</strong> {{ $details['username'] }}<br>
                <strong>Password:</strong> {{ $details['password'] }}<br><br>

                We recommend you change your password after logging in for the first time.<br><br>

                To access your account, please visit <a href="{{ $details['link'] }}">Billbae</a>.<br><br>

                <!-- If you have any questions or concerns, feel free to contact us at [Support Email or Phone Number]. -->
            </span>
        </div>
        <div class="footer">
            &copy; 2024 Billbae. All rights reserved.
        </div>
    </div>
</body>

</html>

<h1>Billbae</h1>
<h2</h2>