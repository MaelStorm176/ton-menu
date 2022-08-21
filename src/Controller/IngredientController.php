<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\RecipeIngredients;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
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
    private EntityManagerInterface $entityManager;
    private IngredientRepository $ingredientRepository;
    public function __construct(EntityManagerInterface $entityManager, IngredientRepository $ingredientRepository)
    {
        $this->entityManager = $entityManager;
        $this->ingredientRepository = $ingredientRepository;
    }

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
                $fileName = $this->upload_image($file, $form);
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

            $this->entityManager->persist($ingredient)->flush();
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
        $ingredient = $this->ingredientRepository->findOneBy(['name' => $name]);
        if ($ingredient){
            return new JsonResponse(['success' => false,'message' => 'Ce nom est déjà utilisé']);
        }
        return new JsonResponse(['success' => true,'message' => 'Ce nom est disponible']);
    }

    #[Route('/{id}', name: 'show')]
    public function show(Ingredient $ingredient): Response {
        return $this->render('ingredient/show.html.twig', [
            'ingredient' => $ingredient,
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, Ingredient $ingredient): Response {
        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //upload ingredient image
            $file = $form->get('image')->getData();

            //ancient image
            $oldImage = $ingredient->getImage();

            if ($file) {
                $fileName = $this->upload_image($file, $form);
                $ingredient->setImage($fileName);

                //delete old image
                if ($oldImage){
                    $this->delete_image($oldImage);
                }
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ingredient);
            $entityManager->flush();

            $this->addFlash('success', 'Ingredient modifié avec succès');
            // do anything else you need here, like send an email
            return $this->redirectToRoute('ingredient_show', [
                'id' => $ingredient->getId()
            ]);
        }
        return $this->render('ingredient/create.html.twig', [
            'ingredientForm' => $form->createView(),
            'ingredient' => $ingredient,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['DELETE', 'POST'])]
    public function delete(Ingredient $ingredient, Request $request): Response {
        if ($this->isCsrfTokenValid('delete'.$ingredient->getId(), $request->request->get('_token'))) {
            if ($ingredient->getRecipes()){
                $this->addFlash('error', 'Cet ingredient est utilisé dans une ou plusieurs recettes');
                return $this->redirectToRoute('ingredient_show', [
                    'id' => $ingredient->getId()
                ]);
            }
            if ($ingredient->getImage()){
                $this->delete_image($ingredient->getImage());
            }
            $this->entityManager->remove($ingredient);
            $this->entityManager->flush();
            $this->addFlash('success', 'Ingredient supprimé avec succès');
        }else{
            $this->addFlash('error', 'Une erreur est survenue');
        }
        return $this->redirectToRoute('home');
    }

    private function upload_image($file, $form): string
    {
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
        return $fileName;
    }

    private function delete_image($image): bool
    {
        $imagePath = $this->getParameter('ingredient_image_directory').'/'.$image;
        if (file_exists($imagePath)){
            unlink($imagePath);
            return true;
        }
        return false;
    }
}
