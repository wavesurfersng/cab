<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $wallet = Auth::user()->wallet;
        $transactions = Transaction::where('wallet_id', $wallet->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('wallet.index', compact('wallet', 'transactions'));
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:20',
            'account_name' => 'required|string|max:255',
        ]);

        $wallet = Auth::user()->wallet;

        DB::beginTransaction();
        try {
            if ($wallet->balance < $request->amount) {
                return back()->with('error', 'Insufficient balance.');
            }

            $wallet->debit($request->amount, 'withdrawal', 'Withdrawal to ' . $request->bank_name);

            // Here you would integrate with a payment processor for actual withdrawal
            // For now, we just record the transaction

            DB::commit();

            return back()->with('success', 'Withdrawal request submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Withdrawal failed. Please try again.');
        }
    }

    public function depositCallback(Request $request)
    {
        // This handles Flutterwave callback
        $transactionRef = $request->input('transaction_ref');
        
        // Verify transaction with Flutterwave API
        // For now, we'll simulate a successful verification
        
        DB::beginTransaction();
        try {
            $wallet = Auth::user()->wallet;
            $amount = $request->input('amount', 0);
            
            if ($amount > 0) {
                $wallet->credit($amount, 'deposit', 'Deposit via Flutterwave - Ref: ' . $transactionRef);
            }
            
            DB::commit();
            
            return redirect()->route('wallet.index')->with('success', 'Deposit successful!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('wallet.index')->with('error', 'Deposit verification failed.');
        }
    }
}
