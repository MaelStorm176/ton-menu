<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeSteps;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MarmitonFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $user_entity = $manager->getRepository(User::class)->findOneBy(['email'=>'admin@gmail.com']);
        $types_recettes = ["ENTREE","PLAT","DESSERT"];
        $ingredient_name_list = [];

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
                /**** RECIPE ****/
                $total_time = intdiv($recipe_json["totalTime"], 60).':'. ($recipe_json["totalTime"] % 60);
                $preparation_time = intdiv($recipe_json["prepTime"], 60).':'. ($recipe_json["prepTime"] % 60);
                $recipe = new Recipe();
                $recipe
                    ->setUserId($user_entity)
                    ->setName($recipe_json["name"])
                    ->setUrl($recipe_json["url"])
                    ->setType($types_recettes[$i])
                    ->setNumberOfPersons($recipe_json["people"])
                    ->setDifficulty($recipe_json["difficulty"])
                    ->setPreparationTime(new \DateTime($preparation_time))
                    ->setTotalTime(new \DateTime($total_time))
                    ->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($recipe);
                /******************************************************/


                /**** INGREDIENTS ****/
                $ingredient_liste = explode(",",$recipe_json["description"]);
                foreach ($ingredient_liste as $ingredient_name){
                    $ingredient_name = trim($ingredient_name);
                    if(!isset($ingredient_name_list[$ingredient_name])){
                        $ingredient_name_list[$ingredient_name] = true;
                        $ingredient = new Ingredient();
                        $ingredient
                            ->setUserId($user_entity)
                            ->setName($ingredient_name)
                            ->setPrimaryType("marmiton")
                            ->setSecondaryType("marmiton")
                            ->setCreatedAt(new \DateTimeImmutable());
                        $manager->persist($ingredient);
                    }
                }
                /******************************************************/

                /**** RECIPE STEPS ****/
                $recipe_steps_array = $recipe_json["steps"];
                foreach ($recipe_steps_array as $recipe_step_string){
                    $recipe_step_string = trim($recipe_step_string);
                    $recipe_step = new RecipeSteps();
                    $recipe_step
                        ->setRecipe($recipe)
                        ->setStep($recipe_step_string);
                    $manager->persist($recipe_step);
                }
                /******************************************************/
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