<?php

namespace App\DataFixtures;

use App\Entity\Bay;
use App\Entity\Customer;
use App\Entity\InterventionType;
use App\Entity\Offer;
use App\Entity\Order;
use App\Entity\Setting;
use App\Entity\State;
use App\Entity\Unit;
use App\Entity\UnitType;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $states = [
            "OK" => "#02c41c", 
            "Arrêt" => "#36010d", 
            "Incident" => "#fcba03", 
            "Maintenance" => "#04d6d6"
        ];
        $unitTypes = [
            "Inutilisé" => "#403f3f",
            "Serveur Web" => "#47a7c9", 
            "Stockage" => "##6e6b6b", 
            "Base de donnée" => "#3fba60"
        ];
        $interventionType = [
            "Incident" => "#fcba03", 
            "Maintenance" => "#04d6d6"
        ];
        $customers = [
            ["Lucas","Alleaume","lucassis@breizh.br","lucas29"],
            ["Killian","Bonneau","kikibobo@cepapho.fr","password"],
            ["Rémi","GUERIN","remig@gmail.feur","security"],
            ["Nathan","Billaud","nathanbibi@tkt.com","azerty"],
        ];
        $users = [
            ["Gaël","BAHIER","bahiergael@feur.com","1234"],
            ["Tawfiq","CADI TAZI","tawfiq@tropfort.com","Not24Get"],
        ];
        $offers = [
            ["Black Friday",30,10],
            ["Pack Pro",20,25],
            ["Acheter une unité",0,1],
        ];
        $orderDates = [
            [new DateTimeImmutable("2024-01-01"), new DateTimeImmutable("2024-06-01")],
            [new DateTimeImmutable("2024-04-08"), new DateTimeImmutable("2024-10-08")],
            [new DateTimeImmutable("2023-04-18"), new DateTimeImmutable("2024-11-18")],
        ];

        $currentCustomers = [];
        $currentOffers = [];
        $currentStates = [];
        $currentTypes = [];

        $setting = new Setting();
        $setting->setSettingKey("currentUnitPrice");
        $setting->setValue("10");
        $manager->persist($setting);

        // ADDING DATA FOR STATE
        foreach ($states as $key => $value) { 
            $state = new State();
            $state->setName($key);
            $state->setColor($value);
            array_push($currentStates, $state);
            $manager->persist($state);
        }

        // ADDING DATA FOR UNITTYPE
        foreach ($unitTypes as $key => $value) { 
            $unitType = new UnitType();
            $unitType->setReference($key);
            $unitType->setColor($value);
            array_push($currentTypes, $unitType);
            $manager->persist($unitType);
        }

        // ADDING DATA FOR INTERVENTIONTYPE
        foreach ($interventionType as $key => $value) {
            $interventionType = new InterventionType();
            $interventionType->setReference($key);
            $interventionType->setColor($value);
            $manager->persist($interventionType);
        }

        // ADDING DATA FOR BAY AND UNIT
        for ($i=1; $i < 31; $i++) {
            $bay = new Bay();
            $bay->setReference(str_pad($i, 4, "B000", STR_PAD_LEFT));
            $manager->persist($bay);
            for ($j=1; $j < 43; $j++) { 
                $unit = new Unit();
                $unit->setReference($bay->getReference() . str_pad($j, 4, "-U00", STR_PAD_LEFT));
                $unit->setBay($bay);
                $unit->setState($currentStates[1]);
                $unit->setType($currentTypes[0]);
                $manager->persist($unit);
            }
        }

        // ADDING DATA FOR CUSTOMER
        foreach ($customers as $customer) {
            $newCustomer = new Customer();
            $newCustomer->setFirstname($customer[0]);
            $newCustomer->setLastname($customer[1]);
            $newCustomer->setMailAddress($customer[2]);
            $newCustomer->setPassword($customer[3]);
            $newCustomer->setRole("ROLE_CLIENT");
            array_push($currentCustomers,$newCustomer);
            $manager->persist($newCustomer);
        }

        // ADDING DATA FOR USER
        foreach ($users as $user) {
            $newUser = new User();
            $newUser->setFirstname($user[0]);
            $newUser->setLastname($user[1]);
            $newUser->setMailAddress($user[2]);
            $newUser->setPassword($user[3]);
            $newUser->setRole("ROLE_ADMIN");
            $manager->persist($newUser);
        }

        // ADDING DATA FOR OFFER
        foreach ($offers as $offer) {
            $newOffer = new Offer();
            $newOffer->setName($offer[0]);
            $newOffer->setPromotionPercentage($offer[1]);
            $newOffer->setUnitLimit($offer[2]);
            array_push($currentOffers,$newOffer);
            $manager->persist($newOffer);
        }

        // ADDING DATA FOR ORDER
        for ($i=0; $i < 3; $i++) {
            $newOrder = new Order();
            $newOrder->setStartDate($orderDates[$i][0]);
            $newOrder->setEndDate($orderDates[$i][1]);
            $newOrder->setCustomer($currentCustomers[$i]);
            $newOrder->setOffer($currentOffers[$i]);
            $newOrder->setUnitPrice($setting->getValue());
            $manager->persist($newOrder);
        }

        $manager->flush();
    }
}
