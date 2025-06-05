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
            $mois = array_unique([rand(1, 12), rand(1, 12)]);
            $conseil->setMois($mois);

            $manager->persist($conseil);
        }

        $manager->flush();
    }
}
