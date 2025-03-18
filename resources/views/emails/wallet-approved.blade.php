<!DOCTYPE html>
<html>

<head>
    <title>Wallet Request Approved</title>
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
    </style>
</head>

<body>
    <div class="header">
        <h1>Campus Connect</h1>
    </div>
    <div class="content">
        <h2>Dear {{ $transaction->wallet->user->first_name }},</h2>

        @if ($transaction->reference_type === 'verification')
            <p>Your wallet verification request has been approved! Your Campus Connect wallet is now active and ready to
                use.</p>
            <p>You can now add funds to your wallet and start selling on our platform.</p>
        @elseif ($transaction->reference_type === 'withdrawal')
            <p>Your withdrawal request of <strong>₱{{ number_format($transaction->amount, 2) }}</strong> has been
                approved and is now being processed.</p>
            <p>The funds will be transferred to your GCash account within 24-48 hours. We'll notify you once the
                transfer is complete.</p>
            <p>Please note that your wallet balance will be updated once the transfer has been completed.</p>
        @else
            <p>Your wallet {{ $transaction->reference_type }} request of
                <strong>₱{{ number_format($transaction->amount, 2) }}</strong> has been approved.
            </p>
            <p>New Balance: <strong>₱{{ number_format($transaction->new_balance, 2) }}</strong></p>
        @endif

        <p>Transaction ID: <strong>{{ $transaction->id }}</strong></p>
        <p>Date: <strong>{{ $transaction->processed_at->format('F j, Y g:i A') }}</strong></p>

        <a href="{{ url('/dashboard/seller/wallet') }}" class="button">View My Wallet</a>

        <p>Thank you for using Campus Connect!</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Campus Connect. All rights reserved.</p>
    </div>
</body>

</html>
