<?php

namespace App\Controller;

use Error;
use Stripe\Price;
use DateImmutable;
use DateTimeImmutable;
use Stripe\Stripe;
use App\Entity\Transaction;
use Stripe\BillingPortal\Session;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Checkout\Session as SessionCheckout;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TransactionController extends AbstractController
{
    #[Route('/payment', name: 'app_payment')]
    public function indexMonthPayment(): Response
    {
        if (in_array("ROLE_PREMIUM", $this->getUser()->getRoles())) {
            return $this->redirectToRoute('home');
        }
        //use stripe for payment
        Stripe::setApiKey('sk_test_51LT4c2FE8xx5Qn4Z4Lhs70L8T5AiOnSbUHGMldAcySIc38XPX0MTz42VwqYr5s1n9AW9E3sJOeygnw1t7C867JgQ00hAvVGDiz');

        // The price ID passed from the front end.
        //   $priceId = $_POST['priceId'];
        header('Content-Type: application/json');

        $YOUR_DOMAIN = 'https://tonmenu.osc-fr1.scalingo.io';

        try {
            $checkout_session = SessionCheckout::create([
                'line_items' => [[
                    'price' => "price_1LT52FFE8xx5Qn4ZoIATO3nm",
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $YOUR_DOMAIN . '/success/{CHECKOUT_SESSION_ID}',
                'cancel_url' => $YOUR_DOMAIN . '/cancel',
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
        $manager = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $user = $this->getUser();
        $transaction->setUser($user);
        $transaction->setValidate(true);
        $transaction->setCreatedAt(new DateTimeImmutable());
        $transaction->setValidateAt(new DateTimeImmutable());
        $transaction->setSessionId($session_id);
        
        $role = $user->getRoles();
        array_push($role, "ROLE_PREMIUM");
        $user->setRoles($role);

        Stripe::setApiKey('sk_test_51LT4c2FE8xx5Qn4Z4Lhs70L8T5AiOnSbUHGMldAcySIc38XPX0MTz42VwqYr5s1n9AW9E3sJOeygnw1t7C867JgQ00hAvVGDiz');

        header('Content-Type: application/json');

        $YOUR_DOMAIN = 'https://tonmenu.osc-fr1.scalingo.io/profile';

        try {
            $checkout_session = SessionCheckout::retrieve($session_id);
            $return_url = $YOUR_DOMAIN;

            // Authenticate your user.
            $session = Session::create([
                'customer' => $checkout_session->customer,
                'return_url' => $return_url,
            ]);
            header("HTTP/1.1 303 See Other");
            header("Location: " . $session->url);

            $manager->persist($transaction);
            $manager->persist($user);
            $manager->flush();
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
