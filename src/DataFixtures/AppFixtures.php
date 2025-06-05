<?php

namespace App\DataFixtures;

use App\Entity\Conseil;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
        
    }


    public function load(ObjectManager $manager): void
    {
        // Création des users
        $user = new User();
        $user->setEmail("user@test.com");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "123"));
        $user->setVille("paris");
        $manager->persist($user);

        $admin = new User();
        $admin->setEmail("admin@test.com");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, "123"));
        $admin->setVille("marseille");
        $manager->persist($admin);

        // Création des conseils
        for ($i = 0; $i < 20; $i++) {
            $conseil = new Conseil();
            $conseil->setContent('Conseil numéro ' . $i . ' : Pensez à arroser vos plantes régulièrement.');
            $mois = array_unique([rand(1, 12), rand(1, 12)]);
            $conseil->setMois($mois);

            $manager->persist($conseil);
        }

        $manager->flush();
    }
}
