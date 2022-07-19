<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeImages;
use App\Entity\RecipeIngredients;
use App\Entity\RecipeQuantities;
use App\Entity\RecipeSteps;
use App\Entity\RecipeTags;
use App\Entity\User;
use App\Services\MarmitonManager;
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
        $marmitonManager = new MarmitonManager($manager->getRepository(Ingredient::class));

        /**** LECTURE DU JSON ****/
        $t0 = microtime(true);
        $string = file_get_contents("marmiton_results.json");
        if ($string === false) {
            throw new \Error("Erreur lecture fichier");
        }
        $t1 = microtime(true);
        echo "\nLecture fichier en : " . ($t1 - $t0) ." sec";

        $t0 = microtime(true);
        $json_a = json_decode($string, true);
        if ($json_a === null) {
            throw new \Error("Conversion JSON impossible");
        }
        $t1 = microtime(true);
        echo "\nDecodage JSON en : " . ($t1 - $t0) ." sec";
        /**** FIN LECTURE DU JSON ****/

        //Je récupère chaque object json qui représente une recette | 0=Entrées, 1=Plats, 2=Desserts
        for ($i=0;$i<3;$i++){
            foreach ($json_a[$i] as $recipe_json) {
                /**** RECIPE ****/
                //$total_time = intdiv($recipe_json["totalTime"], 60).':'. ($recipe_json["totalTime"] % 60);
                //$preparation_time = intdiv($recipe_json["prepTime"], 60).':'. ($recipe_json["prepTime"] % 60);

                $recipe = new Recipe();
                $recipe
                    ->setUserId($user_entity)
                    ->setName($recipe_json["name"])
                    ->setUrl($recipe_json["url"])
                    ->setType($types_recettes[$i])
                    ->setNumberOfPersons($recipe_json["people"])
                    ->setDifficulty($recipe_json["difficulty"])
                    ->setPreparationTime($recipe_json["prepTime"])
                    ->setTotalTime($recipe_json["totalTime"])
                    ->setBudget($recipe_json["budget"])
                    ->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($recipe);
                /******************************************************/

                /********* QUANTITIES *********/
                $quantities = $recipe_json["ingredients"];
                foreach ($quantities as $quantity) {
                    $quantityObj = new RecipeQuantities();
                    $quantityObj->setContent($quantity);
                    $quantityObj->setRecipe($recipe);
                    $manager->persist($quantityObj);
                }

                /********* TAGS **********/
                $tags_json = $recipe_json["tags"];
                $tags = [];
                foreach ($tags_json as $tag_element){
                    $tag_element = utf8_encode(strtolower(trim($tag_element)));
                    if (!empty($tag_element)){
                        $tag = $manager->getRepository(RecipeTags::class)->findOneBy(['name'=>$tag_element]);
                        if ($tag == null && !isset($tags[$tag_element])) {
                            $tag = new RecipeTags();
                            $tag->setName($tag_element);
                            $tag->addRecipe($recipe);
                            $tags[$tag_element] = $tag;
                            $manager->persist($tag);
                        }else if ($tag != null && !isset($tags[$tag_element])){
                            $tag->addRecipe($recipe);
                        }else{
                            $tags[$tag_element]->addRecipe($recipe);
                        }
                    }
                }

                /********* IMAGES **********/
                $images_json = $recipe_json["images"];
                foreach ($images_json as $image_element){
                    if (!empty($image_element) && filter_var($image_element, FILTER_VALIDATE_URL)){
                        $image = new RecipeImages();
                        $image->setRecipe($recipe);
                        $image->setUrl($image_element);
                        $manager->persist($image);
                    }
                }

                /**** INGREDIENTS ****/
                $ingredient_liste = explode(",",$recipe_json["description"]);
                foreach ($ingredient_liste as $ingredient_name){
                    $ingredient_name = utf8_encode(strtolower(trim($ingredient_name)));
                    if(!isset($ingredient_name_list[$ingredient_name])){
                        $ingredient = new Ingredient();
                        $ingredient
                            ->setUserId($user_entity)
                            ->setName($ingredient_name)
                            ->setPrimaryType("marmiton")
                            ->setSecondaryType("marmiton")
                            ->setCreatedAt(new \DateTimeImmutable());
                        $manager->persist($ingredient);
                        $ingredient_name_list[$ingredient_name] = $ingredient;
                    }
                    //On créer le lien ingrédient → recette
                    $recipe_ingredient = new RecipeIngredients();
                    $recipe_ingredient->setRecipe($recipe);
                    $recipe_ingredient->setIngredient($ingredient_name_list[$ingredient_name]);
                    $manager->persist($recipe_ingredient);
                }
                /******************************************************/

                /**** RECIPE STEPS ****/
                $recipe_steps_array = $recipe_json["steps"];
                $step_number = 1;
                foreach ($recipe_steps_array as $recipe_step_string){
                    $recipe_step_string = trim($recipe_step_string);
                    $recipe_step = new RecipeSteps();
                    $recipe_step
                        ->setRecipe($recipe)
                        ->setOrdre($step_number)
                        ->setStep($recipe_step_string);
                    $manager->persist($recipe_step);
                    $step_number++;
                }
                /******************************************************/
                $manager->flush();
            }

            //$marmitonManager->updateIngredientsImage();
        }
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}