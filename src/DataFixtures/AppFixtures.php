<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create(); // Create a Faker instance
        
        $listClients = [];
        for ($i = 0; $i < 10; $i++) {
            $client = new Client();
            $client->setEmail($faker->email);
            $client->setRoles(['ROLE_USER']);
            $client->setPassword($this->userPasswordHasher->hashPassword($client, 'password'));
            $manager->persist($client);
            $listClients[] = $client;
        }

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setFirstName($faker->firstName);
            $user->setLastName($faker->lastName);
            $user->setEmail($faker->email);
            $user->setClient($listClients[array_rand($listClients)]);
            $manager->persist($user);
        }

        for ($i = 0; $i < 20; $i++) {
            $phone = new Phone();
            $phone->setName($faker->word);
            $phone->setBrand($faker->company);
            $phone->setColor($faker->colorName);
            $phone->setPrice($faker->numberBetween(100, 1000));
            $phone->setDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit.");
            $manager->persist($phone);
        }

        $manager->flush();
    }
}