<?php

namespace App\DataFixtures;

use App\Entity\Recipe;
use App\Entity\User;
use Cassandra\Time;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MarmitonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $types_recettes = ["ENTREE","PLAT","DESSERT"];

        /**** LECTURE DU JSON ****/
        $string = file_get_contents("marmiton_results.json");
        if ($string === false) {
            throw new \Error("Erreur lecture fichier");
        }

        $json_a = json_decode($string, true);
        if ($json_a === null) {
            throw new \Error("Conversion JSON impossible");
        }
        /**** FIN LECTURE DU JSON ****/

        //Je récupère chaque object json qui représente une recette | 0=Entrées, 1=Plats, 2=Desserts
        for ($i=0;$i<3;$i++){
            foreach ($json_a[$i] as $recipe_json) {
                $total_time = intdiv($recipe_json["totalTime"], 60).':'. ($recipe_json["totalTime"] % 60);
                $preparation_time = intdiv($recipe_json["prepTime"], 60).':'. ($recipe_json["prepTime"] % 60);
                $recipe = new Recipe();
                $recipe
                    ->setUserId($manager->getRepository(User::class)->findBy(['email'=>'admin@gmail.com'])[0])
                    ->setName($recipe_json["name"])
                    ->setUrl($recipe_json["url"])
                    ->setType($types_recettes[$i])
                    ->setNumberOfPersons($recipe_json["people"])
                    ->setDifficulty($recipe_json["difficulty"])
                    ->setPreparationTime(new \DateTime($preparation_time))
                    ->setTotalTime(new \DateTime($total_time))
                    ->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($recipe);
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}