<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Demande;
use App\Entity\Ingredient;
use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\Signalement;
use App\Entity\User;
use App\Repository\DemandeRepository;
use App\Services\MarmitonManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/admin", name: "admin_")]
class AdminController extends AbstractController
{
    #[Route("/", name: 'index')]
    public function index(): Response
    {
        $marmitonManager = new MarmitonManager($this->getDoctrine()->getRepository(Ingredient::class));
        $marmitonManager->updateIngredientsImage();
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

    #[Route("/users/{id}", name: 'user_show')]
    public function show_user(User $user): Response
    {
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['user' => $user]);
        $bestRecipes = $this->getDoctrine()->getRepository(Recipe::class)->findBestRatedRecipesMadeByAUser($user);
        $worstRecipes = $this->getDoctrine()->getRepository(Recipe::class)->findWorstRatedRecipesMadeByAUser($user);
        return $this->render('admin/users/show_user.html.twig', [
            'user' => $user,
            'bestRecipes' => $bestRecipes,
            'worstRecipes' => $worstRecipes,
            'comments' => $comments,
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
        return $this->redirectToRoute('admin_index');
    }

    #[Route('/role/{id}', name: 'set_chief')]
    public function setChief($id, DemandeRepository $demandeRepository): Response
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);
        
        //get demande with user id
        $demande = $demandeRepository->findOneBy(['user' => $user]);
        $demande->setAccept(1);
    
        $user->setRoles(["ROLE_CHIEF"]);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user, $demande);
        $entityManager->flush();
        // do anything else you need here, like send an email
    
        return $this->redirectToRoute('admin_accept_demande');
    }

    #[Route('/report', name: 'report_comment')]
    public function reportComment(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Signalement::class);
        $comments = $repository->findAll();

        return $this->render('admin/comments/report_comment.html.twig', [
            'comments' => $comments,
        ]);
    }

    #[Route('/demande', name: 'accept_demande')]
    public function acceptDemande(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Demande::class);
        $demandes = $repository->findAll();

        return $this->render('admin/demande/accept_demande.html.twig', [
            'demandes' => $demandes,
        ]);
    }

    #[Route('/delete-comment/{id}', name: 'delete_report')]
    public function deleteReport(Signalement $signalement): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($signalement->getMessage());
        $entityManager->remove($signalement);
        $entityManager->flush();
        return $this->redirectToRoute('admin_report_comment');
    }

    #[Route('/refuse/{id}', name: 'refuse')]
    public function refuseRole($id, DemandeRepository $demandeRepository): Response
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);
        
        //get demande with user id
        $demande = $demandeRepository->findOneBy(['user' => $user]);
        $demande->setAccept(2);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($demande);
        $entityManager->flush();
        // do anything else you need here, like send an email
    
        return $this->redirectToRoute('admin_accept_demande');
    }
}
