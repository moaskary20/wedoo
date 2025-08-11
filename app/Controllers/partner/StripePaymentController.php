<?php

namespace App\Controllers\partner;


class StripePaymentController extends Partner
{

    public function index()
    {
        return view('home');
    }

    /**
     * Get All Data from this method.
     *
     * @return Response
     */
    public function payment()
    {
        require_once('application/libraries/stripe-php/init.php');

        $stripeSecret = '';

        \Stripe\Stripe::setApiKey($stripeSecret);

        $stripe = \Stripe\Charge::create([
            "amount" => $this->request->getVar('amount'),
            "currency" => "usd",
            "source" => $this->request->getVar('tokenId'),
            "description" => "Test payment from tutsmake.com."
        ]);

        // after successfull payment, you can store payment related information into your database

        $data = array('success' => true, 'data' => $stripe);

        echo json_encode($data);
    }
}
