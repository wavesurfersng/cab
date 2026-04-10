<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        header h1 {
            text-align: center;
            font-size: 2.5rem;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .checkout-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .order-summary {
            margin-bottom: 30px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .total-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .total-row.final {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            border-top: 2px solid #ddd;
            padding-top: 15px;
            margin-top: 15px;
        }

        .payment-methods {
            margin-top: 30px;
        }

        .payment-methods h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            margin-bottom: 15px;
        }

        .btn-paystack {
            background: linear-gradient(135deg, #0eb5b5 0%, #00d4aa 100%);
            color: white;
        }

        .btn-paystack:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(14, 181, 181, 0.4);
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>🛒 Checkout</h1>
        </div>
    </header>

    <div class="container">
        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="checkout-section">
            <h2 style="margin-bottom: 20px; color: #2c3e50;">Order Summary</h2>
            
            <div class="order-summary">
                @foreach($cart as $item)
                    <div class="order-item">
                        <div class="item-info">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="item-image">
                            <div>
                                <strong>{{ $item['name'] }}</strong>
                                <br>
                                <small>Quantity: {{ $item['quantity'] }}</small>
                            </div>
                        </div>
                        <div style="font-weight: 600; color: #667eea;">
                            ${{ number_format($item['price'] * $item['quantity'], 2) }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="total-section">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>${{ number_format($cartTotal, 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Tax:</span>
                    <span>$0.00</span>
                </div>
                <div class="total-row final">
                    <span>Total:</span>
                    <span>${{ number_format($cartTotal, 2) }}</span>
                </div>
            </div>

            <div class="payment-methods">
                <h3>Choose Payment Method</h3>
                
                <a href="{{ route('paystack.index') }}" class="btn btn-paystack">
                    💳 Pay with Paystack
                </a>
                
                <a href="{{ route('shop.index') }}" class="btn btn-back">
                    ← Back to Shop
                </a>
            </div>
        </div>
    </div>
</body>
</html>
