<?php

namespace App\Services;

use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;

class PayPalService
{
    private $client;

    public function __construct()
    {
        // Thiết lập môi trường (sandbox hoặc production) dựa trên file .env
        $environment = env('PAYPAL_MODE') === 'sandbox'
            ? new SandboxEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'))
            : new ProductionEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_SECRET'));

        // Khởi tạo client của PayPal
        $this->client = new PayPalHttpClient($environment);
    }

    public function createOrder($amount)
{
    $request = new OrdersCreateRequest();
    $request->prefer('return=representation');
    $request->body = [
        "intent" => "CAPTURE",
        "purchase_units" => [
            [
                "amount" => [
                    "currency_code" => "USD",
                    "value" => $amount
                ]
            ]
        ]
    ];

    try {
        $response = $this->client->execute($request);
        return $response;
    } catch (\PayPal\Exception\PayPalConnectionException $e) {
        // Ghi lại chi tiết mã và thông điệp lỗi từ PayPal
        \Log::error('PayPal error: ' . $e->getCode() . ' - ' . $e->getMessage());
        return null;
    } catch (\Exception $e) {
        // Ghi lại lỗi khác
        \Log::error('General error: ' . $e->getMessage());
        return null;
    }
}

}
