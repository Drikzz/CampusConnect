<!DOCTYPE html>
<html>
<head>
    <title>Wallet Request Rejected</title>
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
        .reason {
            background-color: #f8f8f8;
            padding: 15px;
            border-left: 4px solid #8D0A0A;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Campus Connect</h1>
    </div>
    <div class="content">
        <h2>Dear {{ $transaction->user->first_name }},</h2>
        
        @if($transaction->reference_type === 'verification')
            <p>Unfortunately, your wallet verification request has been rejected.</p>
        @else
            <p>Unfortunately, your wallet {{ $transaction->reference_type }} request of <strong>₱{{ number_format($transaction->amount, 2) }}</strong> was rejected.</p>
        @endif

        <div class="reason">
            <p><strong>Reason:</strong> {{ $transaction->remarks ?: 'No specific reason provided.' }}</p>
        </div>

        <p>Transaction ID: <strong>{{ $transaction->reference_id }}</strong></p>
        <p>Date: <strong>{{ $transaction->processed_at->format('F j, Y g:i A') }}</strong></p>
        
        <p>If you have any questions or need assistance, please contact our support team.</p>
        
        <a href="{{ url('/dashboard/seller/wallet') }}" class="button">Go to My Wallet</a>
        
        <p>Thank you for using Campus Connect!</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Campus Connect. All rights reserved.</p>
    </div>
</body>
</html>
