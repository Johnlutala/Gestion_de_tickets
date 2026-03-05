<?php

namespace App\DataFixtures;

use App\Entity\Privilege;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LinkPrivilegesToRoles extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Fetch existing role and privilege
        $roleRepo = $manager->getRepository(Role::class);
        $privRepo = $manager->getRepository(Privilege::class);

        $roleAdmin = $roleRepo->findOneBy(['nom' => 'ROLE_ADMIN']);
        $privCreateTicket = $privRepo->findOneBy(['name' => 'CREATE_TICKET']);

        // If both exist, link them
        if ($roleAdmin && $privCreateTicket) {
            if (!$roleAdmin->getPrivileges()->contains($privCreateTicket)) {
                $roleAdmin->addPrivilege($privCreateTicket);
                $manager->persist($roleAdmin);
                $manager->flush();
            }
        }
    }
}
