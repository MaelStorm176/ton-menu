<?php

namespace App\Controller;

use App\Entity\User;
use Error;
use Stripe\Exception\ApiErrorException;
use DateTimeImmutable;
use Stripe\Stripe;
use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Stripe\BillingPortal\Session;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Checkout\Session as SessionCheckout;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TransactionController extends AbstractController
{
    const YOUR_DOMAIN = 'https://ton-menu-rattrapage.osc-fr1.scalingo.io/';
    //const YOUR_DOMAIN = 'http://localhost:8741';

    #[Route('/payment', name: 'app_payment')]
    public function indexMonthPayment(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('login');
        }
        if ($user->hasRole('ROLE_PREMIUM') || $user->hasRole('ROLE_ADMIN')) {
            $this->addFlash('error', 'Vous êtes déjà membre premium');
            return $this->redirectToRoute('home');
        }
        //use stripe for payment
        Stripe::setApiKey('sk_test_51LT4c2FE8xx5Qn4Z4Lhs70L8T5AiOnSbUHGMldAcySIc38XPX0MTz42VwqYr5s1n9AW9E3sJOeygnw1t7C867JgQ00hAvVGDiz');

        // The price ID passed from the front end.
        //   $priceId = $_POST['priceId'];
        header('Content-Type: application/json');

        try {
            $checkout_session = SessionCheckout::create([
                'line_items' => [[
                    'price' => "price_1LT52FFE8xx5Qn4ZoIATO3nm",
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => self::YOUR_DOMAIN . '/success/{CHECKOUT_SESSION_ID}',
                'cancel_url' => self::YOUR_DOMAIN . '/cancel',
            ]);

            header("HTTP/1.1 303 See Other");
            header("Location: " . $checkout_session->url);
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (ApiErrorException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }

        return ($this->render('transaction/index.html.twig'));
    }

    #[Route('/success/{session_id}', name: 'app_payment_success')]
    public function success($session_id, TransactionRepository $transactionRepository): Response
    {
        //$stripe = Stripe::setApiKey('sk_test_51LT4c2FE8xx5Qn4Z4Lhs70L8T5AiOnSbUHGMldAcySIc38XPX0MTz42VwqYr5s1n9AW9E3sJOeygnw1t7C867JgQ00hAvVGDiz');
        //$responseStripe = $stripe->paymentIntents->retrieve($session_id->getStripeId());

        $check_transaction = $transactionRepository->findOneBy(['session_id' => $session_id]);
        //dd($this->getUser());
        if($check_transaction && $check_transaction->getUser() != $this->getUser()) {
            $this->addFlash('error', 'Cette confirmation de paiements à déjà été réalisé et appartient à un autre utilisateur, veuillez avoir l\'argent sur vous au lieux de gratter comme un rat.');
            return $this->redirectToRoute('home');
        }elseif($check_transaction && $check_transaction->getUser() == $this->getUser()){
            $this->addFlash('error', 'Votre argent nous fais plaisir, mais vous essayez tout de même de nous gruger, cependant comme vous avez déjà payé vous ne pouvez pas.');
            return $this->redirectToRoute('home');
        }
        
        $manager = $this->getDoctrine()->getManager();

        $transaction = new Transaction();
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('This user does not have access to this section.');
        }
        $transaction->setUser($user);
        $transaction->setValidate(true);
        $transaction->setCreatedAt(new DateTimeImmutable());
        $transaction->setValidateAt(new DateTimeImmutable());
        $transaction->setSessionId($session_id);

        $role = $user->getRoles();
        $role[] = "ROLE_PREMIUM";
        $user->setRoles($role);
        $user->refreshApiKey();

        Stripe::setApiKey('sk_test_51LT4c2FE8xx5Qn4Z4Lhs70L8T5AiOnSbUHGMldAcySIc38XPX0MTz42VwqYr5s1n9AW9E3sJOeygnw1t7C867JgQ00hAvVGDiz');

        header('Content-Type: application/json');

        try {
            $checkout_session = SessionCheckout::retrieve($session_id);

            // Authenticate your user.
            $session = Session::create([
                'customer' => $checkout_session->customer,
                'return_url' => self::YOUR_DOMAIN,
            ]);
            header("HTTP/1.1 303 See Other");
            header("Location: " . $session->url);

            $manager->persist($transaction);
            $manager->persist($user);
            $manager->flush();
        } catch (Error $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (ApiErrorException $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        return ($this->render('transaction/success.html.twig', ["session" => $session]));
    }

    #[Route('/cancel', name: 'app_payment_cancel')]
    public function cancel(): Response
    {
        return ($this->render('transaction/cancel.html.twig'));
    }
}
