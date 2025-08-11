<?php namespace App\Controllers;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
 
class Stripe extends Controller
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
     
      $stripeSecret = 'sk_test_j5k0976GOLSOtiRzbDLpKqat00og5iM3cY';
 
      \Stripe\Stripe::setApiKey($stripeSecret);
      
        $stripe = \Stripe\Charge::create ([
                "amount" => $this->request->getVar('amount'),
                "currency" => "usd",
                "source" => $this->request->getVar('tokenId'),
                "description" => "Test payment from tutsmake.com."
        ]);
             
       // after successfull payment, you can store payment related information into your database
              
        $data = array('success' => true, 'data'=> $stripe);
 
        echo json_encode($data);
    }
 
}