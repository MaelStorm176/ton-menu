<?php

namespace App\Controller;

use Error;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\BillingPortal\Session;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Checkout\Session as SessionCheckout;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TransactionController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function indexMonthPayment(): Response
    {
        //use stripe for payment
        Stripe::setApiKey('sk_test_51LT4c2FE8xx5Qn4Z4Lhs70L8T5AiOnSbUHGMldAcySIc38XPX0MTz42VwqYr5s1n9AW9E3sJOeygnw1t7C867JgQ00hAvVGDiz');

        // The price ID passed from the front end.
        //   $priceId = $_POST['priceId'];
        $priceId = 'price_1LT52FFE8xx5Qn4ZoIATO3nm';
        header('Content-Type: application/json');

        $YOUR_DOMAIN = 'http://localhost:8741/';

        try {
            $checkout_session = Session::create([
                'line_items' => [[
                    'price' => "price_1LT52FFE8xx5Qn4ZoIATO3nm",
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $YOUR_DOMAIN . '/success/{CHECKOUT_SESSION_ID}',
                'cancel_url' => $YOUR_DOMAIN . '/cancel.html',
            ]);

            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

        return ($this->render('transaction/index.html.twig'));
    }

    #[Route('/success/{session_id}', name: 'app_payment_success')]
    public function success($session_id): Response
    {
        Stripe::setApiKey('sk_test_51LT4c2FE8xx5Qn4Z4Lhs70L8T5AiOnSbUHGMldAcySIc38XPX0MTz42VwqYr5s1n9AW9E3sJOeygnw1t7C867JgQ00hAvVGDiz');

        header('Content-Type: application/json');

        $YOUR_DOMAIN = 'http://localhost:8741/profile/';

        try {
            $checkout_session = SessionCheckout::retrieve($session_id);
            $return_url = $YOUR_DOMAIN;

            // Authenticate your user.
            $session = Session::create([
                'customer' => $checkout_session->customer,
                'return_url' => 'http://localhost:8741/success',
            ]);
            header("HTTP/1.1 303 See Other");
            header("Location: " . $session->url);
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        return ($this->render(
            'transaction/success.html.twig',
            [
                "session" => $session,
            ]
        ));
    }

    #[Route('/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        return ($this->render('transaction/cancel.html.twig'));
    }
}
