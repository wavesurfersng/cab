<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Paystack Payment</title>
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
            max-width: 900px;
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

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
        }

        .section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .section h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .order-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-total {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: white;
            border-radius: 5px;
            margin-top: 15px;
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
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
            margin-top: 15px;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        .paystack-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .paystack-logo img {
            max-width: 200px;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-radius: 5px;
            color: #2e7d32;
        }

        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>💳 Secure Checkout</h1>
        </div>
    </header>

    <div class="container">
        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <form id="checkout-form">
            @csrf
            <input type="hidden" name="amount" value="{{ $cartTotal }}">
            
            <div class="checkout-grid">
                <!-- Customer Information -->
                <div class="section">
                    <h2>👤 Customer Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">First Name *</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="{{ old('first_name', $firstName) }}" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="last_name">Last Name *</label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="{{ old('last_name', $lastName) }}" 
                                   required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $email) }}" 
                               required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone', $phone) }}" 
                               required>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="section">
                    <h2>📦 Order Summary</h2>
                    
                    <div class="order-summary">
                        @foreach($cart as $item)
                            <div class="order-item">
                                <div>
                                    <strong>{{ $item['name'] }}</strong>
                                    <br>
                                    <small>Qty: {{ $item['quantity'] }}</small>
                                </div>
                                <div>
                                    ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="order-total">
                        <span>Total Amount:</span>
                        <span>${{ number_format($cartTotal, 2) }}</span>
                    </div>

                    <div class="paystack-logo">
                        <svg width="200" height="60" viewBox="0 0 200 60">
                            <rect fill="#0eb5b5" width="200" height="60" rx="5"/>
                            <text x="100" y="35" font-family="Arial" font-size="20" font-weight="bold" fill="white" text-anchor="middle">PAYSTACK</text>
                        </svg>
                    </div>

                    <div class="secure-badge">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                        </svg>
                        <span>Secure payment powered by Paystack</span>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-paystack" id="pay-button">
                    🔒 Pay ${{ number_format($cartTotal, 2) }} Now
                </button>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Initializing secure payment...</p>
                </div>

                <a href="{{ route('shop.index') }}" class="btn btn-back">← Back to Shop</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const payButton = document.getElementById('pay-button');
            const loading = document.getElementById('loading');
            
            // Show loading state
            payButton.style.display = 'none';
            loading.style.display = 'block';

            const formData = {
                _token: '{{ csrf_token() }}',
                email: document.getElementById('email').value,
                first_name: document.getElementById('first_name').value,
                last_name: document.getElementById('last_name').value,
                phone: document.getElementById('phone').value,
                amount: '{{ $cartTotal }}'
            };

            fetch('{{ route("paystack.initialize") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    // Redirect to Paystack payment page
                    window.location.href = data.data.authorization_url;
                } else {
                    alert(data.message || 'Payment initialization failed');
                    payButton.style.display = 'block';
                    loading.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                payButton.style.display = 'block';
                loading.style.display = 'none';
            });
        });
    </script>
</body>
</html>
