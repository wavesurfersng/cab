<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
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
            max-width: 1200px;
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

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
        }

        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background-color: #f8f9fa;
        }

        .product-info {
            padding: 20px;
        }

        .product-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .product-description {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 15px;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
        }

        .cart-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .cart-header h2 {
            color: #2c3e50;
            font-size: 1.8rem;
        }

        .cart-items {
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            gap: 20px;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }

        .cart-item-details {
            flex: 1;
        }

        .cart-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: #667eea;
            font-weight: 600;
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            font-size: 1rem;
        }

        .cart-total {
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

        .empty-cart {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
        }

        .empty-cart-icon {
            font-size: 4rem;
            margin-bottom: 20px;
        }

        .actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .actions .btn {
            flex: 1;
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                text-align: center;
            }

            .cart-item-quantity {
                margin-top: 15px;
            }

            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>🛍️ Shopping Cart</h1>
        </div>
    </header>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Products Section -->
        <h2 style="margin-bottom: 20px; color: #2c3e50;">Our Products</h2>
        <div class="products-grid">
            @foreach($products as $product)
                <div class="product-card">
                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="product-image">
                    <div class="product-info">
                        <h3 class="product-name">{{ $product['name'] }}</h3>
                        <p class="product-description">{{ $product['description'] }}</p>
                        <div class="product-price">${{ number_format($product['price'], 2) }}</div>
                        <form action="{{ route('shop.cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Cart Section -->
        <div class="cart-section">
            <div class="cart-header">
                <h2>🛒 Your Cart</h2>
                @if(count($cart) > 0)
                    <form action="{{ route('shop.cart.clear') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-danger" style="width: auto;">Clear Cart</button>
                    </form>
                @endif
            </div>

            @if(count($cart) > 0)
                <div class="cart-items">
                    @foreach($cart as $item)
                        <div class="cart-item">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="cart-item-image">
                            <div class="cart-item-details">
                                <div class="cart-item-name">{{ $item['name'] }}</div>
                                <div class="cart-item-price">${{ number_format($item['price'], 2) }}</div>
                            </div>
                            <form action="{{ route('shop.cart.update') }}" method="POST" style="display: flex; align-items: center; gap: 10px;">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                                <div class="cart-item-quantity">
                                    <label>Qty:</label>
                                    <input type="number" 
                                           name="quantity" 
                                           value="{{ $item['quantity'] }}" 
                                           min="1" 
                                           max="99" 
                                           class="quantity-input"
                                           onchange="this.form.submit()">
                                </div>
                                <div style="font-weight: 600; color: #667eea; min-width: 100px; text-align: right;">
                                    ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                </div>
                                <a href="{{ route('shop.cart.remove', $item['id']) }}" 
                                   class="btn btn-danger" 
                                   style="width: auto; padding: 8px 15px;">×</a>
                            </form>
                        </div>
                    @endforeach
                </div>

                <div class="cart-total">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>${{ number_format($cartTotal, 2) }}</span>
                    </div>
                    <div class="total-row">
                        <span>Tax (0%):</span>
                        <span>$0.00</span>
                    </div>
                    <div class="total-row final">
                        <span>Total:</span>
                        <span>${{ number_format($cartTotal, 2) }}</span>
                    </div>
                </div>

                <div class="actions">
                    <a href="{{ route('shop.checkout') }}" class="btn btn-success">Proceed to Checkout →</a>
                </div>
            @else
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Add some products to get started!</p>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
