<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/ingredient", name: "ingredient_")]
class IngredientController extends AbstractController
{
    /*
    #[Route('/ingredient', name: 'ingredient')]
    public function index(): Response
    {
        return $this->render('ingredient/index.html.twig', [
            'controller_name' => 'IngredientController',
        ]);
    }
    */

    #[Route('/add', name: 'add')]
    public function add(Request $request): Response{
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $today = new DateTimeImmutable();
            $ingredient->setUserId($user);
            $ingredient->setCreatedAt($today);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ingredient);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirect($request->request->get('referer'));
        }

        return $this->render('ingredient/create.html.twig', [
            'ingredientForm' => $form->createView(),
        ]);
    }
}
