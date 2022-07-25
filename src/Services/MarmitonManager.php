<?php

namespace App\Services;

use App\Repository\IngredientRepository;
use Goutte\Client;

class MarmitonManager
{
    private Client $client;
    private IngredientRepository $ingredientRepository;

    public function __construct(IngredientRepository $ingredientRepository)
    {
        $this->client = new Client();
        $this->ingredientRepository = $ingredientRepository;
    }

    public function updateIngredientsImage(): void
    {
        $ingredients = $this->ingredientRepository->findBy([
            'image' => null,
            'primary_type' => 'marmiton'
        ]);
        foreach ($ingredients as $ingredient) {
            $imageUrl = $this->getIngredientImageUrl($ingredient->getSluggedName());
            if ($imageUrl) {
                $ingredient->setImage($imageUrl);
                $this->ingredientRepository->save($ingredient);
            }
        }
    }

    private function getIngredientImageUrl(string $ingredientName): string
    {
        $crawler = $this->client->request('GET', 'https://www.marmiton.org/recettes/index/ingredient/' . $ingredientName);
        if ($crawler && $crawler->filter('div.center-text > img')->count() > 0) {
            return $crawler->filter('div.center-text > img')->attr('src');
        }else{
            return '';
        }
    }

}