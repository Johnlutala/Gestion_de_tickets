<?php

namespace App\Controller;

use App\Entity\Privilege;
use App\Form\PrivilegeType;
use App\Repository\PrivilegeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/privilege', name: 'app_privilege_')]
final class PrivilegeController extends AbstractController
{
    #[Route(name: 'index', methods: ['GET'])]
    public function index(PrivilegeRepository $privilegeRepository): Response
    {
        return $this->render('privilege/index.html.twig', [
            'privileges' => $privilegeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $privilege = new Privilege();
        $form = $this->createForm(PrivilegeType::class, $privilege);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($privilege);
            $entityManager->flush();

            return $this->redirectToRoute('app_privilege_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('privilege/new.html.twig', [
            'privilege' => $privilege,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Privilege $privilege): Response
    {
        return $this->render('privilege/show.html.twig', [
            'privilege' => $privilege,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Privilege $privilege, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PrivilegeType::class, $privilege);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_privilege_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('privilege/edit.html.twig', [
            'privilege' => $privilege,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Privilege $privilege, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $privilege->getId(), $request->request->get('_token'))) {
            $entityManager->remove($privilege);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_privilege_index', [], Response::HTTP_SEE_OTHER);
    }
}
