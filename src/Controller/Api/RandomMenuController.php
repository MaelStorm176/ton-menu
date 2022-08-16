<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
class RandomMenuController extends \App\Controller\RandomRecipeController
{
    public function __invoke(int $number): array
    {
        if ($number < 1) {
            throw new \InvalidArgumentException('The number must be greater than 0', Response::HTTP_BAD_REQUEST);
        }
        if ($number > 7) {
            throw new \InvalidArgumentException('The number must be less than 7', Response::HTTP_BAD_REQUEST);
        }
        $entrees = $this->randomRecipes('ENTREE', $number);
        $plats = $this->randomRecipes('PLAT', $number);
        $desserts = $this->randomRecipes('DESSERT', $number);
        $final = [];
        if (count($entrees) == count($plats) && count($plats) == count($desserts)) {
            for ($i = 0; $i < count($entrees); $i++) {
                $final[] = [
                    'entree' => $entrees[$i],
                    'plat' => $plats[$i],
                    'dessert' => $desserts[$i],
                ];
            }
        }
        return $final;
    }
}
