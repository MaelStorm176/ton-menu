<?php

namespace App\Controller;

use App\Entity\RecipeImages;
use App\Form\SearchRecipeType;
use App\Entity\Rating;
use App\Entity\Recipe;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\RecetteType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ingredient;
use App\Entity\RecipeTags;
use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RecipeController extends AbstractController
{

    private RecipeRepository $recipeRepository;
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $manager ,RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
        $this->entityManager = $manager;
    }

    /**
     * @Route("/recipe/add", name="recipe_add")
     */
    public function add_recette(Request $request, MailerInterface $mailer): Response
    {
        $recette = new Recipe();
        $user = $this->getUser();
        $followers = $user->getFollows();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {

                if ($request->files->get('images') != null) {
                    $images = $request->files->get('images');
                    foreach ($images as $image) {
                        $fichier = uniqid() . '.' . $image->guessExtension();
                        $image->move(
                            $this->getParameter('recipe_image_directory'),
                            $fichier
                        );
                        $recipeImage = new RecipeImages();
                        $recipeImage->setPath('/img/recipes/' . $fichier);
                        $recipeImage->setRecipe($recette);
                        $this->entityManager->persist($recipeImage);
                    }
                }

                /** AJOUT DES ETAPES A LA RECETTE **/
                $i = 1;
                foreach ($recette->getRecipeSteps() as $step) {
                    $step->setRecipe($recette);
                    $step->setOrdre($i);
                    $i++;
                }

                foreach ($recette->getRecipeQuantities() as $recipeQuantity) {
                    $recipeQuantity->setRecipe($recette);
                }

                $recette->setCreatedAt(new DateTimeImmutable());
                $recette->setUserId($user);
                $this->entityManager->persist($recette)->flush();
                foreach ($followers as $follower) {
                    $email = (new TemplatedEmail())
                        ->from('tonmenu@mange.fr')
                        ->to($follower->getEmail())
                        ->subject("Nouvelle recette de votre chef !")
                        ->htmlTemplate('new_recette/new.html.twig')
                        ->context([
                            'recette' => $recette,
                        ]);
                    try {
                        $mailer->send($email);
                    } catch (\Exception $e) {
                        return new JsonResponse(["error" => "Erreur lors de l'envoi du mail" . $e->getMessage()]);
                    }
                }

                $this->addFlash('success', 'Votre recette a bien été ajoutée !');
                return $this->redirectToRoute('recipe_show', [
                    'id' => $recette->getId()
                ]);
            }
        }

        return $this->render('new_recette/create.html.twig', [
            'recetteForm' => $form->createView(),
            'ingredients' => $this->getDoctrine()->getRepository(Ingredient::class)->findAll(),
            'tags' => $this->getDoctrine()->getRepository(RecipeTags::class)->findAll()
        ]);
    }

    /**
     * @Route("/recipe/edit/{id}", name="recipe_edit", requirements={"id"="\d+"}, methods={"GET", "POST"})
     */
    public function edit(Request $request, Recipe $recipe): Response
    {
        $form = $this->createForm(RecetteType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Votre recette a bien été modifiée !');
            return $this->redirectToRoute('recipe_show', [
                'id' => $recipe->getId()
            ]);
        }
        return $this->render('new_recette/create.html.twig', [
            'recetteForm' => $form->createView(),
            'ingredients' => $this->getDoctrine()->getRepository(Ingredient::class)->findAll(),
            'tags' => $this->getDoctrine()->getRepository(RecipeTags::class)->findAll()
        ]);
    }

    /**
     * @Route("/recipe/all", name="recipe_all", methods={"GET"})
     */
    public function show_all(RecipeRepository $recipeRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(SearchRecipeType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !empty(array_filter($form->getData()))) {
            $data = $form->getData();
            $recettesResults = $this->recipeRepository->findBySearch($data);
        } else
            $recettesResults = $recipeRepository->findBy([], ['created_at' => 'DESC']);

        $recettes = $paginator->paginate(
            $recettesResults,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('new_recette/home.html.twig', [
            'countRecettes' => count($recettesResults),
            'recettes' => $recettes,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/recipe/{id}", name="recipe_show")
     */
    public function show_recette(Recipe $recette, Request $request): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $repository = $this->getDoctrine()->getRepository(Rating::class);
        $rating = $repository->findOneBy(
            ['user' => $user, 'recette' => $recette],
        );

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

            return $this->redirectToRoute('recipe_show', [
                'id' => $recette->getId()
            ]);
        }

        return $this->render('new_recette/show.html.twig', [
            'recetteForm' => $form->createView(),
            'recette' => $recette,
            'rating' => $rating,
        ]);
    }

    /**
     * @Route ("/recipe/{id}/ingredients", name="recipe_show_ingredients", methods={"POST"})
     */
    public function show_ingredients(Recipe $recipe, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $ingredients = $recipe->getIngredients();
            return $this->render('admin/recipes/table_ingredients.html.twig', [
                'ingredients' => $ingredients
            ]);
        } else {
            return new Response('This is not ajax!', 400);
        }
    }
}
