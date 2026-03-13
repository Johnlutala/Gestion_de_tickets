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
        'image/jpeg', 'image/png','image/gif','image/webp','application/pdf',
        'text/plain','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    // ──────────────────────────────────────────────
    //  LISTE CLASSIQUE
    // ──────────────────────────────────────────────
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(TicketRepository $ticketRepository, EntityManagerInterface $em, Request $request): Response
    {
        /** @var \App\Entity\User|null $currentUser */
        $currentUser = $this->getUser();
        $searchQuery = trim((string) $request->query->get('q', ''));
        $selectedMarchand = trim((string) $request->query->get('marchand', ''));
        $selectedUser = trim((string) $request->query->get('user', ''));
        $selectedDate = trim((string) $request->query->get('date', ''));
        $selectedYear = trim((string) $request->query->get('year', ''));
        $selectedMonth = trim((string) $request->query->get('month', ''));
        $selectedQuarter = trim((string) $request->query->get('quarter', ''));

        $qb = $ticketRepository->createQueryBuilder('t')
            ->leftJoin('t.createdby', 'creator')
            ->leftJoin('t.user', 'ticketUser')
            ->addSelect('creator', 'ticketUser')
            ->andWhere('t.deleted = false')
            ->orderBy('t.createdAt', 'DESC')
            ->addOrderBy('t.id', 'DESC');

        if ($searchQuery !== '') {
            $qb
                ->andWhere('t.title LIKE :search OR t.description LIKE :search OR t.marchand LIKE :search OR creator.username LIKE :search OR ticketUser.username LIKE :search')
                ->setParameter('search', '%' . $searchQuery . '%');
        }

        if ($selectedMarchand !== '') {
            $qb
                ->andWhere('creator.id = :marchandId')
                ->setParameter('marchandId', (int) $selectedMarchand);
        }

        if ($selectedUser !== '') {
            $qb
                ->andWhere('creator.id = :selectedUserId OR ticketUser.id = :selectedUserId')
                ->setParameter('selectedUserId', (int) $selectedUser);
        }

        if ($selectedDate !== '') {
            try {
                $startOfDay = new \DateTimeImmutable($selectedDate . ' 00:00:00');
                $endOfDay = $startOfDay->modify('+1 day');

                $qb
                    ->andWhere('t.createdAt >= :startOfDay')
                    ->andWhere('t.createdAt < :endOfDay')
                    ->setParameter('startOfDay', $startOfDay)
                    ->setParameter('endOfDay', $endOfDay);
            } catch (\Exception) {
                $selectedDate = '';
            }
        }

        if ($selectedYear !== '') {
            $qb
                ->andWhere('t.year = :selectedYear')
                ->setParameter('selectedYear', (int) $selectedYear);
        }

        if ($selectedMonth !== '') {
            $qb
                ->andWhere('t.month = :selectedMonth')
                ->setParameter('selectedMonth', (int) $selectedMonth);
        }

        if ($selectedQuarter !== '') {
            $qb
                ->andWhere('t.quarter = :selectedQuarter')
                ->setParameter('selectedQuarter', (int) $selectedQuarter);
        }

        if ($currentUser && !$this->isGranted('ROLE_ADMIN') && $this->isGranted('ROLE_MARCHAND')) {
            $qb
                ->andWhere('t.createdby = :creator')
                ->setParameter('creator', $currentUser);
        }

        $perPage = 10;
        $currentPage = max(1, $request->query->getInt('page', 1));

        $countQb = clone $qb;
        $totalItems = (int) $countQb
            ->select('COUNT(DISTINCT t.id)')
            ->resetDQLPart('orderBy')
            ->getQuery()
            ->getSingleScalarResult();

        $totalPages = max(1, (int) ceil($totalItems / $perPage));
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        $qb
            ->setFirstResult(($currentPage - 1) * $perPage)
            ->setMaxResults($perPage);

        $tickets = $qb->getQuery()->getResult();

        $marchandRole = $em->getRepository(\App\Entity\Role::class)->findOneBy(['nom' => 'ROLE_MARCHAND']);
        $adminRole = $em->getRepository(\App\Entity\Role::class)->findOneBy(['nom' => 'ROLE_ADMIN']);
        $marchands = $marchandRole
            ? $em->getRepository(\App\Entity\User::class)->findBy(['profile' => $marchandRole], ['username' => 'ASC'])
            : [];
        $admins = $adminRole
            ? $em->getRepository(\App\Entity\User::class)->findBy(['profile' => $adminRole], ['username' => 'ASC'])
            : [];

        $availableYears = $ticketRepository->createQueryBuilder('t')
            ->select('DISTINCT t.year')
            ->andWhere('t.deleted = false')
            ->andWhere('t.year IS NOT NULL')
            ->orderBy('t.year', 'DESC')
            ->getQuery()
            ->getSingleColumnResult();

        $monthChoices = [
            '1' => 'Janvier',
            '2' => 'Fevrier',
            '3' => 'Mars',
            '4' => 'Avril',
            '5' => 'Mai',
            '6' => 'Juin',
            '7' => 'Juillet',
            '8' => 'Aout',
            '9' => 'Septembre',
            '10' => 'Octobre',
            '11' => 'Novembre',
            '12' => 'Decembre',
        ];

        $quarterChoices = [
            '1' => 'T1',
            '2' => 'T2',
            '3' => 'T3',
            '4' => 'T4',
        ];

        $selectedMarchandLabel = null;
        foreach ($marchands as $marchand) {
            if ((string) $marchand->getId() === $selectedMarchand) {
                $selectedMarchandLabel = $marchand->getUsername();
                break;
            }
        }

        $selectedUserLabel = null;
        foreach ($admins as $admin) {
            if ((string) $admin->getId() === $selectedUser) {
                $selectedUserLabel = $admin->getUsername();
                break;
            }
        }

        $selectedMonthLabel = $monthChoices[$selectedMonth] ?? null;
        $selectedQuarterLabel = $quarterChoices[$selectedQuarter] ?? null;

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
            'isTrashView' => false,
            'marchands' => $marchands,
            'admins' => $admins,
            'availableYears' => $availableYears,
            'monthChoices' => $monthChoices,
            'quarterChoices' => $quarterChoices,
            'searchQuery' => $searchQuery,
            'selectedMarchand' => $selectedMarchand,
            'selectedMarchandLabel' => $selectedMarchandLabel,
            'selectedUser' => $selectedUser,
            'selectedUserLabel' => $selectedUserLabel,
            'selectedDate' => $selectedDate,
            'selectedYear' => $selectedYear,
            'selectedMonth' => $selectedMonth,
            'selectedMonthLabel' => $selectedMonthLabel,
            'selectedQuarter' => $selectedQuarter,
            'selectedQuarterLabel' => $selectedQuarterLabel,
            'totalItems' => $totalItems,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
        ]);
    }

    #[Route('/deleted', name: 'deleted', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleted(TicketRepository $ticketRepository): Response
    {
        $deletedTickets = $ticketRepository->findDeletedTickets();

        return $this->render('ticket/index.html.twig', [
            'tickets' => $deletedTickets,
            'isTrashView' => true,
            'marchands' => [],
            'admins' => [],
            'availableYears' => [],
            'monthChoices' => [],
            'quarterChoices' => [],
            'searchQuery' => '',
            'selectedMarchand' => '',
            'selectedMarchandLabel' => null,
            'selectedUser' => '',
            'selectedUserLabel' => null,
            'selectedDate' => '',
            'selectedYear' => '',
            'selectedMonth' => '',
            'selectedMonthLabel' => null,
            'selectedQuarter' => '',
            'selectedQuarterLabel' => null,
            'totalItems' => count($deletedTickets),
            'currentPage' => 1,
            'totalPages' => 1,
            'perPage' => count($deletedTickets) ?: 10,
        ]);
    }

    // ──────────────────────────────────────────────
    //  CHAT  —  liste des conversations (panel gauche)
    //           + conversation sélectionnée (panel droit)
    // ──────────────────────────────────────────────
    #[Route('/chat', name: 'chat', methods: ['GET', 'POST'])]
    #[Route('/chat/{id}', name: 'chat_show', methods: ['GET', 'POST'])]
    public function chat(
        Request $request,
        TicketRepository $ticketRepository,
        EntityManagerInterface $em,
        ?Ticket $ticket = null,
    ): Response {

        //Pour vérifier que l'utilisateur a le droit d'accéder à cette page (admin ou marchand)
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MARCHAND')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        /** @var \App\Entity\User $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        if ($ticket !== null && $ticket->isDeleted()) {
            return $this->redirectToRoute('app_ticket_chat');
        }

        // Un marchand ne voit que ses propres conversations
        $conversations = $isAdmin
            ? $ticketRepository->findRootTickets()
            : $ticketRepository->findRootTicketsByCreator($currentUser);

        // Sécurité : un marchand ne peut pas ouvrir le ticket d'un autre utilisateur
        // On redirige vers le chat (liste vide ou ses tickets) sans message d'erreur brutal
        if ($ticket !== null && !$isAdmin) {
            $creator = $ticket->getCreatedby();
            if (!$creator instanceof \App\Entity\User || !$currentUser instanceof \App\Entity\User || $creator->getId() !== $currentUser->getId()) {
                return $this->redirectToRoute('app_ticket_chat');
            }
        }

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

        // Prépare les infos pour affichage : nom de l'utilisateur et commentaire
        $replyInfos = [];
        foreach ($replies as $r) {
            $replyInfos[] = [
                'user' => $r->getUser() ? ($r->getUser()->getUsername() ?? $r->getUser()->getUserIdentifier()) : ($r->getMarchand() ?? 'Admin'),
                'comment' => $r->getDescription(),
                'createdAt' => $r->getCreatedAt(),
            ];
        }

        return $this->render('ticket/chat.html.twig', [
            'conversations' => $conversations,
            'selected'      => $ticket,
            'replies'       => $replies,
            'replyInfos'    => $replyInfos,
            'newForm'       => $newForm,
        ]);
    }

    /**
     * Retourne un fragment HTML pour le menu notifications (tickets récents).
     * Utilisé par le template via `render(controller(...))`.
     */
    public function menuNotifications(TicketRepository $ticketRepository): Response
    {
        /** @var \App\Entity\User|null $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $currentUser && $this->isGranted('ROLE_ADMIN');

        $tickets = $isAdmin || !$currentUser
            ? $ticketRepository->findRootTickets()
            : $ticketRepository->findRootTicketsByCreator($currentUser);

        // limiter à 6 éléments
        $tickets = array_slice($tickets, 0, 6);

        $items = [];
        foreach ($tickets as $t) {
            $creator = $t->getCreatedby();
            $role = $creator && $creator->getProfile() ? $creator->getProfile()->getNom() : null;
            $application = $t->getApplication() ? $t->getApplication()->getName() : ($creator && $creator->getApplication() ? $creator->getApplication()->getName() : null);

            $items[] = [
                'id' => $t->getId(),
                'title' => $t->getTitle(),
                'description' => $t->getDescription(),
                'marchand' => $t->getMarchand(),
                'role' => $role,
                'application' => $application,
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
        /** @var \App\Entity\User|null $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $currentUser && $this->isGranted('ROLE_ADMIN');

        $tickets = $isAdmin || !$currentUser
            ? $ticketRepository->findRootTickets()
            : $ticketRepository->findRootTicketsByCreator($currentUser);

        $session = $this->container->get('request_stack')->getSession();
        $seen = $session->get('seen_replies', []);

        $unseen = 0;
        foreach ($tickets as $t) {
            $id = $t->getId();
            $currentReplies = count($t->getReplies());
            if (!array_key_exists($id, $seen)) {
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
    public function markNotificationsSeen(TicketRepository $ticketRepository): Response
    {

        //Pour vérifier que l'utilisateur a le droit d'accéder à cette page (admin ou marchand)
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MARCHAND')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        /** @var \App\Entity\User|null $currentUser */
        $currentUser = $this->getUser();
        $isAdmin = $currentUser && $this->isGranted('ROLE_ADMIN');

        $tickets = $isAdmin || !$currentUser
            ? $ticketRepository->findRootTickets()
            : $ticketRepository->findRootTicketsByCreator($currentUser);

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
    public function reply(
        Request $request,
        Ticket $ticket,
        EntityManagerInterface $em,
    ): Response {

        //Pour vérifier que l'utilisateur a le droit d'accéder à cette page (admin ou marchand)
        if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_MARCHAND')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        if (!$this->isCsrfTokenValid('reply' . $ticket->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if ($ticket->isDeleted()) {
            return $this->redirectToRoute('app_ticket_chat');
        }

        $root = $this->resolveRootTicket($ticket);

        if (!$this->canAccessTicket($root)) {
            throw $this->createAccessDeniedException('Accès refusé à ce ticket.');
        }

        if (!$root->isEnabled()) {
            $this->addFlash('error', 'Ce ticket est deja cloturé.');

            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $root->getId(),
            ]);
        }

        $text = trim((string) $request->request->get('reply_text', ''));
        $attachment = $request->files->get('reply_attachment');

        if ($text === '' && !($attachment instanceof UploadedFile)) {
            $this->addFlash('error', 'Ajoutez un message ou un fichier avant l\'envoi.');

            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $root->getId(),
            ]);
        }

        $reply = new Ticket();
        $reply->setTitle($root->getTitle());
        $reply->setDescription($text !== '' ? $text : 'Fichier joint');
        $reply->setMarchand($this->getUser()?->getUserIdentifier() ?? 'Admin');
        $reply->setComment($text !== '' ? $text : 'Fichier joint');
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

        // Historise les interventions admin/user dans le champ comment du ticket racine.
        $author = $this->getUser()?->getUserIdentifier() ?? 'Admin';
        $commentLine = sprintf(
            '[%s] %s: %s',
            (new \DateTimeImmutable())->format('d/m/Y H:i'),
            $author,
            $text !== '' ? $text : 'Fichier joint'
        );
        $existingComment = trim((string) ($root->getComment() ?? ''));
        $root->setComment($existingComment !== '' ? $existingComment . PHP_EOL . $commentLine : $commentLine);

        $em->persist($reply);
        $em->flush();

        return $this->redirectToRoute('app_ticket_chat_show', [
            'id' => $root->getId(),
        ]);
    }

    #[Route('/{id}/evaluate', name: 'evaluate', methods: ['POST'])]
    public function evaluate(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $root = $this->resolveRootTicket($ticket);

        if (!$this->isCsrfTokenValid('evaluate' . $root->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if (!$this->isMerchantOwner($root)) {
            throw $this->createAccessDeniedException('Seul le marchand propriétaire peut évaluer ce ticket.');
        }

        if ($root->isDeleted()) {
            return $this->redirectToRoute('app_ticket_chat');
        }

        if ($root->getNote() !== null && $root->getNote() > 0) {
            $this->addFlash('info', 'Ce ticket a déjà été évalué.');

            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $root->getId(),
            ]);
        }

        $note = $request->request->getInt('note', -1);
        if ($note < 1 || $note > 20) {
            $this->addFlash('error', 'La note doit être comprise entre 1 et 20.');

            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $root->getId(),
            ]);
        }

        $root->setNote($note);
        $entityManager->flush();

        $this->addFlash('success', 'Le ticket a été évalué avec succès.');

        return $this->redirectToRoute('app_ticket_chat_show', [
            'id' => $root->getId(),
        ]);
    }

    #[Route('/{id}/close', name: 'close', methods: ['POST'])]
    public function close(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        $root = $this->resolveRootTicket($ticket);

        if (!$this->isCsrfTokenValid('close' . $root->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if (!$this->isMerchantOwner($root)) {
            throw $this->createAccessDeniedException('Seul le marchand propriétaire peut clôturer ce ticket.');
        }

        if ($root->isDeleted()) {
            return $this->redirectToRoute('app_ticket_chat');
        }

        if (($root->getNote() ?? 0) <= 0) {
            $this->addFlash('error', 'Le ticket doit être évalué avant d\'être clôturé.');

            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $root->getId(),
            ]);
        }

        if (!$root->isEnabled()) {
            $this->addFlash('info', 'Ce ticket est déjà clôturé.');

            return $this->redirectToRoute('app_ticket_chat_show', [
                'id' => $root->getId(),
            ]);
        }

        $root->setNoted(true);
        $root->setEnabled(false);
        $entityManager->flush();

        $this->addFlash('success', 'Le ticket a été clôturé.');

        return $this->redirectToRoute('app_ticket_chat_show', [
            'id' => $root->getId(),
        ]);
    }

    // ──────────────────────────────────────────────
    //  CRUD existant
    // ──────────────────────────────────────────────
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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
        if ($ticket->isDeleted()) {
            return $this->redirectToRoute('app_ticket_index');
        }

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_MARCHAND')]
    public function edit(
        Request $request,
        Ticket $ticket,
        EntityManagerInterface $entityManager
    ): Response {
        if ($ticket->isDeleted()) {
            return $this->redirectToRoute('app_ticket_index');
        }

        $currentUser = $this->getUser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        // Si marchand, il ne peut éditer que ses propres tickets
        $creator = $ticket->getCreatedby();
        if (
            !$isAdmin &&
            (
                !$creator instanceof \App\Entity\User ||
                !$currentUser instanceof \App\Entity\User ||
                $creator->getId() !== $currentUser->getId()
            )
        ) {
            return $this->redirectToRoute('app_ticket_index');
        }

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
        $root = $this->resolveRootTicket($ticket);

        if (!$this->canManageTicket($root)) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas supprimer ce ticket.');
        }

        if (!$root->isDeleted() && $this->isCsrfTokenValid('delete' . $root->getId(), (string) $request->request->get('_token'))) {
            $root->setDeleted(true);
            foreach ($root->getReplies() as $reply) {
                $reply->setDeleted(true);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Le ticket a été déplacé dans la corbeille.');
        }

        $redirectTo = (string) $request->request->get('redirect_to', 'index');
        if ($redirectTo === 'chat') {
            return $this->redirectToRoute('app_ticket_chat', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_ticket_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/restore', name: 'restore', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function restore(Request $request, Ticket $ticket, EntityManagerInterface $entityManager): Response
    {
        if ($ticket->isDeleted() && $this->isCsrfTokenValid('restore' . $ticket->getId(), (string) $request->request->get('_token'))) {
            $ticket->setDeleted(false);
            foreach ($ticket->getReplies() as $reply) {
                $reply->setDeleted(false);
            }
            $entityManager->flush();
            $this->addFlash('success', 'Le ticket a été restauré.');
        }

        return $this->redirectToRoute('app_ticket_deleted', [], Response::HTTP_SEE_OTHER);
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

    private function resolveRootTicket(Ticket $ticket): Ticket
    {
        return $ticket->isRootTicket() ? $ticket : ($ticket->getParent() ?? $ticket);
    }

    private function canAccessTicket(Ticket $ticket): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $this->isMerchantOwner($ticket);
    }

    private function canManageTicket(Ticket $ticket): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $this->isMerchantOwner($ticket);
    }

    private function isMerchantOwner(Ticket $ticket): bool
    {
        if (!$this->isGranted('ROLE_MARCHAND')) {
            return false;
        }

        $creator = $ticket->getCreatedby();
        $currentUser = $this->getUser();

        return $creator instanceof \App\Entity\User
            && $currentUser instanceof \App\Entity\User
            && $creator->getId() === $currentUser->getId();
    }
}
