<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'order_id',
        'amount',
        'currency',
        'status',
        'payment_data',
        'callback_data',
        'payment_method',
        'card_number',
        'card_holder_name',
        'error_message'
    ];

    protected $casts = [
        'payment_data' => 'array',
        'callback_data' => 'array',
        'amount' => 'decimal:2'
    ];

    // Helper method to create a transaction from Paymob callback
    public static function createFromPaymobCallback(array $callbackData)
    {
        // Check if transaction already exists
        $existingTransaction = self::where('transaction_id', $callbackData['id'])->first();
        if ($existingTransaction) {
            return $existingTransaction;
        }

        // Extract transaction status
        $isSuccess = $callbackData['success'] === 'true';
        $status = $isSuccess ? 'success' : 'failed';
        
        // Get error message if any
        $errorMessage = null;
        if (!$isSuccess) {
            if (!empty($callbackData['data_message'])) {
                $errorMessage = $callbackData['data_message'];
            } elseif (!empty($callbackData['txn_response_code'])) {
                $errorMessage = $callbackData['txn_response_code'];
            } else {
                $errorMessage = 'Transaction failed';
            }
        }

        return self::create([
            'transaction_id' => $callbackData['id'] ?? null,
            'order_id' => $callbackData['order'] ?? null,
            'amount' => ($callbackData['amount_cents'] ?? 0) / 100,
            'currency' => $callbackData['currency'] ?? null,
            'status' => $status,
            'payment_data' => null,
            'callback_data' => $callbackData,
            'payment_method' => $callbackData['source_data_type'] ?? null,
            'card_number' => $callbackData['source_data_pan'] ?? null,
            'card_holder_name' => null,
            'error_message' => $errorMessage
        ]);
    }
}
