<!DOCTYPE html>
<html>

<head>
    <title>Withdrawal Completed</title>
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

        .details-box {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            border-bottom: 1px dashed #eee;
            padding-bottom: 8px;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Campus Connect</h1>
    </div>
    <div class="content">
        <h2>Dear {{ $transaction->wallet->user->first_name }},</h2>

        <p>Good news! Your withdrawal request has been <strong>completed</strong>.</p>

        <div class="details-box">
            <div class="detail-row">
                <span><strong>Amount: </strong>₱{{ number_format($transaction->amount, 2) }}</span>
            </div>
            <div class="detail-row">
                <span><strong>GCash Reference Number: </strong>{{ $transaction->reference_id }}</span>
            </div>
            <div class="detail-row">
                <span><strong>GCash Number: </strong>{{ $transaction->phone_number }}</span>
            </div>
            <div class="detail-row">
                <span><strong>New Wallet Balance: </strong>₱{{ number_format($transaction->new_balance, 2) }}</span>
            </div>
            <div class="detail-row">
                <span><strong>Transaction Date: </strong>{{ $transaction->processed_at->format('F j, Y g:i A') }}</span>
            </div>
        </div>

        <p>The funds have been sent to your GCash account. Please check your GCash app for the transferred amount.</p>

        <p>If you have any questions or haven't received your funds within 24 hours, please contact our support team.
        </p>

        <a href="{{ url('/dashboard/seller/wallet') }}" class="button">View My Wallet</a>

        <p>Thank you for using Campus Connect!</p>
    </div>
    <div class="footer">
        <p>© {{ date('Y') }} Campus Connect. All rights reserved.</p>
    </div>
</body>

</html>
