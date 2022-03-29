<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeTags;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function menu(Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $countE = $repository->countEntreeRecipe();
        $countP = $repository->countPlatRecipe();
        $countD = $repository->countDessertRecipe();

        $repository2 = $this->getDoctrine()->getRepository(RecipeTags::class);
        $tags = $repository2->findAll();

        $test3 = $request->request->all();
        //

        $rEntreM = [];
        $rPlatM = [];
        $rDessertM = [];

        $rEntreS = [];
        $rPlatS = [];
        $rDessertS = [];
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
            'test' => $countD[$randDM[0]]['id'],
            'tags' => $tags,
        ]);
    }
}
