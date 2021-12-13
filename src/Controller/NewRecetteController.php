<?php

namespace App\Controller;

use DateTime;
use App\Entity\Recipe;
use App\Form\RecetteType;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class NewRecetteController extends AbstractController
{
/**
     * @Route("/add", name="add")
     */
    public function add_recette(Request $request, SluggerInterface $slugger): Response{
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $recette = new Recipe();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $today = new DateTimeImmutable();
            $recette->setUserId($user);
            $recette->setCreatedAt($today);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recette);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('/recette', [
                'id' => $recette->getId()
            ]);
        }

        return $this->render('new_recette/create.html.twig', [
            'recetteForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/recette/{id}", name="recette")
     */
    public function show_recette(Recipe $recette): Response{
        return $this->render('new_recette/show.html.twig', [
            'recette' => $recette,
        ]);
    }

    /**
     * @Route("/all", name="all_recette")
     */

     public function show_all(){
        $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $recettes = $repository->findAll();

        return $this->render('new_recette/home.html.twig', [
            'recette' => $recettes,
        ]);
     }
}
