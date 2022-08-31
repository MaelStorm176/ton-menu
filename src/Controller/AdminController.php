<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Demande;
use App\Entity\Ingredient;
use App\Entity\Rating;
use App\Entity\Recipe;
use App\Entity\RecipeTags;
use App\Entity\Signalement;
use App\Entity\User;
use App\Form\SearchRecipeType;
use App\Repository\DemandeRepository;
use App\Repository\CommentRepository;
use App\Repository\RecipeRepository;
use App\Repository\SignalementRepository;
use App\Services\MarmitonManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route("/admin", name: "admin_")]
class AdminController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route("/", name: 'index')]
    public function index(SignalementRepository $signalementRepository, DemandeRepository $demandeRepository, RecipeRepository $recipeRepository, ParameterBagInterface $parameterBag): Response
    {
        //find last 5 signalement where traiter is not true
        $signalements = $signalementRepository->findBy(['traiter' => false], ['create_at' => 'DESC'], 5);
        $demandes = $demandeRepository->findBy(['accept' => false], ['send_at' => 'DESC'], 5);
        $recipes = $recipeRepository->findBy([], ['created_at' => 'DESC'], 10);

        return $this->render('admin/index.html.twig', [
            'comments' => $signalements,
            'demandes' => $demandes,
            'recipes' => $recipes,
        ]);
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

    #[Route("/tags", name: 'tags')]
    public function show_tags(): Response
    {
        $repository = $this->getDoctrine()->getRepository(RecipeTags::class);
        $tags = $repository->findAll();
        return $this->render('admin/tags/show_tags.html.twig', [
            'tags' => $tags,
        ]);
    }

    #[Route("/recipes",name: 'recipes')]
    public function show_recipes(Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Recipe::class);

        $form = $this->createForm(SearchRecipeType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !empty(array_filter($form->getData()))) {
            $data = $form->getData();
            $recettesResults = $repository->findBySearch($data);
        } else
            $recettesResults = $repository->findBy([], ['created_at' => 'DESC']);

        /*
        $recettes = $paginator->paginate(
            $recettesResults,
            $request->query->getInt('page', 1),
            8
        );
        */

        return $this->render('admin/recipes/show_recipes.html.twig', [
            'recipes' => $recettesResults,
            'form' => $form->createView(),
        ]);
    }

    #[Route("/set",name: 'set_admin')]
    public function setAdmin(): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $user->setRoles(["ROLE_ADMIN"]);
        $this->entityManager->persist($user)->flush();
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
        $role = $user->getRoles();
        $role[] = "ROLE_CHIEF";
        $user->setRoles($role);
        $this->entityManager->persist($demande);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Le role chef a bien été attribué');
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
        $this->entityManager->remove($signalement->getMessage())->flush();
        return $this->redirectToRoute('admin_report_comment');
    }

    #[Route('/accept-comment/{id}', name: 'accept_comment')]
    public function acceptComment(Signalement $signalement): Response
    {
        $signalement->setTraiter(true);
        $this->entityManager->persist($signalement)->flush();
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
        $this->entityManager->persist($demande);
        $this->entityManager->flush();
        // do anything else you need here, like send an email

        return $this->redirectToRoute('admin_accept_demande');
    }
}
