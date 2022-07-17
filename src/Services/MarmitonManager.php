<?php

namespace App\Services;

use App\Repository\IngredientRepository;
use Goutte\Client;

class MarmitonManager
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function updateIngredientsImage(IngredientRepository $repository): void
    {
        $ingredients = $repository->findBy([
            'image' => null,
            'primary_type' => 'marmiton'
        ]);
        foreach ($ingredients as $ingredient) {
            $imageUrl = $this->getIngredientImageUrl($ingredient->getName());
            if ($imageUrl) {
                $ingredient->setImage($imageUrl);
            }
        }
    }

    private function getIngredientImageUrl(string $ingredientName): string
    {
        $crawler = $this->client->request('GET', 'https://www.marmiton.org/index/ingredient/' . $ingredientName);
        return $crawler->filter('div.center-text')->filter('img')->attr('src');
    }

}