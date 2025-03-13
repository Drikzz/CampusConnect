<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wallet Transaction Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        .logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .header {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
            color: #8D0A0A;
        }

        .receipt-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            margin-bottom: 15px;
        }

        .label {
            font-weight: bold;
            color: #666;
            margin-bottom: 5px;
            font-size: 12px;
        }

        .value {
            font-size: 14px;
        }

        .big-value {
            font-size: 20px;
            font-weight: bold;
            color: #8D0A0A;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: gray;
            text-align: center;
        }

        .divider {
            border-top: 1px dashed #ddd;
            margin: 20px 0;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #047857;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .reference-section {
            margin-top: 30px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="logo">
            <h1>Campus Connect</h1>
        </div>

        <div class="header">Transaction Receipt</div>

        <div class="receipt-box">
            <!-- Transaction Type and Status -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <div class="label">Transaction Type</div>
                    <div class="value">{{ ucfirst($transaction->reference_type) }}</div>
                </div>

                <div style="text-align: right;">
                    <div class="label">Status</div>
                    <div
                        class="status 
                        {{ strtolower($transaction->status) === 'completed' ? 'status-completed' : '' }}
                        {{ strtolower($transaction->status) === 'pending' ? 'status-pending' : '' }}
                        {{ strtolower($transaction->status) === 'rejected' ? 'status-rejected' : '' }}">
                        {{ strtoupper($transaction->status) }}
                    </div>
                </div>
            </div>

            <!-- Amount Section -->
            <div style="text-align: center; margin-bottom: 30px;">
                <div class="label">Amount</div>
                <div class="big-value">₱{{ number_format(floatval($transaction->amount), 2) }}</div>
            </div>

            <div class="divider"></div>

            <!-- Detailed Information -->
            <div class="details-grid">
                <div class="detail-item">
                    <div class="label">Transaction ID</div>
                    <div class="value">{{ $transaction->id }}</div>
                </div>

                <div class="detail-item">
                    <div class="label">Reference ID</div>
                    <div class="value">{{ $transaction->reference_id }}</div>
                </div>

                <div class="detail-item">
                    <div class="label">Date</div>
                    <div class="value">{{ $transaction->created_at->format('F j, Y g:i A') }}</div>
                </div>

                @if ($transaction->processed_at)
                    <div class="detail-item">
                        <div class="label">Processed Date</div>
                        <div class="value">{{ $transaction->processed_at->format('F j, Y g:i A') }}</div>
                    </div>
                @endif

                <div class="detail-item">
                    <div class="label">Previous Balance</div>
                    <div class="value">₱{{ number_format(floatval($transaction->previous_balance ?? 0), 2) }}</div>
                </div>

                <div class="detail-item">
                    <div class="label">New Balance</div>
                    <div class="value">₱{{ number_format(floatval($transaction->new_balance ?? 0), 2) }}</div>
                </div>
            </div>

            @if ($transaction->description)
                <div class="reference-section">
                    <div class="label">Description</div>
                    <div class="value">{{ $transaction->description }}</div>
                </div>
            @endif

            @if ($transaction->remarks)
                <div class="reference-section">
                    <div class="label">Remarks</div>
                    <div class="value">{{ $transaction->remarks }}</div>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>This is an automatically generated receipt and is valid without a signature.</p>
            <p>Thank you for using Campus Connect!</p>
            <p>Generated on: {{ now()->format('F j, Y g:i A') }}</p>
        </div>
    </div>
</body>

</html>
