<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\Recipe;
use App\Repository\RatingRepository;
use App\Repository\RecipeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RatingController extends AbstractController
{
    private $ratingRepository;
    private $recipeRepository;
    public function __construct(RatingRepository $ratingRepository, RecipeRepository $recipeRepository){
        $this->ratingRepository = $ratingRepository;
        $this->recipeRepository = $recipeRepository;
    }

    #[Route('/rate/{id}', name: 'rate', methods: ['POST'])]
    public function index(Request $request, $id): JsonResponse|Response
    {
        $user = $this->getUser();
        $rateInput = $request->request->get('rating');
        $recipe = $this->recipeRepository->find($id);
        $rate = $this->ratingRepository->findBy([
            "recette" => $recipe,
            "user" => $user
        ]);

        if ($request->isXmlHttpRequest() && $recipe && $rateInput) {
            if (!empty($rate))
                return new JsonResponse(["error" => "Vous avez déjà noté cette recette"], Response::HTTP_BAD_REQUEST);
            if ($rateInput < 0 || $rateInput > 5) {
                return new JsonResponse(["error" => "Valeur invalide"], 400);
            }
            $rate = new Rating();
    
            $rate->setUser($user);
            $rate->setRecette($recipe);
            $rate->setRate($rateInput);
            $this->ratingRepository->save($rate);
            return new JsonResponse($rate);
        }
        //return bad method request
        return new Response('Bad Request', 400);
    }
}
