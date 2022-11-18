<?php

namespace App\Services;

use App\Repository\IngredientRepository;
use Goutte\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\Response\StreamWrapper;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

class MarmitonManager
{
    private Client $client;
    private IngredientRepository $ingredientRepository;
    private ParameterBagInterface $parameterBag;

    public function __construct(IngredientRepository $ingredientRepository, ParameterBagInterface $parameterBag)
    {
        $this->client = new Client();
        $this->ingredientRepository = $ingredientRepository;
        $this->parameterBag = $parameterBag;
    }

    public function updateIngredientsImage(): void
    {
        $this->deleteAllMarmitonImages();
        $ingredients = $this->ingredientRepository->findBy([
            'primary_type' => 'marmiton'
        ]);
        foreach ($ingredients as $ingredient) {
            $imageUrl = $this->getIngredientImageUrl($ingredient->getSluggedName());
            if ($imageUrl) {
                $fileName = $this->downloadImage($imageUrl);
                $ingredient->setImage($fileName);
            }else{
                $ingredient->setImage(null);
            }
            $this->ingredientRepository->save($ingredient);
        }
    }

    private function deleteAllMarmitonImages(): void
    {
        $images = glob($this->parameterBag->get('ingredient_image_directory') . '/*');
        foreach ($images as $image) {
            if (is_file($image) && str_starts_with($image, $this->parameterBag->get('ingredient_image_directory').'/marmiton_')) {
                @unlink($image);
            }
        }
    }

    private function deleteImage(string $imagePath): void
    {
        $imagePath = $this->parameterBag->get('ingredient_image_directory') . $imagePath;
        if (file_exists($imagePath)) {
            unlink($imagePath);
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

    private function downloadImage(string $imageUrl): string
    {
        $imageName = uniqid("marmiton_").'.'.pathinfo($imageUrl, PATHINFO_EXTENSION);
        $this->client->request('GET', $imageUrl);
        $imageContent = $this->client->getResponse()->getContent();
        file_put_contents($this->parameterBag->get('ingredient_image_directory'). '/' . $imageName, $imageContent);
        return $imageName;
    }

}