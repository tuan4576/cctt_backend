<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PayPalService;

class PaymentController extends Controller
{
    protected $payPalService;

    public function __construct(PayPalService $payPalService)
    {
        $this->payPalService = $payPalService;
    }

    public function createPayment(Request $request)
    {
        try {
            $order = $this->payPalService->createOrder($request->amount);
            if ($order) {
                return response()->json(['approval_url' => $order->result->links[1]->href]);
            }
            return response()->json([
                'error' => 'Payment creation failed'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('PayPal error: ' . $e->getMessage());
            return response()->json([
                'error' => 'An error occurred while processing the payment'
            ], 500);
        }
    }

    public function paypalSuccess(Request $request)
    {
        return "Payment Successful!";
    }

    public function paypalCancel()
    {
        return "Payment Cancelled!";
    }
}
