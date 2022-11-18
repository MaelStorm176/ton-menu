<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Rating;
use App\Entity\Recipe;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Entity\Follower;
use App\Form\CommentType;
use App\Form\RecetteType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Ingredient;
use App\Entity\RecipeTags;
use App\Entity\RecipeImages;
use App\Form\SearchRecipeType;
use App\Repository\RecipeRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        $followers = $this->entityManager->getRepository(Follower::class)->findBy(['chef_id' => $user->getId()]);

        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);
        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {

            if ($request->files->get('images') !== null) {
                $images = $request->files->get('images');
                foreach ($images as $image) {
                    $fichier = uniqid('', true) . '.' . $image->guessExtension();
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
                $this->entityManager->persist($recette);
                $this->entityManager->flush();
                foreach ($followers as $follower) {
                    $follow = $this->entityManager->getRepository(User::class)->findOneBy(['id' => $follower->getUserId()]);
                    $email = (new TemplatedEmail())
                        ->from('tonmenu@mange.fr')
                        ->to($follow->getEmail())
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
            'recipe' => $recipe,
            'recetteForm' => $form->createView(),
            'ingredients' => $this->getDoctrine()->getRepository(Ingredient::class)->findAll(),
            'tags' => $this->getDoctrine()->getRepository(RecipeTags::class)->findAll()
        ]);
    }

    /**
     * @Route("/recipe/delete/{id}", name="recipe_delete", requirements={"id"="\d+"}, methods={"DELETE", "POST"})
     */
    public function delete(Request $request, Recipe $recipe): Response
    {
        $user = $this->getUser();

        if (!$user instanceof User)
        {
            $this->addFlash('error', 'Vous devez être connecté pour supprimer une recette');
            return $this->redirectToRoute('home');
        }
        if ($this->isCsrfTokenValid('delete'.$recipe->getId(), $request->request->get('_token')) && $user->getId() == $recipe->getUserId()->getId()) {
            $images = $recipe->getRecipeImages();
            foreach ($images as $image) {

                if ($image->getPath() && file_exists($this->getParameter('recipe_image_directory') . $image->getPath())) {
                    unlink($this->getParameter('recipe_image_directory') . $image->getPath());
                }
                $this->entityManager->remove($image);
            }
            $this->entityManager->remove($recipe);
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre recette a bien été supprimée !');
        }else{
            $this->addFlash('error', 'Vous n\'avez pas le droit de supprimer cette recette !');
        }
        if ($user->hasRole('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_recipes');
        }
        return $this->redirectToRoute('recipe_all');
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
        } else {
            $recettesResults = $recipeRepository->findBy([], ['created_at' => 'DESC']);
        }

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
     * @Route("/recipe/{id}", requirements={"id"="\d+"},name="recipe_show")
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
        }

        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route ("/recipe/{id}/tags", name="recipe_show_tags", methods={"POST"})
     */
    public function show_tags(Recipe $recipe, Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $tags = $recipe->getRecipeTags();
            return $this->render('admin/recipes/table_tags.html.twig', [
                'tags' => $tags
            ]);
        }

        return new Response('This is not ajax!', 400);
    }

    /**
     * @Route("/recipe/like/{id}", name="recipe_like", requirements={"id"="\d+"}, methods={"POST"})
     */
    public function like(Recipe $recipe, Request $request): Response|JsonResponse
    {
        if ($request->isXmlHttpRequest()){
            $user = $this->get('security.token_storage')->getToken()->getUser();
            if ($user instanceof User){

                if ($user->getId() == $recipe->getUserId()->getId()){
                    return new JsonResponse(['error' => 'Vous ne pouvez pas liker votre propre recette'], Response::HTTP_ACCEPTED);
                }
                if ($user->getRecipesLiked()->contains($recipe)){
                    // remove like
                    $user->removeRecipesLiked($recipe);
                    $this->entityManager->flush();
                    return new JsonResponse(['message' => 'Vous avez bien supprimé votre like'], Response::HTTP_OK);
                }
                $user->addRecipesLiked($recipe);
                $this->entityManager->flush();
                return new JsonResponse(['success' => true], Response::HTTP_OK);
            }

            return $this->json(['error' => 'Vous devez être connecté pour aimer une recette'], Response::HTTP_FORBIDDEN);
        }

        return new Response('This is not ajax!', 400);
    }
}
