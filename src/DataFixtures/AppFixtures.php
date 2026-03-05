<?php

namespace App\DataFixtures;

use App\Entity\Eleve;
use App\Entity\Privilege;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

     public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {

        // Créer un profil
        $profile = new Role();
        $profile->setNom('ROLE_ADMIN');
        $manager->persist($profile);

        // Créer un privilège
        $privilege = new Privilege();
        $privilege->setName('CREATE_TICKET');
        $privilege->setDescription('Permet de créer des tickets');
        $manager->persist($privilege);

        // Lier le privilège au rôle
        $profile->addPrivilege($privilege);

        // Créer l'admin
        $admin = new User();
        $admin->setUsername('johnkisina');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setProfile($profile); // IMPORTANT
        $manager->persist($admin);

        $manager->flush();


    }
}
