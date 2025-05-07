<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymobPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    /**
     * Create a new class instance.
     */
    protected $api_key;
    protected $integrations_id;

    public function __construct()
    {
        $this->base_url = env("PAYMOB_BASE_URL");
        $this->api_key = env("PAYMOB_API_KEY");
        $this->header = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        // Read integration ID from environment
        $integration_id = env("PAYMOB_INTEGRATION_ID");
        $this->integrations_id = $integration_id ? [$integration_id] : [];
    }

    // First generate token to access API
    protected function generateToken()
    {
        $response = $this->buildRequest('POST', '/api/auth/tokens', ['api_key' => $this->api_key]);
        return $response->getData(true)['data']['token'];
    }

    public function sendPayment(Request $request): array
    {
        try {
            $this->header['Authorization'] = 'Bearer ' . $this->generateToken();
            
            // Validate required data
            $data = $request->all();
            $requiredFields = ['amount_cents', 'currency'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    return [
                        'success' => false,
                        'message' => "Missing required field: {$field}",
                        'url' => null
                    ];
                }
            }

            // Ensure the merchant_order_id is unique by appending a timestamp if not provided or already used
            if (!isset($data['merchant_order_id']) || empty($data['merchant_order_id'])) {
                $data['merchant_order_id'] = time() . '-' . Str::random(8);
            } else {
                // Append timestamp to make it unique
                $data['merchant_order_id'] = $data['merchant_order_id'] . '-' . time();
            }
            
            $data['api_source'] = "INVOICE";
            $data['integrations'] = $this->integrations_id;

            // Log the request payload for debugging
            Storage::put('paymob_request.json', json_encode($data));

            $response = $this->buildRequest('POST', '/api/ecommerce/orders', $data);
            $responseData = $response->getData(true);

            // Handle payment response data and return it
            if ($responseData['success']) {
                return [
                    'success' => true,
                    'url' => $responseData['data']['url'],
                    'order_id' => $responseData['data']['id'] ?? null
                ];
            }

            // Log the error for debugging
            Storage::put('paymob_error.json', json_encode([
                'error' => 'Payment request failed',
                'response' => $responseData
            ]));
            
            return [
                'success' => false,
                'message' => $responseData['message'] ?? 'Payment request failed',
                'url' => null
            ];
        } catch (\Exception $e) {
            // Log the exception
            Storage::put('paymob_exception.json', json_encode([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]));

            return [
                'success' => false,
                'message' => 'An error occurred while processing the payment',
                'url' => null
            ];
        }
    }

    public function callBack(Request $request): bool
    {
        $response = $request->all();
        Storage::put('paymob_response.json', json_encode($request->all()));

        // Validate required Paymob parameters
        if (!isset($response['obj']) || !isset($response['success'])) {
            Storage::put('paymob_error.json', json_encode(['error' => 'Missing required parameters']));
            return false;
        }

        // Extract transaction data
        $transactionData = $response['obj'];
        
        // Validate transaction status
        if ($transactionData['success'] !== true) {
            Storage::put('paymob_error.json', json_encode(['error' => 'Transaction failed', 'data' => $transactionData]));
            return false;
        }

        // Validate transaction amount if needed
        if (isset($transactionData['amount_cents']) && isset($transactionData['currency'])) {
            // You can add amount validation here if needed
            // $expectedAmount = ...;
            // if ($transactionData['amount_cents'] !== $expectedAmount) {
            //     return false;
            // }
        }

        // Log successful transaction
        Storage::put('paymob_success.json', json_encode($transactionData));
        
        return true;
    }
} 