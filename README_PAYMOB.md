# Paymob Payment Integration

This integration allows your application to process payments securely through the Paymob payment gateway.

## Features

- Secure payment processing through Paymob gateway
- RESTful API endpoints for payment processing
- Callback handling for payment status updates
- Clean architecture with service layer implementation

## Prerequisites

- Laravel application
- Paymob account and API credentials

## Installation

The integration is already set up in the application. You just need to:

1. Add the following environment variables to your `.env` file:
```
PAYMOB_BASE_URL=https://accept.paymob.com
PAYMOB_API_KEY=your_paymob_api_key
```

2. Update the integration IDs in `app/Services/PaymobPaymentService.php` with your actual Paymob integration IDs.

## API Endpoints

### Payment Processing
- **POST** `/api/payment/process`
  - Process a new payment
  - Returns payment URL for redirection

### Payment Callback
- **GET/POST** `/api/payment/callback`
  - Handles payment status updates from Paymob
  - Redirects to success or failure page based on payment status

### Payment Status Pages
- **GET** `/payment-success`
  - Displayed after successful payment
  
- **GET** `/payment-failed`
  - Displayed after failed payment

## Usage Example

```php
// Example of making a payment request
$paymentData = [
    'amount_cents' => 10000, // 100 EGP
    'currency' => 'EGP',
    'merchant_order_id' => 123456, // Your order ID
    'items' => [
        [
            'name' => 'Product Name',
            'amount_cents' => 10000,
            'description' => 'Product Description',
            'quantity' => 1
        ]
    ],
    'shipping_data' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '+201234567890',
        'email' => 'john.doe@example.com',
    ],
    'billing_data' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '+201234567890',
        'email' => 'john.doe@example.com',
        'street' => '123 Main St',
        'city' => 'Cairo',
        'country' => 'Egypt',
        'state' => 'Cairo',
        'postal_code' => '12345'
    ]
];

// Make an HTTP request to the payment process endpoint
$response = Http::post('/api/payment/process', $paymentData);

if ($response->successful() && $response->json()['success']) {
    // Redirect the user to the payment URL
    return redirect($response->json()['url']);
}
```

## Security

- Payment callbacks are validated
- Error handling and logging are implemented

## Troubleshooting

If you encounter issues with the Paymob integration:

1. Check your Paymob API credentials in the `.env` file
2. Verify that your integration IDs are correct
3. Check the storage logs (`storage/logs/paymob_response.json`) for detailed error information

For more information, please refer to the [Paymob API Documentation](https://accept.paymob.com/docs). 