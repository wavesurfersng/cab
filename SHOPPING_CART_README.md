# Shopping Cart with Paystack Payment Integration

A complete Laravel shopping cart application with Paystack payment gateway integration.

## Features

- 🛍️ Product catalog with sample products
- 🛒 Full shopping cart functionality (add, update, remove items)
- 💳 Secure Paystack payment integration
- 📧 Order confirmation and receipt
- 📱 Responsive design for mobile and desktop
- 🔒 Transaction verification with Paystack API

## Installation

### 1. Clone or copy the files to your Laravel project

The following files have been created:

**Controllers:**
- `Http/Controllers/Web/ShopController.php` - Handles shopping cart operations
- `Http/Controllers/Web/PaystackController.php` - Handles Paystack payment processing

**Views:**
- `resources/views/shop/index.blade.php` - Product catalog and shopping cart
- `resources/views/shop/checkout.blade.php` - Checkout page
- `resources/views/paystack/checkout.blade.php` - Paystack payment form
- `resources/views/paystack/success.blade.php` - Payment success confirmation

**Routes:**
- Routes have been added to `routes/web.php`

### 2. Configure Paystack API Keys

Add your Paystack credentials to your `.env` file:

```env
PAYSTACK_SECRET_KEY=sk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
PAYSTACK_PUBLIC_KEY=pk_test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

Get your API keys from: https://dashboard.paystack.com/#/settings/developer

### 3. Update Routes (already done)

The following routes are available:

```php
// Shop routes
Route::get('shop', 'ShopController@index')->name('shop.index');
Route::post('shop/cart/add', 'ShopController@addToCart')->name('shop.cart.add');
Route::post('shop/cart/update', 'ShopController@updateCart')->name('shop.cart.update');
Route::get('shop/cart/remove/{productId}', 'ShopController@removeFromCart')->name('shop.cart.remove');
Route::post('shop/cart/clear', 'ShopController@clearCart')->name('shop.cart.clear');
Route::get('shop/checkout', 'ShopController@checkout')->name('shop.checkout');

// Paystack routes
Route::get('paystack', 'PaystackController@index')->name('paystack.index');
Route::post('paystack/initialize', 'PaystackController@initializePayment')->name('paystack.initialize');
Route::get('paystack/payment/success', 'PaystackController@paystackCheckout')->name('paystack.success');
```

### 4. Access the Application

Visit these URLs in your browser:

- **Shop/Cart**: `http://your-domain.com/shop`
- **Checkout**: `http://your-domain.com/shop/checkout`
- **Paystack Payment**: `http://your-domain.com/paystack`

## Usage Flow

1. **Browse Products**: Visit `/shop` to see available products
2. **Add to Cart**: Click "Add to Cart" on any product
3. **View Cart**: Review your cart on the same page
4. **Update Quantity**: Change quantities directly in the cart
5. **Proceed to Checkout**: Click "Proceed to Checkout"
6. **Enter Details**: Fill in customer information
7. **Pay with Paystack**: Complete payment securely
8. **Confirmation**: Receive order confirmation with details

## Customization

### Adding Real Products

Replace the `getProducts()` method in `ShopController.php` with your database query:

```php
private function getProducts()
{
    return Product::all()->toArray();
}
```

### Database Integration

To save orders to your database:

1. Create an Order model and migration:
```bash
php artisan make:model Order -m
php artisan make:model OrderItem -m
```

2. Uncomment and customize the order creation code in `PaystackController@paystackCheckout`:

```php
// Save order to database
$order = Order::create($orderData);
foreach ($orderData['items'] as $item) {
    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $item['id'],
        'quantity' => $item['quantity'],
        'price' => $item['price']
    ]);
}
```

### Styling

All views use inline CSS for simplicity. You can:
- Move styles to separate CSS files
- Use a CSS framework like Bootstrap or Tailwind
- Customize colors and layouts as needed

## Security Notes

- Always use HTTPS in production
- Keep your Paystack secret key secure
- Verify all transactions server-side (already implemented)
- Implement proper authentication for user orders
- Add CSRF protection (already included)

## Testing

Use Paystack's test mode with test card numbers:
- **Success**: 4084 0840 8408 4084 (any future date, any CVV)
- **Decline**: 4111 1111 1111 1522

## Support

For Paystack-specific issues, refer to:
- Documentation: https://paystack.com/docs/
- API Reference: https://paystack.com/docs/api/
- Support: support@paystack.com

## License

This code is provided as-is for educational and commercial use.
