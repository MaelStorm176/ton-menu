<?php

namespace App\Controller;

use App\Entity\Ingredient;
use DateTime;
use App\Entity\Rating;
use App\Entity\Recipe;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Entity\RecipeIngredients;
use App\Entity\RecipeSteps;
use App\Form\CommentType;
use App\Form\RecetteType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RecipeController extends AbstractController
{
    /**
     * @Route("/recipe/add", name="recipe_add")
     */
    public function add_recette(Request $request, SluggerInterface $slugger): Response {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $recette = new Recipe();
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        $requete = $request->request->all();

        if ($form->isSubmitted() && $form->isValid()) {
            $today = new DateTimeImmutable();
            $recette->setUserId($user);
            $recette->setCreatedAt($today);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($recette);

            if (isset($requete["choosen_ingredient"]) && !empty($requete["choosen_ingredient"])){
                foreach ($requete["choosen_ingredient"] as $ingredient_id) {
                    $ingredient = $this->getDoctrine()->getRepository(Ingredient::class)->find($ingredient_id);
                    $recipeIngredient = new RecipeIngredients();
                    $recipeIngredient->setIngredient($ingredient);
                    $recipeIngredient->setRecipe($recette);
                    $entityManager->persist($recipeIngredient);
                }
            }

            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('recipe_show', [
                'id' => $recette->getId()
            ]);
        }

        return $this->render('new_recette/create.html.twig', [
            'recetteForm' => $form->createView(),
            'ingredients'=> $this->getDoctrine()->getRepository(Ingredient::class)->findAll()
        ]);
    }

    /**
     * @Route("/recipe/all", name="recipe_all")
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

    /**
     * @Route("/recipe/{id}", name="recipe_show")
     */
    public function show_recette(Recipe $recette, Request $request): Response{
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $repository = $this->getDoctrine()->getRepository(Rating::class);
        $rating = $repository->findOneBy(
            ['user' => $user, 'recette' => $recette],
        );

        $repository2 = $this->getDoctrine()->getRepository(Comment::class);
        $commentary = $repository2->findAll();

        $repository3 = $this->getDoctrine()->getRepository(RecipeSteps::class);
        $step = $repository3->findBy(['recipe' => $recette->getId()]);

        $repository4 = $this->getDoctrine()->getRepository(RecipeIngredients::class);
        $ingredient = $repository4->findBy(['recipe' => $recette->getId()]);

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
            'step' => $step,
            'ingredient' => $ingredient,
        ]);
    }

    /**
     * @Route ("/recipe/{id}/ingredients", name="recipe_show_ingredients")
     * @param Recipe $recipe
     * @param Request $request
     * @return Response
     */
    public function show_ingredients(Recipe $recipe, Request $request){
        $ingredients = $recipe->getIngredients();
        $array_ingredients = [];
        foreach ($ingredients as $ingredient){
            $array_ingredients []= $ingredient->getIngredient();
        }

        return $this->render('admin/recipes/table_ingredients.html.twig',[
            'ingredients' => $array_ingredients
        ]);
    }
}
