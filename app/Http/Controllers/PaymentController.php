<?php

namespace App\Http\Controllers;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use App\Models\PaymentTransaction;
use App\Models\PaymentOrder;
use App\Models\Client;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(PaymentGatewayInterface $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function paymentProcess(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:clients,id',
                'amount_cents' => 'required|numeric',
                'currency' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request parameters'
                ], 422);
            }

            // Process payment first to get the order ID
            $response = $this->paymentGateway->sendPayment($request);

            if ($response['success']) {
                // Create payment order with the order ID from Paymob
                $order = PaymentOrder::create([
                    'client_id' => $request->client_id,
                    'order_id' => $response['order_id'],
                    'amount' => $request->amount_cents / 100,
                    'currency' => $request->currency,
                    'status' => 'processing',
                    'payment_data' => $request->all()
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment initiated successfully',
                    'order_id' => $order->order_id,
                    'url' => $response['url']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment',
                'error' => $response['message'] ?? 'Unknown error'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function callBack(Request $request)
    {
        try {
            $response = $this->paymentGateway->callBack($request);
            
            // Save or retrieve the transaction
            $transaction = PaymentTransaction::createFromPaymobCallback($request->all());
            
            if ($transaction->status === 'success') {
                return redirect('http://localhost:3000/client/offers/active');
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'error_message' => $transaction->error_message,
                    'is_duplicate' => $transaction->wasRecentlyCreated ? false : true
                ]
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment callback',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function success()
    {
        return view('payment-success');
    }
    
    public function failed()
    {
        return view('payment-failed');
    }

    public function getTransactionByOrderId($orderId): \Illuminate\Http\JsonResponse
    {
        try {
            $transaction = PaymentTransaction::where('order_id', $orderId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'No transaction found for this order',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'order_id' => $transaction->order_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'payment_method' => $transaction->payment_method,
                    'card_number' => $transaction->card_number,
                    'error_message' => $transaction->error_message,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                    'callback_data' => $transaction->callback_data
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getClientOrders(): \Illuminate\Http\JsonResponse
    {
        try {
            $clientId = auth()->user()->id;
            
            $orders = PaymentOrder::where('client_id', $clientId)
                ->select(
                    'payment_orders.order_id',
                    'payment_orders.amount',
                    'payment_orders.currency',
                    'payment_orders.status as order_status',
                    'payment_orders.created_at as order_created_at'
                )
                ->orderBy('payment_orders.created_at', 'desc')
                ->get()
                ->map(function($order) {
                    $transaction = DB::table('payment_transactions')
                        ->where('order_id', $order->order_id)
                        ->select(
                            'transaction_id',
                            'status as transaction_status',
                            'payment_method',
                            'created_at as transaction_created_at',
                            'error_message'
                        )
                        ->first();

                    return [
                        'order_id' => $order->order_id,
                        'amount' => $order->amount,
                        'currency' => $order->currency,
                        'order_created_at' => $order->order_created_at,
                        'transaction' => $transaction ? [
                            'transaction_id' => $transaction->transaction_id,
                            'status' => $transaction->transaction_status,
                            'payment_method' => $transaction->payment_method,
                            'created_at' => $transaction->transaction_created_at,
                            'error_message' => $transaction->error_message
                        ] : null
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving client orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 