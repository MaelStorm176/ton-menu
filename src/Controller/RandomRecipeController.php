<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\Ingredient;
use App\Entity\SavedMenus;
use App\Entity\User;
use App\Form\IngredientFilterType;
use App\Form\TonFrigoType;
use App\Repository\RecipeRepository;
use App\Repository\SavedMenusRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RandomRecipeController extends AbstractController
{
    private RecipeRepository $recipeRepository;
    private SavedMenusRepository $savedMenusRepository;
    const MAX_DAYS = 7;
    public function __construct(RecipeRepository $recipeRepository, SavedMenusRepository $savedMenusRepository)
    {
        //get recipe repository
        $this->recipeRepository = $recipeRepository;
        $this->savedMenusRepository = $savedMenusRepository;
    }

    /*#[Route('/generation-plat', name: 'generation_plat')]
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $rand = rand(1, $repository->countRecipe());
        $recette = $repository->find($rand);

        return $this->render('generation_menu/index.html.twig', [
            'recipe' => $recette,
        ]);
    }*/

    #[Route('/ajax/generation-menu/save_menu', name: 'save_menu', methods: ['POST'])]
    public function saveMenu(Request $request)
    {
        if ($request->get("menu") && json_decode($request->get("menu"), true) && $request->get("nb_jours")) {
            $em = $this->getDoctrine()->getManagerForClass(SavedMenus::class);

            $menuJson = json_decode($request->get("menu"), true);
            $user = $this->getUser();
            if (!$user instanceof User)
                return $this->createAccessDeniedException("Vous devez être connecté pour sauvegarder un menu");

            $id_menu = $request->get("id_menu", 0);
            if ($id_menu == 0) {
                $savedMenu = new SavedMenus();
                $savedMenu->setUser($user);
            } else {
                $savedMenu = $this->savedMenusRepository->find($id_menu);
                $savedMenu->setUpdatedAt(new \DateTimeImmutable());
                if ($savedMenu->getUser()->getId() != $user->getId()) {
                    return $this->createAccessDeniedException("Vous n'avez pas le droit de modifier ce menu");
                }
            }
            $savedMenu->setRecipes($menuJson);
            $em->persist($savedMenu);
            $em->flush();

            $this->addFlash('success', 'La génération de menus a bien été sauvegardée');
            return $this->redirectToRoute('generation_menu', [
                'id_menu' => $savedMenu->getId(),
                'nb_jours' => $request->get("nb_jours"),
            ]);
        } else {
            return $this->createNotFoundException("Menu non valide");
        }
    }

    #[Route('/ajax/generation-menu/refresh', name: 'refresh_menu', methods: ['GET'])]
    public function refresh(Request $request): Response
    {
        if ($request->isXmlHttpRequest()){
            $type = $request->get("type");
            if ($type == "ENTREE") {
                return new Response($this->renderView('components/recipe_thumbnail.html.twig', ['recipe' => $this->randomEntrees()[0], 'reload' => true]));
            } elseif ($type == "PLAT") {
                return new Response($this->renderView('components/recipe_thumbnail.html.twig', ['recipe' => $this->randomPlats()[0], 'reload' => true]));
            } elseif ($type == "DESSERT") {
                return new Response($this->renderView('components/recipe_thumbnail.html.twig', ['recipe' => $this->randomDesserts()[0], 'reload' => true]));
            } else {
                return new JsonResponse(array(
                    'success' => false,
                    'msg' => 'Erreur lors de la récupération des recettes'
                ));
            }
        } else {
            return new Response('This is not ajax!', 400);
        }
    }

    #[Route('/ajax/generation-menu/send', name: 'send_to_mail', methods: ['POST'])]
    public function send_to_mail(Request $request, MailerInterface $mailer): JsonResponse
    {
        if ($request->get("menu") && json_decode($request->get("menu"), true) && $request->get("nb_jours")) {
            $menuJson = json_decode($request->get("menu"), true);
            $user = $this->getUser();
            if ($user->getEmail() == null) {
                return new JsonResponse(["error" => "Vous n'avez pas d'email"]);
            }
            if (is_array($menuJson)) {
                $menus = $menuJson;
                $entrees = $this->recipeRepository->findBy(["id" => $menus["entrees"]]);
                $plats = $this->recipeRepository->findBy(["id" => $menus["plats"]]);
                $desserts = $this->recipeRepository->findBy(["id" => $menus["desserts"]]);
                $nb_jours = $request->get("nb_jours");
                $email = (new TemplatedEmail())
                    ->from('tonmenu@mange.fr')
                    ->to($user->getEmail())
                    ->subject("Votre liste d'ingrédients pour votre menu de la semaine")
                    ->htmlTemplate('ingredient/send.html.twig')
                    ->context([
                        'nb_jours' => $nb_jours,
                        'entrees' => $entrees,
                        'plats' => $plats,
                        'desserts' => $desserts,
                    ]);
                try {
                    $mailer->send($email);
                } catch (\Exception $e) {
                    return new JsonResponse(["error" => "Erreur lors de l'envoi du mail " . $e->getMessage()]);
                } catch (TransportExceptionInterface $e) {
                    return new JsonResponse(["error" => "Erreur lors de l'envoi du mail " . $e->getMessage()]);
                }
            }
            return new JsonResponse(["success" => true, "msg" => "Votre menu a été envoyé à votre adresse email"]);
        } else {
            return new JsonResponse(["error" => "Erreur"]);
        }
    }

    #[Route('/generation-menu', name: 'generation_menu', methods: ['GET'])]
    public function menu(Request $request): Response
    {
        //Si l'utilisateur est connecté
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $form = $this->createForm(IngredientFilterType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        //Nb de jours par défaut
        $nb_jour = $request->get('nb_jours', self::MAX_DAYS);

        //Si je demande à voir un menu en particulier
        $refuseRecipe = [];
        if ($form->isSubmitted() && $form->isValid() && !empty(array_filter($form->getData()))) {
            $data = $form->getData();
            $refuseRecipe = $this->recipeRepository->findByIngredients($data["ingredients"]);
            if ($data["nb_jours"] && $data["nb_jours"] > 0 && $data["nb_jours"] < self::MAX_DAYS) {
                $nb_jour = $data["nb_jours"];
            }
        }

        //Si je demande à voir un menu en particulier
        if ($id_menu = $request->get('id_menu')) {
            $monMenu = $this->savedMenusRepository->find($id_menu);
            if ($monMenu && $monMenu->getUser()->getId() == $user->getId()) {
                $nb_jour_mon_menu = $monMenu->getNbJours();
                if ($nb_jour != $nb_jour_mon_menu) {
                    //Si le nombre de jour demandé est SUPERIEUR au nombre de jours du menu en base
                    if ($nb_jour > $nb_jour_mon_menu) {
                        $diff = ($nb_jour * 2) - ($nb_jour_mon_menu * 2);

                        $newEntrees = $this->randomEntrees($diff, $monMenu->getRecipes()['entrees']);
                        $newPlats = $this->randomPlats($diff, $monMenu->getRecipes()['plats']);
                        $newDesserts = $this->randomDesserts($diff, $monMenu->getRecipes()['desserts']);

                        $newEntreesIds = array_map(function ($e) {
                            return $e->getId();
                        }, $newEntrees);
                        $newPlatsIds = array_map(function ($p) {
                            return $p->getId();
                        }, $newPlats);
                        $newDessertsIds = array_map(function ($d) {
                            return $d->getId();
                        }, $newDesserts);


                        $newEntreesRecipes = array_merge($monMenu->getRecipes()["entrees"], $newEntreesIds);
                        $newPlatsRecipes = array_merge($monMenu->getRecipes()["plats"], $newPlatsIds);
                        $newDessertsRecipes = array_merge($monMenu->getRecipes()["desserts"], $newDessertsIds);
                    }
                    //Si le nombre de jour demandé est INFERIEUR au nombre de jours du menu en base
                    else {
                        $newEntreesRecipes = $monMenu->getRecipes()["entrees"] = array_slice($monMenu->getRecipes()["entrees"], 0, $nb_jour * 2);
                        $newPlatsRecipes = $monMenu->getRecipes()["plats"] = array_slice($monMenu->getRecipes()["plats"], 0, $nb_jour * 2);
                        $newDessertsRecipes = $monMenu->getRecipes()["desserts"] = array_slice($monMenu->getRecipes()["desserts"], 0, $nb_jour * 2);
                    }
                    $monMenu->setRecipes([
                        "entrees" => $newEntreesRecipes,
                        "plats" => $newPlatsRecipes,
                        "desserts" => $newDessertsRecipes,
                    ]);
                    $em = $this->getDoctrine()->getManagerForClass(SavedMenus::class);
                    $em->persist($monMenu);
                    $em->flush();
                }
                $idsRecipes = $monMenu->getRecipes(); //[entrees: [id, id, id], plats: [id, id, id], desserts: [id, id, id]]

                $entrees = $this->sortRecipes($this->recipeRepository->findBy(['id' => $idsRecipes['entrees']]), $idsRecipes['entrees']);
                $plats = $this->sortRecipes($this->recipeRepository->findBy(['id' => $idsRecipes['plats']]), $idsRecipes['plats']);
                $desserts = $this->sortRecipes($this->recipeRepository->findBy(['id' => $idsRecipes['desserts']]), $idsRecipes['desserts']);

                $json_menu = json_encode($idsRecipes);
                $countGener = $user->getGeneratedCounter();

            } else {
                $this->addFlash('error', 'Ce menu n\'existe pas');
                return $this->redirectToRoute('home');
            }
        }

        //Si je genere un menu
        else {
            if ($user instanceof User) {
                $countGener = $user->getGeneratedCounter() + 1;
                $user->setGeneratedCounter($countGener);
                $manager->persist($user);
                $manager->flush();
            }

            $nb_matin_soir = $nb_jour * 2;
            $id_menu = 0;

            $entrees = $this->randomEntrees($nb_matin_soir, $refuseRecipe);
            $plats = $this->randomPlats($nb_matin_soir, $refuseRecipe);
            $desserts = $this->randomDesserts($nb_matin_soir, $refuseRecipe);

            $entreesIds = array_map(function ($e) {
                return $e->getId();
            }, $entrees);
            $platsIds = array_map(function ($p) {
                return $p->getId();
            }, $plats);
            $dessertsIds = array_map(function ($d) {
                return $d->getId();
            }, $desserts);

            $json_menu = json_encode([
                "entrees" => $entreesIds,
                "plats" => $platsIds,
                "desserts" => $dessertsIds,
            ]);
        }

        //Si on a bien générer des entrees, plats et desserts, on peut rendre la vue
        if ($entrees && $plats && $desserts && count($entrees) == count($plats) && count($plats) == count($desserts)) {
            return $this->render('generation_menu/index.html.twig', [
                'id_menu' => $id_menu,
                'entrees' => $entrees,
                'plats' => $plats,
                'desserts' => $desserts,
                'nb_jour' => $nb_jour,
                'json_menu' => $json_menu,
                'form' => $form->createView(),
                "countGener" => $countGener ?? 20,
            ]);
        }
        //Si on n'a pas généré des entrees, plats et desserts, on redirige vers la page d'accueil
        else {
            $this->addFlash('error', 'Il n\'y a pas assez de recettes disponibles pour générer un menu');
            return $this->redirectToRoute('home');
        }
    }

    /**
     * @Route("/generation-by-ingredient", name="generation_by_ingredient")
     */
    public function generationByIngredient(Request $request): Response
    {
        $ingredientRepository = $this->getDoctrine()->getRepository(Ingredient::class);
        $form = $this->createForm(TonFrigoType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            //dd($data);
            $myRecipe = $this->recipeRepository->getBySearchQueryBuilder($data)->orderBy('RAND()')->setMaxResults(1)->getQuery()->getResult();
            //dd($myRecipe);
            return $this->render('generation_menu/generation_by_ingredient.html.twig', [
                'ingredients' => $ingredientRepository->findAll(),
                'myRecipes' => $myRecipe,
                "form" => $form->createView(),
            ]);
        }
        return $this->render('generation_menu/generation_by_ingredient.html.twig', [
            'ingredients' => $ingredientRepository->findAll(),
            'myRecipes' => null,
            "form" => $form->createView(),
        ]);
    }


    private function randomEntrees($nb_matin_soir = 1, $not_in = [])
    {
        return $this->randomRecipes("ENTREE", $nb_matin_soir, $not_in);
    }

    private function randomPlats($nb_matin_soir = 1, $not_in = [])
    {
        return $this->randomRecipes("PLAT", $nb_matin_soir, $not_in);
    }

    private function randomDesserts($nb_matin_soir = 1, $not_in = [])
    {
        return $this->randomRecipes("DESSERT", $nb_matin_soir, $not_in);
    }

    protected function randomRecipes($type, $max = 1, $notIn = [])
    {
        $type = strtoupper($type);
        if (in_array($type, ["ENTREE", "PLAT", "DESSERT"])) {
            return $this->recipeRepository->getRandomRecipes($type, $max, $notIn);
        } else {
            return null;
        }
    }

    private function sortRecipes($recipes, $order)
    {
        $new_recipes = array_flip($order);
        usort($recipes, function($a, $b) use ($new_recipes) {
            return $new_recipes[$a->getId()] <=> $new_recipes[$b->getId()];
        });
        return $recipes;
    }
}
