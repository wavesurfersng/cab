<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful!</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 15px;
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-icon svg {
            width: 60px;
            height: 60px;
            color: white;
        }

        h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #7f8c8d;
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #7f8c8d;
            font-weight: 500;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 600;
        }

        .reference-number {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-family: monospace;
            font-size: 1.1rem;
            color: #1976d2;
        }

        .btn {
            display: inline-block;
            padding: 15px 40px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .items-list {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
        }

        .items-list h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            color: #7f8c8d;
        }

        @media (max-width: 480px) {
            .success-container {
                padding: 30px 20px;
            }

            h1 {
                font-size: 2rem;
            }

            .btn {
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h1>Payment Successful!</h1>
        <p class="subtitle">Thank you for your purchase</p>

        <div class="order-details">
            <div class="detail-row">
                <span class="detail-label">Transaction ID:</span>
                <span class="detail-value">#{{ $orderData['transaction_id'] }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Amount Paid:</span>
                <span class="detail-value">${{ number_format($orderData['amount'], 2) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Method:</span>
                <span class="detail-value">{{ ucfirst($orderData['channel']) }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Email:</span>
                <span class="detail-value">{{ $orderData['email'] }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Payment Date:</span>
                <span class="detail-value">{{ date('F j, Y g:i A', strtotime($orderData['paid_at'])) }}</span>
            </div>

            <div class="reference-number">
                Reference: {{ $orderData['reference'] }}
            </div>

            @if(isset($orderData['items']) && count($orderData['items']) > 0)
                <div class="items-list">
                    <h3>Items Purchased:</h3>
                    @foreach($orderData['items'] as $item)
                        <div class="item">
                            <span>{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                            <span>${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <p style="color: #7f8c8d; margin-bottom: 25px; line-height: 1.6;">
            A confirmation email has been sent to <strong>{{ $orderData['email'] }}</strong>. 
            Please save your reference number for future inquiries.
        </p>

        <div>
            <a href="{{ route('shop.index') }}" class="btn btn-primary">Continue Shopping</a>
            <a href="#" onclick="window.print(); return false;" class="btn btn-secondary">Print Receipt</a>
        </div>
    </div>

    <script>
        // Auto-print option (optional)
        // setTimeout(() => {
        //     if (confirm('Would you like to print your receipt?')) {
        //         window.print();
        //     }
        // }, 1000);
    </script>
</body>
</html>
