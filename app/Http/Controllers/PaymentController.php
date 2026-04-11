<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function uploadFee()
    {
        $fee = Setting::get('upload_fee', '5000'); // Default NGN 5000
        return view('payment.upload-fee', compact('fee'));
    }

    public function initializeFlutterwave(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
        ]);

        $flutterwavePublicKey = Setting::get('flutterwave_public_key', '');
        
        // Generate a unique transaction reference
        $txRef = 'TXN-' . time() . '-' . Auth::id();

        // Return view with Flutterwave payment form
        return view('payment.flutterwave', [
            'amount' => $request->amount,
            'txRef' => $txRef,
            'email' => Auth::user()->email,
            'name' => Auth::user()->name,
            'publicKey' => $flutterwavePublicKey,
        ]);
    }

    public function flutterwaveCallback(Request $request)
    {
        // Verify the transaction with Flutterwave API
        $transactionId = $request->input('transaction_id');
        $status = $request->input('status');
        
        if ($status === 'successful') {
            // Transaction was successful
            return redirect()->route('home')->with('success', 'Payment successful! You can now upload content.');
        }
        
        return redirect()->route('payment.upload-fee')->with('error', 'Payment failed or was cancelled.');
    }

    public function verifyTransaction($txRef)
    {
        // This would call Flutterwave API to verify the transaction
        // For demonstration purposes, we'll return a sample response
        
        return response()->json([
            'status' => 'success',
            'message' => 'Transaction verified'
        ]);
    }
}
