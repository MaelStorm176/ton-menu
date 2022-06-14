<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\MonMenu;
use App\Entity\RecipeTags;
use App\Entity\RecipeIngredients;
use App\Entity\Ingredients;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class RandomRecipeController extends AbstractController
{
    #[Route('/generation-plat', name: 'generation_plat')]
    public function index(): Response
    {
        $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $count = $repository->countRecipe();
        $rand = rand(1, $count);
        $recette = $repository->find($rand);

        return $this->render('generation_menu/index.html.twig', [
            'recipe' => $recette,
        ]);
    }

    #[Route('/generation-menu/{nb_jour}', name: 'generation_menu')]
    public function menu(Request $request, MailerInterface $mailer): Response
    {
        //get data in db from user
        if ($this->isGranted('ROLE_USER') == true) {
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->find($user->getId());
            $menu = $user->getMonMenu();

            if($menu != null){
                $menu2 = $menu->getMenuSave();

                if(isset($_POST['ingredient']) && $_POST['ingredient'] != null){
                    $all_ingredient = [];
                    $repository4 = $this->getDoctrine()->getRepository(RecipeIngredients::class);
                    for($v=0; $v<count($menu2); $v++){
                        for($b=0; $b<count($menu2[$v]); $b++){
                            $ingredient = $repository4->findBy(['recipe' => $menu2[$v][$b]]);
                            foreach($ingredient as $ing){
                                array_push($all_ingredient, $ing->getIngredient()->getName());
                            }
                        }
                    }
                    $email = (new TemplatedEmail())
                    ->from('tonmenu@mange.fr')
                    ->to($user->getEmail())
                    ->subject('Votre liste d\'ingrédients pour votre menu de la semaine')
                    ->htmlTemplate('ingredient/send.html.twig')
                    ->context([
                        'all_ingredient' => $all_ingredient,
                    ]);
                    $mailer->send($email);
                }

                $date_menu = $menu->getDateGenerate();
                $now = new \DateTime();
                $diff = $now->diff($date_menu);
    
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
                            array_push($rEntreM, $recette);
                        } else if ($i == 1) {
                            array_push($rPlatM, $recette);
                        } else if ($i == 2) {
                            array_push($rDessertM, $recette);
                        } else if ($i == 3) {
                            array_push($rEntreS, $recette);
                        } else if ($i == 4) {
                            array_push($rPlatS, $recette);
                        } else if ($i == 5) {
                            array_push($rDessertS, $recette);
                        }
                    }
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
        }else{
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
    }
}
