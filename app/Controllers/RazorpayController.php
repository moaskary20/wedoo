<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TransactionModel;
use Razorpay\Api\Api;

class RazorpayController extends BaseController
{
    public function payWithRazorpay()
    {
        return view('payment-form');
    }

    public function processPayment()
    {
        //Input items of form
        $input = $this->request->getVar();
        //get API Configuration
        $api = new Api(env('razorKey'), env('razorSecret'));
        //Fetch payment information by razorpay_payment_id
        $payment = $api->payment->fetch($input['razorpay_payment_id']);

        if (count($input) && !empty($input['razorpay_payment_id'])) {
            try {

                $response = $api->payment->fetch($input['razorpay_payment_id'])->capture(array('amount' => $payment['amount']));

            } catch (\Exception $e) {
                return $e->getMessage();
                session()->setFlashdata("error", $e->getMessage());
                return redirect()->back();
            }

            // save transaction details
            // $trnx = new TransactionModel();

            // $trnx->insert([
            //     "amount" => $response->amount,
            //     "currency" => $response->currency,
            //     "trnx_id" => $response->id,
            //     "card_id" => $response->card_id,
            //     "status" => $response->status,
            // ]);
        }

        session()->setFlashdata('success', 'Payment successfully done');
        
        return redirect()->back();
    }
}