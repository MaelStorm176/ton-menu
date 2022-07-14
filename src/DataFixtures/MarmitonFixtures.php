<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use App\Entity\Recipe;
use App\Entity\RecipeImages;
use App\Entity\RecipeIngredients;
use App\Entity\RecipeSteps;
use App\Entity\RecipeTags;
use App\Entity\RecipeTagsLinks;
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
                            $tags[$tag_element] = $tag;
                            $manager->persist($tag);
                        }elseif (isset($tags[$tag_element])){
                            $tag = $tags[$tag_element];
                        }
                        //On créer le lien tag → recette
                        $recipeTagLink = new RecipeTagsLinks();
                        $recipeTagLink->setRecipe($recipe);
                        $recipeTagLink->setRecipeTag($tag);
                        $manager->persist($recipeTagLink);
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
                foreach ($recipe_steps_array as $recipe_step_string){
                    $recipe_step_string = trim($recipe_step_string);
                    $recipe_step = new RecipeSteps();
                    $recipe_step
                        ->setRecipe($recipe)
                        ->setStep($recipe_step_string);
                    $manager->persist($recipe_step);
                }
                /******************************************************/
                $manager->flush();
            }
        }
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}