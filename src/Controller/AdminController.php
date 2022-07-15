<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/admin", name: "admin_")]
class AdminController extends AbstractController
{
    #[Route("/", name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route("/users", name: 'users')]
    public function show_users(): Response
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $users = $repository->findAll();

        return $this->render('admin/users/show_users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route("/ingredients", name: 'ingredients')]
    public function show_ingredients(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Ingredient::class);
        $ingredients = $repository->findAll();

        return $this->render('admin/ingredients/show_ingredients.html.twig', [
            'ingredients' => $ingredients,
        ]);
    }

    #[Route("/recipes",name: 'recipes')]
    public function show_recipes(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $recipes = $repository->findAll();

        return $this->render('admin/recipes/show_recipes.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route("/set",name: 'set_admin')]
    public function setAdmin(): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $user->setRoles(["ROLE_ADMIN"]);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        // do anything else you need here, like send an email
        return $this->redirectToRoute('admin');
    }

    #[Route('/role/{id}', name: 'set_chief')]
    public function setChief($id): Response
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);
    
    
        $user->setRoles(["ROLE_CHIEF"]);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
        // do anything else you need here, like send an email
    
        return $this->redirectToRoute('admin_users');
    }
}
