<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('', name: 'app_home_')]
final class HomeController extends AbstractController
{
    #[Route('', name: 'home')]
    public function base(AuthenticationUtils $authenticationUtils)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home_index'); // redirige vers le menu
        }
        // Récupère l'erreur de connexion et le dernier nom d'utilisateur
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
        ]);
    }

    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
