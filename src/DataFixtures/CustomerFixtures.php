<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $alamirault = new Customer("alamirault");
        $jdoe = new Customer("jdoe");
        $manager->persist($alamirault);
        $manager->persist($jdoe);

        $manager->flush();
    }
}
