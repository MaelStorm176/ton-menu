<?php
namespace App\DataFixtures;

use App\Entity\Rating;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class RatingFixtures extends Fixture implements DependentFixtureInterface
{
    private $faker;
    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }
    public function load(ObjectManager $manager)
    {
        $users = $manager->getRepository('App:User')->findAll();
        $recipes = $manager->getRepository('App:Recipe')->findAll();

        //create 1000 ratings
        for ($i = 0; $i < 1000; $i++) {
            $rating = new Rating();
            $rating
                ->setRate($this->faker->numberBetween(1, 5))
                ->setUser($users[array_rand($users)])
                ->setRecette($recipes[array_rand($recipes)]);
            $manager->persist($rating);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            MarmitonFixtures::class,
        ];
    }
}
