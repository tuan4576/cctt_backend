<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PaymentController extends Controller
{
    public function createPayment(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|numeric',
        ]);

        $paypal = new PayPalClient();
        $paypal->setApiCredentials(config('paypal'));
        $paypal->setAccessToken($paypal->getAccessToken());

        $data = [
            'intent' => 'sale',
            'redirect_urls' => [
                'return_url' => url('/payment/success'),
                'cancel_url' => url('/payment/cancel'),
            ],
            'payer' => [
                'payment_method' => 'paypal',
            ],
            'transactions' => [[
                'amount' => [
                    'total' => $request->amount,
                    'currency' => 'USD',
                ],
                'description' => 'Payment description.',
            ]],
        ];

        $response = $paypal->createOrder($data);

        if (isset($response['id'])) {
            return response()->json($response);
        }

        return response()->json($response, 500); // Trả về mã lỗi 500 nếu có lỗi
    }
}
