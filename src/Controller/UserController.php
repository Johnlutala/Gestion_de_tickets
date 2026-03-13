<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user', name: 'app_user_')]
#[IsGranted('ROLE_ADMIN')]
final class UserController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(UserRepository $userRepository, Request $request, EntityManagerInterface $em): Response
    {
        $searchQuery = trim((string) $request->query->get('q', ''));
        $selectedApplication = trim((string) $request->query->get('application', ''));
        $selectedMarchand = trim((string) $request->query->get('marchand', ''));

        $qb = $userRepository->createQueryBuilder('u')
            ->leftJoin('u.application', 'a')
            ->leftJoin('u.profile', 'p')
            ->addSelect('a', 'p')
            ->orderBy('u.id', 'DESC');

        if ($searchQuery !== '') {
            $qb
                ->andWhere('u.username LIKE :userSearch OR u.nom LIKE :userSearch OR u.prenom LIKE :userSearch')
                ->setParameter('userSearch', '%' . $searchQuery . '%');
        }

        if ($selectedApplication !== '') {
            $qb
                ->andWhere('a.id = :applicationId')
                ->setParameter('applicationId', (int) $selectedApplication);
        }

        if ($selectedMarchand !== '') {
            $qb
                ->andWhere('u.id = :marchandId')
                ->setParameter('marchandId', (int) $selectedMarchand);
        }

        $users = $qb->getQuery()->getResult();

        $applications = $em->getRepository(\App\Entity\Application::class)->findBy([], ['name' => 'ASC']);
        $marchandRole = $em->getRepository(\App\Entity\Role::class)->findOneBy(['nom' => 'ROLE_MARCHAND']);
        $marchands = $marchandRole
            ? $userRepository->findBy(['profile' => $marchandRole], ['username' => 'ASC'])
            : [];

        $selectedApplicationLabel = null;
        foreach ($applications as $application) {
            if ((string) $application->getId() === $selectedApplication) {
                $selectedApplicationLabel = $application->getName();
                break;
            }
        }

        $selectedMarchandLabel = null;
        foreach ($marchands as $marchand) {
            if ((string) $marchand->getId() === $selectedMarchand) {
                $selectedMarchandLabel = $marchand->getUsername();
                break;
            }
        }

        return $this->render('user/index.html.twig', [
            'users' => $users,
            'applications' => $applications,
            'marchands' => $marchands,
            'searchQuery' => $searchQuery,
            'selectedApplication' => $selectedApplication,
            'selectedApplicationLabel' => $selectedApplicationLabel,
            'selectedMarchand' => $selectedMarchand,
            'selectedMarchandLabel' => $selectedMarchandLabel,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
