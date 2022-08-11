<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\MonMenu;
use App\Entity\Ingredient;
use App\Entity\RecipeTags;
use App\Entity\SavedMenus;
use App\Entity\Ingredients;
use App\Entity\RecipeIngredients;
use App\Form\IngredientFilterType;
use App\Repository\RecipeRepository;
use App\Repository\SavedMenusRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RandomRecipeController extends AbstractController
{
    private RecipeRepository $recipeRepository;
    private SavedMenusRepository $savedMenusRepository;
    public function __construct(RecipeRepository $recipeRepository, SavedMenusRepository $savedMenusRepository)
    {
        //get recipe repository
        $this->recipeRepository = $recipeRepository;
        $this->savedMenusRepository = $savedMenusRepository;
    }

    #[Route('/generation-plat', name: 'generation_plat')]
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $rand = rand(1, $repository->countRecipe());
        $recette = $repository->find($rand);

        return $this->render('generation_menu/index.html.twig', [
            'recipe' => $recette,
        ]);
    }

    /*
    #[Route('/generation-menu/{nb_jour}', name: 'generation_menu')]
    public function menu(Request $request, MailerInterface $mailer): Response
    {
        //Si l'utilisateur est connecté
        if ($this->isGranted('ROLE_USER')) {
            $userId = $this->get('security.token_storage')->getToken()->getUser()->getId(); //id_user
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->find($userId);

            //Menu de l'user
            $menu = $user->getMonMenu();

            if($menu != null){
                $menu2 = $menu->getMenuSave();
                //dd($request->get("ingredient"),$_POST);
                if(isset($_POST['ingredient']) && $_POST['ingredient'] != null){
                    $all_ingredient = [];
                    $ingredientRepository = $this->getDoctrine()->getRepository(RecipeIngredients::class);
                    for($v=0; $v<count($menu2); $v++){
                        for($b=0; $b<count($menu2[$v]); $b++){
                            $ingredient = $ingredientRepository->findBy(['recipe' => $menu2[$v][$b]]);
                            foreach($ingredient as $ing){
                                $all_ingredient[] = $ing->getIngredient()->getName();
                            }
                        }
                        //break;
                    }
                    var_dump($count_ingredient);
                    var_dump($i);
                    $email = (new TemplatedEmail())
                    ->from('tonmenu@mange.fr')
                    ->to($user->getEmail())
                    ->subject("Votre liste d'ingrédients pour votre menu de la semaine")
                    ->htmlTemplate('ingredient/send.html.twig')
                    ->context([
                        'all_ingredient' => $all_ingredient,
                        'count_ingredient' => $count_ingredient,
                    ]);
                    $mailer->send($email);
                }

                $now = new \DateTime();
                $diff = $now->diff($menu->getDateGenerate());
    
                if ($diff->d > count($menu2[0])) {
                    $user->setMonMenu(null);
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                }
            }

            if (isset($menu) && $menu->getMenuSave() != "" && !isset($_POST['change'])) {
                $menu_save = $menu->getMenuSave();
                $rEntreM = [];
                $rPlatM = [];
                $rDessertM = [];

                $rEntreS = [];
                $rPlatS = [];
                $rDessertS = [];

                $j = [];

                $repository = $this->getDoctrine()->getRepository(Recipe::class);

                $repository2 = $this->getDoctrine()->getRepository(RecipeTags::class);
                $tags = $repository2->findAll();

                for ($i = 0; $i < count($menu_save); $i++) {
                    for ($t = 0; $t < count($menu_save[$i]); $t++) {
                        $recette = $repository->find($menu_save[$i][$t]);

                        if ($i == 0) {
                            $rEntreM[] = $recette;
                        } else if ($i == 1) {
                            $rPlatM[] = $recette;
                        } else if ($i == 2) {
                            $rDessertM[] = $recette;
                        } else if ($i == 3) {
                            $rEntreS[] = $recette;
                        } else if ($i == 4) {
                            $rPlatS[] = $recette;
                        } else if ($i == 5) {
                            $rDessertS[] = $recette;
                        }
                    }
                    $j[] = $i;
                }
                return $this->render('generation_menu/index.html.twig', [
                    'entreeM' => $rEntreM,
                    'platM' => $rPlatM,
                    'dessertM' => $rDessertM,
                    'entreeS' => $rEntreS,
                    'platS' => $rPlatS,
                    'dessertS' => $rDessertS,
                    'cpt' => $j,
                    'tags' => $tags,
                    'msg' => "Importation du menu sauvegardé",
                ]);
            } else {
                $repository = $this->getDoctrine()->getRepository(Recipe::class);
                $countE = $repository->countEntreeRecipe();
                $countP = $repository->countPlatRecipe();
                $countD = $repository->countDessertRecipe();

                $repository2 = $this->getDoctrine()->getRepository(RecipeTags::class);
                $tags = $repository2->findAll();

                $rEntreM = [];
                $rPlatM = [];
                $rDessertM = [];

                $rEntreS = [];
                $rPlatS = [];
                $rDessertS = [];

                $rEntreM1 = [];
                $rPlatM1 = [];
                $rDessertM1 = [];

                $rEntreS1 = [];
                $rPlatS1 = [];
                $rDessertS1 = [];

                $j = [];

                $test = $request->get('nb_jour');

                $randEM = array_rand($countE, $test);
                $randPM = array_rand($countP, $test);
                $randDM = array_rand($countD, $test);

                $randES = array_rand($countE, $test);
                $randPS = array_rand($countP, $test);
                $randDS = array_rand($countD, $test);

                for ($i = 0; $i < count($randEM); $i++) {
                    $recette1 = $repository->find($countE[$randEM[$i]]['id']);
                    $recette2 = $repository->find($countP[$randPM[$i]]['id']);
                    $recette3 = $repository->find($countD[$randDM[$i]]['id']);

                    $recette4 = $repository->find($countE[$randES[$i]]['id']);
                    $recette5 = $repository->find($countP[$randPS[$i]]['id']);
                    $recette6 = $repository->find($countD[$randDS[$i]]['id']);


                    $rEntreM[] = $recette1;
                    $rPlatM[] = $recette2;
                    $rDessertM[] = $recette3;
                    $rEntreS[] = $recette4;
                    $rPlatS[] = $recette5;
                    $rDessertS[] = $recette6;

                    $rEntreM1[] = $recette1->getId();
                    $rPlatM1[] = $recette2->getId();
                    $rDessertM1[] = $recette3->getId();
                    $rEntreS1[] = $recette4->getId();
                    $rPlatS1[] = $recette5->getId();
                    $rDessertS1[] = $recette6->getId();

                    $j[] = $i;
                }

                $allPlat = [];
                array_push($allPlat, $rEntreM1, $rPlatM1, $rDessertM1, $rEntreS1, $rPlatS1, $rDessertS1);
                //generate date immutable
                //var_dump($allPlat[0][0]->getName());
                $date = new \DateTimeImmutable();
                $date->format('Y-m-d H:i:s');

                $em = $this->getDoctrine()->getManager();
                $menu = new MonMenu();
                $menu->setDateGenerate($date);
                $menu->setMenuSave($allPlat);
                $em->persist($menu);
                $em->flush();

                //add id menu generate in user database
                $user = $this->getUser();
                $user->setMonMenu($menu);
                $em->persist($user);
                $em->flush();

                return $this->render('generation_menu/index.html.twig', [
                    'entreeM' => $rEntreM,
                    'platM' => $rPlatM,
                    'dessertM' => $rDessertM,
                    'entreeS' => $rEntreS,
                    'platS' => $rPlatS,
                    'dessertS' => $rDessertS,
                    'cpt' => $j,
                    'tags' => $tags,
                    'msg' => "Nouveau menu sauvegarder",
                ]);
            }
        }
        else{
            $repository = $this->getDoctrine()->getRepository(Recipe::class);
            $countE = $repository->countEntreeRecipe();
            $countP = $repository->countPlatRecipe();
            $countD = $repository->countDessertRecipe();

            $repository2 = $this->getDoctrine()->getRepository(RecipeTags::class);
            $tags = $repository2->findAll();

            $rEntreM = [];
            $rPlatM = [];
            $rDessertM = [];

            $rEntreS = [];
            $rPlatS = [];
            $rDessertS = [];

            $rEntreM1 = [];
            $rPlatM1 = [];
            $rDessertM1 = [];

            $rEntreS1 = [];
            $rPlatS1 = [];
            $rDessertS1 = [];

            $j = [];

            $test = $request->get('nb_jour');

            $randEM = array_rand($countE, $test);
            $randPM = array_rand($countP, $test);
            $randDM = array_rand($countD, $test);

            $randES = array_rand($countE, $test);
            $randPS = array_rand($countP, $test);
            $randDS = array_rand($countD, $test);

            for ($i = 0; $i < count($randEM); $i++) {
                $recette1 = $repository->find($countE[$randEM[$i]]['id']);
                $recette2 = $repository->find($countP[$randPM[$i]]['id']);
                $recette3 = $repository->find($countD[$randDM[$i]]['id']);

                $recette4 = $repository->find($countE[$randES[$i]]['id']);
                $recette5 = $repository->find($countP[$randPS[$i]]['id']);
                $recette6 = $repository->find($countD[$randDS[$i]]['id']);


                array_push($rEntreM, $recette1);
                array_push($rPlatM, $recette2);
                array_push($rDessertM, $recette3);
                array_push($rEntreS, $recette4);
                array_push($rPlatS, $recette5);
                array_push($rDessertS, $recette6);

                array_push($rEntreM1, $recette1->getId());
                array_push($rPlatM1, $recette2->getId());
                array_push($rDessertM1, $recette3->getId());
                array_push($rEntreS1, $recette4->getId());
                array_push($rPlatS1, $recette5->getId());
                array_push($rDessertS1, $recette6->getId());

                array_push($j, $i);
            }

            return $this->render('generation_menu/index.html.twig', [
                'entreeM' => $rEntreM,
                'platM' => $rPlatM,
                'dessertM' => $rDessertM,
                'entreeS' => $rEntreS,
                'platS' => $rPlatS,
                'dessertS' => $rDessertS,
                'cpt' => $j,
                'tags' => $tags,
                'msg' => "Nouveau menu générer",
            ]);
        }
    }*/

    #[Route('/ajax/generation-menu/save_menu', name: 'save_menu', methods: ['POST'])]
    public function saveMenu(Request $request)
    {
        if ($request->get("menu") && json_decode($request->get("menu"), true) && $request->get("nb_jour")) {
            $em = $this->getDoctrine()->getManagerForClass(SavedMenus::class);
            $savedMenuRepository = $em->getRepository(SavedMenus::class);

            $menuJson = json_decode($request->get("menu"), true);
            $user = $this->getUser();
            $id_menu = $request->get("id_menu", 0);
            if ($id_menu == 0) {
                $savedMenu = new SavedMenus();
                $savedMenu->setUser($user);
            } else {
                $savedMenu = $savedMenuRepository->find($id_menu);
                $savedMenu->setUpdatedAt(new \DateTimeImmutable());
                if ($savedMenu->getUser()->getId() != $user->getId()) {
                    return new JsonResponse(["error" => "Vous n'avez pas le droit d'éditer ce menu"]);
                }
            }
            $savedMenu->setRecipes($menuJson);
            $em->persist($savedMenu);
            $em->flush();
            return $this->redirectToRoute('generation_menu', [
                'id_menu' => $savedMenu->getId(),
                'nb_jour' => $request->get("nb_jour"),
            ]);
        } else {
            return new Response("Erreur");
        }
    }

    #[Route('/ajax/generation-menu/refresh', name: 'refresh_menu', methods: ['GET'])]
    public function refresh(Request $request): Response
    {
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
    }
    /*
    public function refresh(Request $request): JsonResponse
    {
        $type = $request->get("type");
        if ($request->get("day")) {
            return new JsonResponse([
                "success" => true,
                "data" => [
                    "entree" => $this->randomEntrees(),
                    "plat" => $this->randomPlats(),
                    "dessert" => $this->randomDesserts(),
                ]
            ]);
        } elseif ($type == "ENTREE") {
            return new JsonResponse([
                "success" => true,
                "data" => $this->randomEntrees()
            ]);
        } elseif ($type == "PLAT") {
            return new JsonResponse([
                "success" => true,
                "data" => $this->randomPlats()
            ]);
        } elseif ($type == "DESSERT") {
            return new JsonResponse([
                "success" => true,
                "data" => $this->randomDesserts()
            ]);
        } else {
            return new JsonResponse(array(
                'success' => false,
                'msg' => 'Erreur lors de la récupération des recettes'
            ));
        }
    }*/

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
                $entrees = $this->getDoctrine()->getRepository(Recipe::class)->findBy(["id" => $menus["entrees"]]);
                $plats = $this->getDoctrine()->getRepository(Recipe::class)->findBy(["id" => $menus["plats"]]);
                $desserts = $this->getDoctrine()->getRepository(Recipe::class)->findBy(["id" => $menus["desserts"]]);
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
                    return new JsonResponse(["error" => "Erreur lors de l'envoi du mail" . $e->getMessage()]);
                }
            }
            return new JsonResponse(["success" => true, "msg" => "Votre menu a été envoyé à votre adresse email"]);
        } else {
            return new JsonResponse(["error" => "Erreur"]);
        }
    }

    #[Route('/generation-menu/{nb_jour}', name: 'generation_menu', methods: ['GET'])]
    public function menu(Request $request): Response
    {
        //Si l'utilisateur est connecté
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $countGener = $user->getGeneratedCounter();
        $countGener++;
        $user->setGeneratedCounter($countGener);

        $nb_jour = (int) $request->get('nb_jour');

        $form = $this->createForm(IngredientFilterType::class, null, ['method' => 'GET']);
        $form->handleRequest($request);
        //Si je demande à voir un menu en particulier
        if ($form->isSubmitted() && $form->isValid() && !empty(array_filter($form->getData()))) {
            $data = $form->getData();
            $refuseRecipe = $this->getDoctrine()->getRepository(Recipe::class)->findByIngredients($data["ingredients"]);
        } else {
            $refuseRecipe = [];
        }

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
                $entrees = $this->getDoctrine()->getRepository(Recipe::class)->findBy(['id' => $idsRecipes['entrees']]);
                $plats = $this->getDoctrine()->getRepository(Recipe::class)->findBy(['id' => $idsRecipes['plats']]);
                $desserts = $this->getDoctrine()->getRepository(Recipe::class)->findBy(['id' => $idsRecipes['desserts']]);
                $json_menu = json_encode($idsRecipes);
            } else {
                return $this->redirectToRoute('home');
            }
        }

        //Si je genere un menu
        else {
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
        $manager->persist($user);
        $manager->flush();
        //Si on a bien générer des entrees, plats et desserts, on peut rendre la vue
        if ($entrees && $plats && $desserts && count($entrees) == count($plats) && count($plats) == count($desserts)) {
            return $this->render('generation_menu/index.html.twig', [
                'id_menu' => $id_menu,
                'entrees' => $entrees,
                'plats' => $plats,
                'desserts' => $desserts,
                'nb_jour' => $nb_jour,
                'json_menu' => $json_menu,
                'msg' => "Nouveau menu généré",
                'form' => $form->createView(),
                "countGener" => $countGener,
            ]);
        }
        //Si on n'a pas généré des entrees, plats et desserts, on redirige vers la page d'accueil
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/generation-by-ingredient", name="generation_by_ingredient")
     */
    public function generationByIngredient(Request $request): Response
    {
        $ingredientRepository = $this->getDoctrine()->getRepository(Ingredient::class);
        $requete = $request->request->all();

        if ($request->isMethod('POST')) {
            /** AJOUT DES INGREDIENTS A LA RECETTE **/
            if (isset($requete["choosen_ingredient"]) && !empty($requete["choosen_ingredient"])) {
                $myRecipe = $this->getDoctrine()->getRepository(Recipe::class)->findByIngredients($requete["choosen_ingredient"]);
                return $this->render('generation_menu/generation_by_ingredient.html.twig', [
                    'ingredients' => $this->getDoctrine()->getRepository(Ingredient::class)->findAll(),
                    'myRecipes' => $myRecipe,
                ]);
            }
        }
        return $this->render('generation_menu/generation_by_ingredient.html.twig', [
            'ingredients' => $this->getDoctrine()->getRepository(Ingredient::class)->findAll(),
            'myRecipes' => null,
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

    private function randomRecipes($type, $max = 1, $notIn = [])
    {
        $type = strtoupper($type);
        if (in_array($type, ["ENTREE", "PLAT", "DESSERT"])) {
            return $this->recipeRepository->getRandomRecipes($type, $max, $notIn);
        } else {
            return null;
        }
    }
}
