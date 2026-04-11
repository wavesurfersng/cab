<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'total_earned',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function credit($amount, $source = 'views', $description = null)
    {
        $this->increment('balance', $amount);
        
        Transaction::create([
            'wallet_id' => $this->id,
            'type' => 'credit',
            'source' => $source,
            'amount' => $amount,
            'reference' => 'TXN-' . uniqid(),
            'description' => $description,
            'status' => 'completed',
        ]);
    }

    public function debit($amount, $source = 'withdrawal', $description = null)
    {
        if ($this->balance >= $amount) {
            $this->decrement('balance', $amount);
            
            Transaction::create([
                'wallet_id' => $this->id,
                'type' => 'debit',
                'source' => $source,
                'amount' => $amount,
                'reference' => 'TXN-' . uniqid(),
                'description' => $description,
                'status' => 'completed',
            ]);
            
            return true;
        }
        
        return false;
    }
}
