<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Transaction Receipt</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            padding: 15px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid {{ $primary_color ?? '#20509e' }};
            padding-bottom: 10px;
        }

        .header h1 {
            color: {{ $primary_color ?? '#20509e' }};
            margin: 0;
            font-size: 20px;
        }

        .receipt-details {
            margin-bottom: 20px;
        }

        .receipt-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .receipt-details table td {
            padding: 5px 8px;
            vertical-align: top;
            border-bottom: 1px solid #eee;
        }

        .receipt-details table td:first-child {
            font-weight: bold;
            width: 40%;
            color: {{ $primary_color ?? '#20509e' }};
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .status-completed {
            color: #22c55e;
            font-weight: bold;
        }

        .status-pending {
            color: #f59e0b;
            font-weight: bold;
        }

        .status-rejected {
            color: #ef4444;
            font-weight: bold;
        }

        .status-in_process {
            color: #3b82f6;
            font-weight: bold;
        }

        .logo {
            max-width: 120px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="Campus Connect Logo" class="logo">
            <h1>Transaction Receipt</h1>
            <p>#{{ $transaction->id }}</p>
        </div>

        <div class="receipt-details">
            <table>
                <tr>
                    <td>Transaction Type</td>
                    <td>
                        @if (!$transaction->reference_type)
                            Wallet Activation
                        @elseif($transaction->reference_type == 'verification')
                            Verification
                        @elseif($transaction->reference_type == 'refill')
                            Add Funds
                        @elseif($transaction->reference_type == 'withdrawal')
                            Withdrawal
                        @else
                            {{ ucfirst($transaction->reference_type) }}
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td class="status-{{ strtolower($transaction->status) }}">
                        {{ strtoupper($transaction->status) }}
                    </td>
                </tr>
                <tr>
                    <td>Amount</td>
                    <td>₱{{ number_format((float) $transaction->amount, 2) }}</td>
                </tr>
                <tr>
                    <td>Transaction ID</td>
                    <td>{{ $transaction->id }}</td>
                </tr>
                <tr>
                    <td>Reference ID</td>
                    <td>{{ $transaction->reference_id }}</td>
                </tr>
                <tr>
                    <td>Date</td>
                    <td>{{ date('F j, Y g:i A', strtotime($transaction->created_at)) }}</td>
                </tr>
                @if ($transaction->processed_at)
                    <tr>
                        <td>Processed Date</td>
                        <td>{{ date('F j, Y g:i A', strtotime($transaction->processed_at)) }}</td>
                    </tr>
                @endif

                <!-- Previous and New Balance - Only show actual balance change if not rejected -->
                @if ($transaction->status !== 'rejected')
                    <tr>
                        <td>Previous Balance</td>
                        <td>₱{{ number_format((float) $transaction->previous_balance, 2) }}</td>
                    </tr>
                    <tr>
                        <td>New Balance</td>
                        <td>₱{{ number_format((float) $transaction->new_balance, 2) }}</td>
                    </tr>
                @else
                    <tr>
                        <td>Current Balance</td>
                        <td>₱{{ number_format((float) $transaction->previous_balance, 2) }}</td>
                    </tr>
                @endif

                @if ($transaction->description)
                    <tr>
                        <td>Description</td>
                        <td>{{ $transaction->description }}</td>
                    </tr>
                @endif

                @if ($transaction->remarks)
                    <tr>
                        <td>Remarks</td>
                        <td>{{ $transaction->remarks }}</td>
                    </tr>
                @endif

                <!-- Add GCash Phone Number if available for withdrawals -->
                @if ($transaction->reference_type == 'withdrawal' && !empty($transaction->phone_number))
                    <tr>
                        <td>GCash Phone Number</td>
                        <td>{{ $transaction->phone_number }}</td>
                    </tr>
                @endif

                <!-- Add Account Name if available for withdrawals -->
                @if ($transaction->reference_type == 'withdrawal' && !empty($transaction->account_name))
                    <tr>
                        <td>Account Name</td>
                        <td>{{ $transaction->account_name }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="footer">
            <p>This is an automatically generated receipt and is valid without a signature.</p>
            <p>Thank you for using Campus Connect!</p>
            <p>Generated on: {{ date('F j, Y g:i A') }}</p>
        </div>
    </div>
</body>

</html>
