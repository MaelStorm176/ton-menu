<?php

namespace App\Controller;

use DateTime;
use App\Entity\Rating;
use App\Entity\Recipe;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\RecetteType;
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

            return $this->redirectToRoute('recette', [
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
    public function show_recette(Recipe $recette, Request $request): Response{
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $repository = $this->getDoctrine()->getRepository(Rating::class);
        $rating = $repository->findOneBy(
            ['user' => $user, 'recette' => $recette],
        );

        $repository2 = $this->getDoctrine()->getRepository(Comment::class);
        $commentary = $repository2->findAll();

        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $today = new DateTimeImmutable();
            $comment->setUser($user);
            $comment->setCreatedAt($today);
            $comment->setRecette($recette);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('recette', [
                'id' => $recette->getId()
            ]);
        }

        return $this->render('new_recette/show.html.twig', [
            'recetteForm' => $form->createView(),
            'recette' => $recette,
            'rating' => $rating,
            'comment' => $commentary,
        ]);
    }

    /**
     * @Route("/all", name="all_recette")
     */

     public function show_all(){
        $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $recettes = $repository->findAll();

        $repository2 = $this->getDoctrine()->getRepository(Rating::class);
        $rating = $repository2->findAll();

        $repository3 = $this->getDoctrine()->getRepository(Comment::class);
        $comment = $repository3->findAll();

        return $this->render('new_recette/home.html.twig', [
            'recette' => $recettes,
            'rating' => $rating,
            'comment' => $comment,
        ]);
     }
}
