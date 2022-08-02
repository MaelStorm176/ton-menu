<?php

namespace App\Controller;

use App\Form\SearchRecipeType;
use App\Entity\Rating;
use App\Entity\Recipe;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\RecetteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Ingredient;
use App\Entity\RecipeTags;
use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{

    private $recipeRepository;
    public function __construct(RecipeRepository $recipeRepository)
    {
        $this->recipeRepository = $recipeRepository;
    }

    /**
     * @Route("/recipe/add", name="recipe_add")
     */
    public function add_recette(Request $request): Response {
        $manager = $this->getDoctrine()->getManager();
        $ingredientRepository = $this->getDoctrine()->getRepository(Ingredient::class);
        $tagRepository = $this->getDoctrine()->getRepository(RecipeTags::class);
        $recette = new Recipe();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        $requete = $request->request->all();
        if ($request->isMethod('POST')) {
            if ($form->isSubmitted() && $form->isValid()) {

                /** AJOUT DES INGREDIENTS A LA RECETTE **/
                if (isset($requete["choosen_ingredient"]) && !empty($requete["choosen_ingredient"])) {
                    foreach ($requete["choosen_ingredient"] as $item) {
                        $ingredient = $ingredientRepository->find($item);
                        $recette->addIngredient($ingredient);
                    }
                }

                /** AJOUT DES TAGS A LA RECETTE **/
                if (isset($requete["choosen_tag"]) && !empty($requete["choosen_tag"])) {
                    foreach ($requete["choosen_tag"] as $item) {
                        $tag = $tagRepository->find($item);
                        $recette->addRecipeTag($tag);
                    }
                }

                /** AJOUT DES ETAPES A LA RECETTE **/
                $i = 1;
                foreach ($recette->getRecipeSteps() as $step) {
                    $step->setRecipe($recette);
                    $step->setOrdre($i);
                    $i++;
                }

                $recette->setCreatedAt(new DateTimeImmutable());
                $recette->setUserId($this->getUser());
                $manager->persist($recette);
                $manager->flush();

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
     * @Route("/recipe/all", name="recipe_all", methods={"GET"})
     */
    public function show_all(RecipeRepository $recipeRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $form = $this->createForm(SearchRecipeType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && !empty(array_filter($form->getData()))) {
            $data = $form->getData();
            $recettesResults = $this->recipeRepository->findBySearch($data);
        }
        else
            $recettesResults = $recipeRepository->findBy([], ['created_at' => 'DESC']);

        $recettes = $paginator->paginate(
            $recettesResults,
            $request->query->getInt('page', 1),
            8
        );

        return $this->render('new_recette/home.html.twig', [
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
        if ($request->isXmlHttpRequest()){
            $ingredients = $recipe->getIngredients();
            return $this->render('admin/recipes/table_ingredients.html.twig', [
                'ingredients' => $ingredients
            ]);
        }else{
            return new Response('This is not ajax!', 400);
        }
    }
}
