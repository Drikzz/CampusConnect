<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Campus Connect Seller Program</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #8D0A0A;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            border: 1px solid #ddd;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 5px 5px;
        }

        .button {
            display: inline-block;
            background-color: #8D0A0A;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }

        .next-steps {
            background-color: #f8f8f8;
            padding: 15px;
            border-left: 4px solid #8D0A0A;
            margin: 20px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Campus Connect</h1>
    </div>
    <div class="content">
        <h2>Welcome, {{ $user->first_name }}!</h2>

        <p>Congratulations! You are now registered as a seller on Campus Connect. Your seller account has been
            successfully created with the following details:</p>

        <ul>
            <li><strong>Seller Code:</strong> {{ $user->seller_code }}</li>
            <li><strong>WMSU Email:</strong> {{ $user->wmsu_email }}</li>
        </ul>

        <div class="next-steps">
            <h3>Next Steps:</h3>
            <ol>
                <li>Set up your seller wallet to start selling</li>
                <li>Add your products to your inventory</li>
                <li>Set up your meetup locations for in-person exchanges</li>
            </ol>
        </div>

        <p>With your seller account, you can now list products, manage orders, and track your sales performance. We're
            excited to have you join our community of campus sellers!</p>

        <a href="{{ url('/dashboard/seller/wallet') }}" class="button">Set Up Your Wallet</a>

        <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>

        <p>Happy selling!</p>
        <p>The Campus Connect Team</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Campus Connect. All rights reserved.</p>
    </div>
</body>

</html>
