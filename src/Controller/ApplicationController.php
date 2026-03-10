<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\ApplicationType;
use App\Repository\ApplicationRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/application', name: 'app_application_')]
final class ApplicationController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(ApplicationRepository $applicationRepository): Response
    {
        return $this->render('application/index.html.twig', [
            'applications' => $applicationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $application = new Application();
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        // Si POST, récupérer les données postées pour diagnostic léger
        if ($request->isMethod('POST')) {
            $formName = $form->getName();
            $allPosted = $request->request->all();
            $postedData = is_array($allPosted) ? ($allPosted[$formName] ?? []) : [];
            $postedToken = is_array($postedData) ? ($postedData['_token'] ?? null) : null;
            $formToken = $form->has('_token') ? $form->get('_token')->getData() : null;

            // Diagnostic: token imbriqué sous le nom du formulaire (ex: application[_token])
            if (!$postedToken) {
                $this->addFlash('danger', sprintf('Jeton CSRF manquant dans la requête POST (attendu sous "%s[_token]").', $formName));
            } elseif ($formToken && $postedToken !== $formToken) {
                $this->addFlash('danger', 'Jeton CSRF invalide ou modifié.');
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($application);
            $entityManager->flush();

            $this->addFlash('success', 'Application créée avec succès !');

            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application/new.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Application $application): Response
    {
        return $this->render('application/show.html.twig', [
            'application' => $application,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Application $application, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ApplicationType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Application mise à jour avec succès !');

            return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('application/edit.html.twig', [
            'application' => $application,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Application $application,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        TicketRepository $ticketRepository
    ): Response {
        $token = $request->request->get('_token');

        if ($this->isCsrfTokenValid('delete' . $application->getId(), $token)) {
            foreach ($userRepository->findBy(['application' => $application]) as $user) {
                $user->setApplication(null);
            }

            foreach ($ticketRepository->findBy(['application' => $application]) as $ticket) {
                $ticket->setApplication(null);
            }

            $entityManager->remove($application);
            $entityManager->flush();

            $this->addFlash('success', 'Application supprimée avec succès !');
        } else {
            $this->addFlash('danger', 'Jeton CSRF invalide pour la suppression.');
        }

        return $this->redirectToRoute('app_application_index', [], Response::HTTP_SEE_OTHER);
    }
}
