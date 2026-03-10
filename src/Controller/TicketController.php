<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ticket', name: 'app_ticket_')]
final class TicketController extends AbstractController
{
    private const CHAT_UPLOAD_DIR = 'uploads/tickets';
    private const MAX_UPLOAD_SIZE = 10485760;
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'text/plain',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    // ──────────────────────────────────────────────
    //  LISTE CLASSIQUE
    // ──────────────────────────────────────────────
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TicketRepository $ticketRepository): Response
    {
        return $this->render('ticket/index.html.twig', [
            'tickets' => $ticketRepository->findAll(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  CHAT  —  liste des conversations (panel gauche)
    //           + conversation sélectionnée (panel droit)
    // ──────────────────────────────────────────────
    #[Route('/chat', name: 'chat', methods: ['GET', 'POST'])]
    #[Route('/chat/{id}', name: 'chat_show', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function chat(
        Request $request,
        TicketRepository $ticketRepository,
        EntityManagerInterface $em,
        ?Ticket $ticket = null,
    ): Response {
        // Toutes les conversations racines
        $conversations = $ticketRepository->findRootTickets();

        // Si aucun ticket sélectionné, prendre le premier disponible (GET uniquement)
        if ($request->isMethod('GET') && $ticket === null && count($conversations) > 0) {
            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $conversations[0]->getId(),
            ]);
        }

        // Formulaire pour créer une nouvelle conversation
        $newTicket = new Ticket();
        $newForm   = $this->createForm(TicketType::class, $newTicket, [
            'action' => $this->generateUrl('app_ticket_chat'),
        ]);
        $newForm->handleRequest($request);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $newAttachment = $request->files->get('new_ticket_attachment');
            if ($newAttachment instanceof UploadedFile) {
                $uploadError = $this->attachUploadedFile($newAttachment, $newTicket);
                if ($uploadError !== null) {
                    $this->addFlash('error', $uploadError);

                    return $this->redirectToRoute('app_ticket_chat');
                }
            }

            $newTicket->setEnabled(true);
            $newTicket->setDeleted(false);
            $newTicket->setNoted(false);
            $newTicket->setNote(0);
            $newTicket->setCreatedAt(new \DateTimeImmutable());
            if ($this->getUser()) {
                $newTicket->setCreatedby($this->getUser());
                $newTicket->setUser($this->getUser());
            }
            $em->persist($newTicket);
            $em->flush();

            $this->addFlash('success', 'Ticket créé avec succès !');

            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $newTicket->getId(),
            ]);
        }

        // Si un ticket est sélectionné, marquer le nombre de réponses vues pour cette session
        if ($ticket !== null) {
            $session = $request->getSession();
            $seen = $session->get('seen_replies', []);
            $seen[$ticket->getId()] = count($ticket->getReplies());
            $session->set('seen_replies', $seen);
        }

        // Réponses de la conversation sélectionnée
        $replies = $ticket ? $ticketRepository->findRepliesOf($ticket) : [];

        return $this->render('ticket/chat.html.twig', [
            'conversations' => $conversations,
            'selected'      => $ticket,
            'replies'       => $replies,
            'newForm'       => $newForm,
        ]);
    }

    /**
     * Retourne un fragment HTML pour le menu notifications (tickets récents).
     * Utilisé par le template via `render(controller(...))`.
     */
    public function menuNotifications(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findRootTickets();
        // limiter à 6 éléments
        $tickets = array_slice($tickets, 0, 6);

        $items = [];
        foreach ($tickets as $t) {
            $items[] = [
                'id' => $t->getId(),
                'title' => $t->getTitle(),
                'marchand' => $t->getMarchand(),
                'replies' => count($t->getReplies()),
                'timeAgo' => $this->formatRelativeTime($t->getCreatedAt()),
            ];
        }

        return $this->render('Partials/_menu_notifications.html.twig', [
            'items' => $items,
        ]);
    }

    public function menuNotificationsBadge(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findRootTickets();
        $session = $this->container->get('request_stack')->getSession();
        $seen = $session->get('seen_replies', []);

        $unseen = 0;
        foreach ($tickets as $t) {
            $id = $t->getId();
            $currentReplies = count($t->getReplies());
            if (!array_key_exists($id, $seen)) {
                // never seen → count as unseen
                $unseen++;
            } else {
                $seenCount = (int) $seen[$id];
                if ($currentReplies > $seenCount) {
                    $unseen++;
                }
            }
        }

        return $this->render('Partials/_menu_notifications_badge.html.twig', [
            'count' => $unseen,
        ]);
    }

    #[Route('/notifications/mark-seen', name: 'notifications_mark_seen', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function markNotificationsSeen(TicketRepository $ticketRepository): Response
    {
        $tickets = $ticketRepository->findRootTickets();
        $map = [];
        foreach ($tickets as $t) {
            $map[$t->getId()] = count($t->getReplies());
        }

        $session = $this->container->get('request_stack')->getSession();
        $seen = $session->get('seen_replies', []);
        $seen = array_merge($seen, $map);
        $session->set('seen_replies', $seen);

        return new Response('', Response::HTTP_NO_CONTENT);
    }

    private function formatRelativeTime(?\DateTimeInterface $dt): string
    {
        if (! $dt) {
            return '';
        }
        $now = new \DateTimeImmutable();
        $diff = $now->getTimestamp() - $dt->getTimestamp();

        if ($diff < 60) {
            return 'à l\'instant';
        }
        if ($diff < 3600) {
            $m = (int) floor($diff / 60);
            return $m . ' min';
        }
        if ($diff < 86400) {
            $h = (int) floor($diff / 3600);
            return $h . ' h';
        }
        $d = (int) floor($diff / 86400);
        return $d . ' j';
    }

    // ──────────────────────────────────────────────
    //  RÉPONDRE à un ticket
    // ──────────────────────────────────────────────
    #[Route('/chat/{id}/reply', name: 'chat_reply', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function reply(
        Request $request,
        Ticket $ticket,
        EntityManagerInterface $em,
    ): Response {
        if (!$this->isCsrfTokenValid('reply' . $ticket->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $text = trim((string) $request->request->get('reply_text', ''));
        $attachment = $request->files->get('reply_attachment');

        if ($text === '' && !($attachment instanceof UploadedFile)) {
            $this->addFlash('error', 'Ajoutez un message ou un fichier avant l\'envoi.');

            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $ticket->isRootTicket() ? $ticket->getId() : $ticket->getParent()->getId(),
            ]);
        }

        // Si ce ticket est lui-même une réponse, on remonte au parent racine
        $root = $ticket->isRootTicket() ? $ticket : $ticket->getParent();

        $reply = new Ticket();
        $reply->setTitle($root->getTitle());
        $reply->setDescription($text !== '' ? $text : 'Fichier joint');
        $reply->setMarchand($this->getUser()?->getUserIdentifier() ?? 'Admin');
        $reply->setParent($root);
        $reply->setEnabled(true);
        $reply->setDeleted(false);
        $reply->setNoted(false);
        $reply->setNote(0);
        $reply->setCreatedAt(new \DateTimeImmutable());
        if ($root->getApplication()) {
            $reply->setApplication($root->getApplication());
        }
        if ($root->getUser()) {
            $reply->setUser($this->getUser() ?? $root->getUser());
        }
        if ($this->getUser()) {
            $reply->setCreatedby($this->getUser());
        }
        if ($attachment instanceof UploadedFile) {
            $uploadError = $this->attachUploadedFile($attachment, $reply);
            if ($uploadError !== null) {
                $this->addFlash('error', $uploadError);

                return $this->redirectToRoute('app_ticket_chat_show', [
                    'id' => $root->getId(),
                ]);
            }
        }

        $em->persist($reply);
        $em->flush();

        return $this->redirectToRoute('app_ticket_chat_show', [
            'id' => $ticket->isRootTicket() ? $ticket->getId() : $ticket->getParent()->getId(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  CRUD existant 
    // ──────────────────────────────────────────────
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        TicketRepository $ticketRepository
    ): Response {
        $ticket = new Ticket();
        $form   = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        $tickets = $ticketRepository->findAll();

        return $this->render('ticket/new.html.twig', [
            'ticket'  => $ticket,
            'tickets' => $tickets,
            'form'    => $form,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Ticket $ticket): Response
    {
        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form'   => $form,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $ticket->getId(), $request->request->get('_token'))) {
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
    }
    // fonction pour l'importation de s fichiers
    private function attachUploadedFile(UploadedFile $file, Ticket $ticket): ?string
    {
        $fileSize = $file->getSize();
        if ($fileSize !== null && $fileSize > self::MAX_UPLOAD_SIZE) {
            return 'Le fichier dépasse la taille maximale autorisée de 10 Mo.';
        }

        $mimeType = $file->getMimeType() ?? 'application/octet-stream';
        $originalName = $file->getClientOriginalName();
        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            return 'Ce type de fichier n\'est pas autorisé.';
        }

        $targetDirectory = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . self::CHAT_UPLOAD_DIR;
        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
            return 'Impossible de préparer le dossier d\'upload.';
        }

        $safeBaseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeBaseName = preg_replace('/[^A-Za-z0-9_-]/', '-', $safeBaseName) ?: 'fichier';
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');
        $storedName = $safeBaseName . '-' . bin2hex(random_bytes(6)) . '.' . $extension;

        try {
            $file->move($targetDirectory, $storedName);
        } catch (\Throwable) {
            return 'Le fichier n\'a pas pu être envoyé.';
        }

        $ticket->setAttachmentPath(self::CHAT_UPLOAD_DIR . '/' . $storedName);
        $ticket->setAttachmentOriginalName($originalName);
        $ticket->setAttachmentMimeType($mimeType);
        $ticket->setAttachmentSize($fileSize);

        return null;
    }
}
