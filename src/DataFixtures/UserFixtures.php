<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture {
    private $encoder;
    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $user = new User();
        $password = "pass_1234";
        $password_encoded = $this->encoder->hashPassword($user,$password);

        $user->setEmail("admin@gmail.com")
            ->setFirstname("Administrateur")
            ->setLastname(null)
            ->setPassword($password_encoded)
            ->setRoles(["ROLE_ADMIN"])
            ->setIsVerify(true)
            ->setProfilePicture("/blank.png");
        $manager->persist($user);
        $manager->flush();
    }
}
?>