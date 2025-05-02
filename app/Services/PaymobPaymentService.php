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
        $this->header['Authorization'] = 'Bearer ' . $this->generateToken();
        // Validate data before sending it
        $data = $request->all();
        
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
        // Handle payment response data and return it
        if ($response->getData(true)['success']) {
            return ['success' => true, 'url' => $response->getData(true)['data']['url']];
        }

        // Log the error for debugging
        Storage::put('paymob_error.json', json_encode($response->getData(true)));
        
        return ['success' => false, 'url' => null];
    }

    public function callBack(Request $request): bool
    {
        $response = $request->all();
        Storage::put('paymob_response.json', json_encode($request->all()));
        if (isset($response['success']) && $response['success'] === 'true') {
            return true;
        }
        return false;
    }
} 