<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\RecipeIngredients;
use App\Form\IngredientType;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/ingredient", name: "ingredient_")]
class IngredientController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(Request $request): Response{
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //upload ingredient image
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = uniqid().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('ingredient_image_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $fileUploadError = new FormError("Une erreur est survenue lors de l'upload de l'image");
                    $form->get('image')->addError($fileUploadError);
                }
                $ingredient->setImage($fileName);
            }else{
                $ingredient->setImage(null);
            }

            // encode the plain password
            $today = new DateTimeImmutable();
            $ingredient->setUserId($user);
            $ingredient->setPrimaryType('ingredient');
            $ingredient->setSecondaryType(null);
            $ingredient->setCreatedAt($today);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ingredient);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('ingredient_show', [
                'id' => $ingredient->getId()
            ]);
        }

        return $this->render('ingredient/create.html.twig', [
            'ingredientForm' => $form->createView(),
        ]);
    }

    #[Route('/check-name', name: 'check_name', methods: ['GET'])]
    public function checkName(Request $request): JsonResponse{
        $name = $request->query->get('name');
        if (!$name) {
            return new JsonResponse(['error' => 'No name provided'], 400);
        }
        $ingredient = $this->getDoctrine()->getRepository(Ingredient::class)->findOneBy(['name' => $name]);
        if ($ingredient){
            return new JsonResponse(['success' => false,'message' => 'Ce nom est déjà utilisé']);
        }
        return new JsonResponse(['success' => true,'message' => 'Ce nom est disponible']);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Ingredient $ingredient): Response{
        return $this->render('ingredient/show.html.twig', [
            'ingredient' => $ingredient,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit')]
    public function edit(Request $request, Ingredient $ingredient): Response{
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //upload ingredient image
            $file = $form->get('image')->getData();
            if ($file) {
                $fileName = uniqid().'.'.$file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('ingredient_image_directory'),
                        $fileName
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $fileUploadError = new FormError("Une erreur est survenue lors de l'upload de l'image");
                    $form->get('image')->addError($fileUploadError);
                }
                $ingredient->setImage($fileName);
            }else{
                $ingredient->setImage(null);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ingredient);
            $entityManager->flush();
            // do anything else you need here, like send an email
            return $this->redirectToRoute('ingredient_show', [
                'id' => $ingredient->getId()
            ]);
        }
        return $this->render('ingredient/edit.html.twig', [
            'ingredientForm' => $form->createView(),
            'ingredient' => $ingredient,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete')]
    public function delete(Ingredient $ingredient): Response{
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($ingredient);
        $entityManager->flush();
        return $this->redirectToRoute('ingredient_index');
    }
}
