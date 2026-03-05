<?php

namespace App\Command;

use App\Entity\Privilege;
use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:link-privileges',
    description: 'Link privileges to roles in the database'
)]
class LinkPrivilegesCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $roleRepo = $this->em->getRepository(Role::class);
        $privRepo = $this->em->getRepository(Privilege::class);

        $roleAdmin = $roleRepo->findOneBy(['nom' => 'ROLE_ADMIN']);
        $privCreateTicket = $privRepo->findOneBy(['name' => 'CREATE_TICKET']);

        if (!$roleAdmin) {
            $output->writeln('<error>Role ROLE_ADMIN not found</error>');
            return Command::FAILURE;
        }

        if (!$privCreateTicket) {
            $output->writeln('<error>Privilege CREATE_TICKET not found</error>');
            return Command::FAILURE;
        }

        if ($roleAdmin->getPrivileges()->contains($privCreateTicket)) {
            $output->writeln('<info>Privilege already linked to role</info>');
            return Command::SUCCESS;
        }

        $roleAdmin->addPrivilege($privCreateTicket);
        $this->em->persist($roleAdmin);
        $this->em->flush();

        $output->writeln('<info>✓ Privilege CREATE_TICKET linked to ROLE_ADMIN successfully!</info>');
        return Command::SUCCESS;
    }
}
