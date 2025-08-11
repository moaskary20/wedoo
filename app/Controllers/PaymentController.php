<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Stripe\Stripe;
use Stripe\Charge;

class PaymentController extends Controller
{
    public function index()
    {
        // Load Stripe library
        $this->loadStripe();

        // Set your secret key
        Stripe::setApiKey(getenv('STRIPE_API_KEY'));

        // Process the payment
        try {
            $charge = Charge::create([
                'amount' => 1000, // Amount in cents
                'currency' => 'usd',
                'source' => 'tok_visa', // Sample token, replace with actual token from your payment form
                'description' => 'Example charge'
            ]);

            // Payment successful, do something
            echo 'Payment successful! Charge ID: ' . $charge->id;
        } catch (\Stripe\Exception\CardException $e) {
            // Handle card error
            echo 'Card Error: ' . $e->getMessage();
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Handle invalid request
            echo 'Invalid Request Error: ' . $e->getMessage();
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Handle authentication error
            echo 'Authentication Error: ' . $e->getMessage();
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Handle API connection error
            echo 'API Connection Error: ' . $e->getMessage();
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Handle general API error
            echo 'API Error: ' . $e->getMessage();
        } catch (\Exception $e) {
            // Handle other errors
            echo 'Error: ' . $e->getMessage();
        }
    }

    private function loadStripe()
    {
        require_once APPPATH . '../vendor/autoload.php';
    }
}
