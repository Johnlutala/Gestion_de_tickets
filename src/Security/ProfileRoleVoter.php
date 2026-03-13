<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter qui accorde les rôles basés sur le profil personnalisé (tb_role).
 *
 * Symfony appelle ce Voter à chaque is_granted() / #[IsGranted].
 * Il lit directement user->getProfile()->getNom() depuis la base de données,
 * contournant ainsi les problèmes de sérialisation de session.
 *
 * Usage dans Twig   : {% if is_granted('ROLE_ADMIN') %}
 * Usage contrôleur  : #[IsGranted('ROLE_ADMIN')]
 */
class ProfileRoleVoter extends Voter
{
    /**
     * Ce Voter s'active pour tout attribut qui commence par "ROLE_".
     * Il couvre donc ROLE_ADMIN, ROLE_MARCHAND, et n'importe quel rôle
     * que tu crées manuellement dans ta table tb_role.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return str_starts_with(strtoupper($attribute), 'ROLE_');
    }

    /**
     * Logique de décision : l'utilisateur a-t-il ce rôle ?
     * On calcule simplement si profile.nom == $attribute (après normalisation).
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // L'utilisateur doit être une instance de notre entité User
        if (!$user instanceof User) {
            return false;
        }

        // Récupère le nom du profil assigné à cet utilisateur (ex: "ROLE_ADMIN")
        $profileNom = $user->getProfile()?->getNom() ?? '';

        // Normalisation : "Admin", "ADMIN", "ROLE_ADMIN" → "ROLE_ADMIN"
        $profileNom = strtoupper(trim($profileNom));
        $profileNom = str_replace([' ', '-'], '_', $profileNom);
        if (!str_starts_with($profileNom, 'ROLE_')) {
            $profileNom = 'ROLE_' . $profileNom;
        }

        // Normalise aussi l'attribut demandé (juste au cas où)
        $attribute = strtoupper(trim($attribute));

        return $profileNom === $attribute;
    }
}
