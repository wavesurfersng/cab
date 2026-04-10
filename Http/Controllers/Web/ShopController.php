<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Exception;

class ShopController extends Controller
{
    /**
     * Display the shopping cart page
     */
    public function index()
    {
        $products = $this->getProducts();
        $cart = Session::get('cart', []);
        $cartTotal = $this->calculateCartTotal($cart);
        
        return view('shop.index', compact('products', 'cart', 'cartTotal'));
    }

    /**
     * Add item to cart
     */
    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);
        
        $products = $this->getProducts();
        $product = collect($products)->firstWhere('id', $productId);
        
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }
        
        Session::put('cart', $cart);
        
        return redirect()->back()->with('success', 'Product added to cart!');
    }

    /**
     * Update cart item quantity
     */
    public function updateCart(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');
        
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            if ($quantity > 0) {
                $cart[$productId]['quantity'] = $quantity;
            } else {
                unset($cart[$productId]);
            }
            Session::put('cart', $cart);
        }
        
        return redirect()->back()->with('success', 'Cart updated!');
    }

    /**
     * Remove item from cart
     */
    public function removeFromCart($productId)
    {
        $cart = Session::get('cart', []);
        
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            Session::put('cart', $cart);
        }
        
        return redirect()->back()->with('success', 'Product removed from cart!');
    }

    /**
     * Clear entire cart
     */
    public function clearCart()
    {
        Session::forget('cart');
        return redirect()->back()->with('success', 'Cart cleared!');
    }

    /**
     * Proceed to checkout
     */
    public function checkout()
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty');
        }
        
        $cartTotal = $this->calculateCartTotal($cart);
        
        return view('shop.checkout', compact('cart', 'cartTotal'));
    }

    /**
     * Get sample products
     */
    private function getProducts()
    {
        return [
            [
                'id' => 1,
                'name' => 'Premium Wireless Headphones',
                'price' => 99.99,
                'image' => 'https://via.placeholder.com/300x300?text=Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation'
            ],
            [
                'id' => 2,
                'name' => 'Smart Watch Pro',
                'price' => 249.99,
                'image' => 'https://via.placeholder.com/300x300?text=Smart+Watch',
                'description' => 'Feature-rich smartwatch with health tracking'
            ],
            [
                'id' => 3,
                'name' => 'Bluetooth Speaker',
                'price' => 79.99,
                'image' => 'https://via.placeholder.com/300x300?text=Speaker',
                'description' => 'Portable Bluetooth speaker with amazing sound'
            ],
            [
                'id' => 4,
                'name' => 'Laptop Stand',
                'price' => 49.99,
                'image' => 'https://via.placeholder.com/300x300?text=Laptop+Stand',
                'description' => 'Ergonomic laptop stand for better posture'
            ],
            [
                'id' => 5,
                'name' => 'Mechanical Keyboard',
                'price' => 129.99,
                'image' => 'https://via.placeholder.com/300x300?text=Keyboard',
                'description' => 'RGB mechanical keyboard with Cherry MX switches'
            ],
            [
                'id' => 6,
                'name' => 'Gaming Mouse',
                'price' => 69.99,
                'image' => 'https://via.placeholder.com/300x300?text=Gaming+Mouse',
                'description' => 'Precision gaming mouse with customizable buttons'
            ]
        ];
    }

    /**
     * Calculate cart total
     */
    private function calculateCartTotal($cart)
    {
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return round($total, 2);
    }
}
