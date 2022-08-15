<?php
namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CommentFixtures extends Fixture implements DependentFixtureInterface
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

        // create 100 comments
        for ($i = 0; $i < 500; $i++) {
            $createdAt = $this->faker->dateTimeBetween('-1 years', 'now');

            $comment = new Comment();
            $comment
                ->setContent($this->faker->text)
                ->setUser($users[array_rand($users)])
                ->setCreatedAt(\DateTimeImmutable::createFromMutable($createdAt))
                ->setRecette($recipes[array_rand($recipes)]);
            $manager->persist($comment);
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
