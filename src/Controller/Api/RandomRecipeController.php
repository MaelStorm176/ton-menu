<?php

namespace App\Controller\Api;

use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class RandomRecipeController extends \App\Controller\RandomRecipeController
{
    public function __invoke(string $type): array
    {
        return $this->randomRecipes(mb_strtoupper($type));
    }
}
