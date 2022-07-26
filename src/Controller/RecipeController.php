<?php

namespace App\Controller;

use DateTime;
use App\Entity\Rating;
use App\Entity\Recipe;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\RecetteType;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Entity\Ingredient;
use App\Entity\RecipeTags;
use App\Entity\RecipeSteps;
use App\Entity\RecipeTagsLinks;
use App\Entity\RecipeIngredients;
use App\Repository\RatingRepository;
use App\Repository\RecipeRepository;
use App\Repository\CommentRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class RecipeController extends AbstractController
{


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
                        $recipeIngredient = new RecipeIngredients();
                        $recipeIngredient->setIngredient($ingredient);
                        $recipeIngredient->setRecipe($recette);
                        $manager->persist($recipeIngredient);
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
     * @Route("/recipe/all", name="recipe_all")
     */
    public function show_all(RecipeRepository $recipeRepository, RatingRepository $ratingRepository, CommentRepository $commentRepository): Response
    {
        $recettes = $recipeRepository->findBy([], ['created_at' => 'DESC'], 10);
        return $this->render('new_recette/home.html.twig', [
            'recettes' => $recettes,
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

            return $this->redirectToRoute('recipe_show', [
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
    public function show_ingredients(Recipe $recipe, Request $request)
    {
        $ingredients = $recipe->getIngredients();
        $array_ingredients = [];
        foreach ($ingredients as $ingredient) {
            $array_ingredients[] = $ingredient->getIngredient();
        }

        return $this->render('admin/recipes/table_ingredients.html.twig', [
            'ingredients' => $array_ingredients
        ]);
    }

    /**
     * @Route("/handleSearch", name="handleSearch")
     * @param Request $request
     */
    public function handleSearch(Request $request, RecipeRepository $repo)
    {
        $query = $request->request->get('form')['query'];
        if($query) {
            $articles = $repo->findArticlesByName($query);
        }
        return $this->render('new_recette/mySearch.html.twig', [
            'articles' => $articles
        ]);
    } 

    public function searchBar()
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('handleSearch'))
            ->add('query', TextType::class, [
                'label' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez un mot-clé'
                ]
            ])
            ->add('recherche', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
            ->getForm();
        return $this->render('new_recette/search.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
