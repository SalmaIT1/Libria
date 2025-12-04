<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications')]
    public function index(NotificationRepository $notificationRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $notifications = $notificationRepository->findBy(
            ['user' => $user],
            ['dateCreation' => 'DESC'],
            50
        );

        $nonLues = $notificationRepository->count([
            'user' => $user,
            'lu' => false
        ]);

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
            'nonLues' => $nonLues,
        ]);
    }

    #[Route('/notifications/marquer-lu/{id}', name: 'notification_mark_read', methods: ['POST'])]
    public function marquerLu(int $id, NotificationRepository $notificationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $notification = $notificationRepository->find($id);
        
        if (!$notification || $notification->getUser() !== $user) {
            return $this->json(['error' => 'Notification non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $notification->setLu(true);
        $entityManager->flush();

        return $this->json(['success' => true]);
    }

    #[Route('/notifications/marquer-toutes-lues', name: 'notification_mark_all_read', methods: ['POST'])]
    public function marquerToutesLues(NotificationRepository $notificationRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        $notifications = $notificationRepository->findBy([
            'user' => $user,
            'lu' => false
        ]);

        foreach ($notifications as $notification) {
            $notification->setLu(true);
        }

        $entityManager->flush();

        return $this->json(['success' => true, 'count' => count($notifications)]);
    }

    #[Route('/notifications/non-lues', name: 'notification_unread_count', methods: ['GET'])]
    public function countUnread(NotificationRepository $notificationRepository): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['count' => 0]);
        }

        $count = $notificationRepository->count([
            'user' => $user,
            'lu' => false
        ]);

        return $this->json(['count' => $count]);
    }

    #[Route('/notifications/recentes', name: 'notification_recent', methods: ['GET'])]
    public function recentes(NotificationRepository $notificationRepository): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['notifications' => []]);
        }

        $notifications = $notificationRepository->findBy(
            ['user' => $user],
            ['dateCreation' => 'DESC'],
            5 // 5 dernières notifications
        );

        $data = [];
        foreach ($notifications as $notification) {
            $data[] = [
                'id' => $notification->getId(),
                'titre' => $notification->getTitre(),
                'message' => $notification->getMessage(),
                'type' => $notification->getType(),
                'lu' => $notification->isLu(),
                'dateCreation' => $notification->getDateCreation()->format('Y-m-d H:i:s'),
                'dateCreationFormatted' => $notification->getDateCreation()->format('M d, Y H:i'),
                'lien' => $notification->getLien(),
            ];
        }

        $nonLues = $notificationRepository->count([
            'user' => $user,
            'lu' => false
        ]);

        return $this->json([
            'notifications' => $data,
            'nonLues' => $nonLues
        ]);
    }
}

