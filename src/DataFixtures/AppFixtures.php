<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i < 11; $i++) { 
            for ($i=1; $i < 42; $i++) { 
                # code...
            }
        }

        $manager->flush();
    }
}
