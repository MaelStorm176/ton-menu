<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CommentRepository;
use App\Repository\RatingRepository;
use App\Repository\RecipeRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        $recipes = $recipeRepository->getRandomRecipes("PLAT", 3);

        return $this->render('home/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

}
