<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Exception;

class PaystackController extends Controller
{
    protected $paystackSecretKey;
    protected $paystackPublicKey;

    public function __construct()
    {
        // Configure your Paystack keys in .env file:
        // PAYSTACK_SECRET_KEY=sk_test_xxx
        // PAYSTACK_PUBLIC_KEY=pk_test_xxx
        $this->paystackSecretKey = env('PAYSTACK_SECRET_KEY');
        $this->paystackPublicKey = env('PAYSTACK_PUBLIC_KEY');
    }

    /**
     * Display Paystack payment page
     */
    public function index(Request $request)
    {
        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty');
        }

        $cartTotal = $this->calculateCartTotal($cart);
        
        // Convert to kobo (Paystack uses smallest currency unit)
        $amountInKobo = $cartTotal * 100;
        
        // Get user email from request or session
        $email = $request->input('email', Session::get('user_email', 'customer@example.com'));
        $firstName = $request->input('first_name', Session::get('user_first_name', 'John'));
        $lastName = $request->input('last_name', Session::get('user_last_name', 'Doe'));
        $phone = $request->input('phone', Session::get('user_phone', '+1234567890'));

        // Generate a unique reference for this transaction
        $reference = 'SHOP_' . time() . '_' . rand(10000, 99999);
        
        // Store order details in session for verification after payment
        Session::put('pending_order', [
            'reference' => $reference,
            'cart' => $cart,
            'total' => $cartTotal,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone
        ]);

        return view('paystack.checkout', compact(
            'cart',
            'cartTotal',
            'amountInKobo',
            'email',
            'firstName',
            'lastName',
            'phone',
            'reference'
        ));
    }

    /**
     * Initialize Paystack payment
     */
    public function initializePayment(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'amount' => 'required|numeric|min:1',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
        ]);

        $cart = Session::get('cart', []);
        
        if (empty($cart)) {
            return response()->json(['status' => false, 'message' => 'Cart is empty'], 400);
        }

        $cartTotal = $this->calculateCartTotal($cart);
        $amountInKobo = $cartTotal * 100;
        
        // Generate unique reference
        $reference = 'SHOP_' . time() . '_' . rand(10000, 99999);
        
        // Store pending order
        Session::put('pending_order', [
            'reference' => $reference,
            'cart' => $cart,
            'total' => $cartTotal,
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', [
                'email' => $request->email,
                'amount' => $amountInKobo,
                'reference' => $reference,
                'callback_url' => route('paystack.success'),
                'metadata' => json_encode([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'cart_items' => $cart
                ])
            ]);

            $result = $response->json();

            if ($response->successful() && $result['status']) {
                return response()->json([
                    'status' => true,
                    'message' => 'Payment initialization successful',
                    'data' => [
                        'authorization_url' => $result['data']['authorization_url'],
                        'access_code' => $result['data']['access_code'],
                        'reference' => $reference
                    ]
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => $result['message'] ?? 'Payment initialization failed'
                ], 400);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Paystack callback/success
     */
    public function paystackCheckout(Request $request)
    {
        $reference = $request->input('reference');
        
        if (!$reference) {
            return redirect()->route('shop.index')->with('error', 'No reference provided');
        }

        // Verify the transaction with Paystack
        $verification = $this->verifyTransaction($reference);

        if ($verification['status'] && $verification['data']['status'] === 'success') {
            // Payment successful
            $pendingOrder = Session::get('pending_order');
            
            // Here you would typically:
            // 1. Create an order in your database
            // 2. Send confirmation email
            // 3. Clear the cart
            
            $orderData = [
                'reference' => $reference,
                'transaction_id' => $verification['data']['id'],
                'amount' => $verification['data']['amount'] / 100, // Convert from kobo
                'currency' => $verification['data']['currency'],
                'channel' => $verification['data']['channel'],
                'email' => $verification['data']['customer']['email'],
                'paid_at' => $verification['data']['paid_at'],
                'items' => $pendingOrder['cart'] ?? []
            ];

            // Save order to database (implement this based on your needs)
            // Order::create($orderData);

            // Clear cart and pending order
            Session::forget('cart');
            Session::forget('pending_order');
            
            // Store success message
            Session::put('order_success', $orderData);

            return view('paystack.success', compact('orderData'));
        } else {
            // Payment failed
            return redirect()->route('shop.checkout')->with('error', 'Payment verification failed. Please try again.');
        }
    }

    /**
     * Verify transaction with Paystack API
     */
    private function verifyTransaction($reference)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->paystackSecretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get("https://api.paystack.co/transaction/verify/{$reference}");

            $result = $response->json();

            if ($response->successful()) {
                return [
                    'status' => true,
                    'data' => $result['data']
                ];
            } else {
                return [
                    'status' => false,
                    'message' => $result['message'] ?? 'Verification failed'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
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

    /**
     * Get public key for frontend
     */
    public function getPublicKey()
    {
        return response()->json([
            'public_key' => $this->paystackPublicKey
        ]);
    }
}
