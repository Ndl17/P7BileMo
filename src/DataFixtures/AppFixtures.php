<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $listClients = [];

        for ($i = 0; $i < 10; $i++) {
            $client = new Client();
            $client->setEmail('user' . $i . '@user.user');
            $client->setRoles(['ROLE_USER']);
            $client->setPassword($this->userPasswordHasher->hashPassword($client, 'password'));
            $manager->persist($client);
            $listClients[] = $client;
        }

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setFirstName('Prénom ' . $i);
            $user->setLastName('Nom ' . $i);
            $user->setEmail('email' . $i . '@mail.com');
            $user->setClient($listClients[array_rand($listClients)]);
            $manager->persist($user);
        }

        for ($i = 0; $i < 20; $i++) {
            $phone = new Phone();
            $phone->setName('nom ' . $i);
            $phone->setBrand('Marque' . $i);
            $phone->setColor('Couleur' . $i);
            $phone->setPrice($i);
            $phone->setDescription('Description' . $i);
            $manager->persist($phone);
        }

        $manager->flush();
    }
}
