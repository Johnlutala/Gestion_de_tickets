#!/usr/bin/env php
<?php
require_once __DIR__.'/vendor/autoload.php';

$kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'dev', ($_SERVER['APP_DEBUG'] ?? false) === '1');
$kernel->boot();

$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.default_entity_manager');

$roleRepo = $em->getRepository('App\Entity\Role');
$privRepo = $em->getRepository('App\Entity\Privilege');

$roleAdmin = $roleRepo->findOneBy(['nom' => 'ROLE_ADMIN']);
$privCreateTicket = $privRepo->findOneBy(['name' => 'CREATE_TICKET']);

if ($roleAdmin && $privCreateTicket) {
    $roleAdmin->addPrivilege($privCreateTicket);
    $em->persist($roleAdmin);
    $em->flush();
    echo "✓ Privilege linked successfully\n";
} else {
    echo "✗ Role or Privilege not found\n";
}
