<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture {
    private $encoder;
    private $adminPassword;
    private $adminEmail;
    private $faker;
    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->adminPassword = "admin@gmail.com";
        $this->adminEmail = "pass_1234";
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager)
    {
        $admin = $this->createAdminUser();
        $user = $this->createFakeUser();
        $chief = $this->createFakeChief();
        $manager->persist($admin);
        $manager->persist($user);
        $manager->persist($chief);

        //create 100 users
        for ($i = 0; $i < 100; $i++) {
            $user = $this->createUser();
            $manager->persist($user);
        }

        $manager->flush();
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail($this->faker->email);
        $user->setPassword($this->encoder->hashPassword($user, $this->faker->password));
        $user->setFirstName($this->faker->firstName);
        $user->setLastName($this->faker->lastName);
        $user->setIsVerify(true);
        $user->setProfilePicture("/blank.png");
        $user->setRoles(['ROLE_USER']);
        return $user;
    }

    private function createAdminUser(): User
    {
        $user = new User();
        $password = "pass_1234";
        $password_encoded = $this->encoder->hashPassword($user, $password);

        $user->setEmail("admin@gmail.com")
            ->setFirstname("Administrateur")
            ->setLastname(null)
            ->setPassword($password_encoded)
            ->setRoles(["ROLE_ADMIN"])
            ->setIsVerify(true)
            ->setProfilePicture("/blank.png");
        return $user;
    }


    private function createFakeUser(): User
    {
        $user = new User();
        $user->setEmail("fakeuser@gmail.com");
        $user->setPassword($this->encoder->hashPassword($user, "pass_1234"));
        $user->setFirstName($this->faker->firstName);
        $user->setLastName($this->faker->lastName);
        $user->setIsVerify(true);
        $user->setProfilePicture("/blank.png");
        $user->setRoles(['ROLE_USER']);
        return $user;
    }


    private function createFakeChief(): User
    {
        $user = new User();
        $user->setEmail("fakechief@gmail.com");
        $user->setPassword($this->encoder->hashPassword($user, "pass_1234"));
        $user->setFirstName($this->faker->firstName);
        $user->setLastName($this->faker->lastName);
        $user->setIsVerify(true);
        $user->setProfilePicture("/blank.png");
        $user->setRoles(['ROLE_CHIEF']);
        return $user;
    }
}
?>