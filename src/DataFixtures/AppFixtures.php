<?php

namespace App\DataFixtures;

use App\Entity\Conseil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $conseil = new Conseil();
            $conseil->setContent('Conseil numéro ' . $i . ' : Pensez à arroser vos plantes régulièrement.');
            $conseil->setMois(rand(1, 12));

            $manager->persist($conseil);
        }

        $manager->flush();
    }
}
