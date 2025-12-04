<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminNotificationService
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    /**
     * Notifier tous les admins d'un nouveau livre ajouté
     */
    public function notifyNewBook(string $bookTitle, string $bookLink = null): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        
        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setUser($admin);
            $notification->setTitre('Nouveau livre ajouté');
            $notification->setMessage('Un nouveau livre "' . $bookTitle . '" a été ajouté à la bibliothèque.');
            $notification->setType('info');
            $notification->setLien($bookLink ?: '/admin');
            
            $this->entityManager->persist($notification);
        }
        
        $this->entityManager->flush();
    }

    /**
     * Notifier tous les admins d'un stock faible
     */
    public function notifyLowStock(string $bookTitle, int $currentStock, string $bookLink = null): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        
        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setUser($admin);
            $notification->setTitre('Stock faible');
            $notification->setMessage('Attention : Le livre "' . $bookTitle . '" n\'a plus que ' . $currentStock . ' exemplaire(s) disponible(s).');
            $notification->setType('warning');
            $notification->setLien($bookLink ?: '/admin');
            
            $this->entityManager->persist($notification);
        }
        
        $this->entityManager->flush();
    }

    /**
     * Notifier tous les admins d'un nouvel utilisateur inscrit
     */
    public function notifyNewUser(string $userName, string $userEmail, string $userLink = null): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        
        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setUser($admin);
            $notification->setTitre('Nouvel utilisateur inscrit');
            $notification->setMessage('Un nouvel utilisateur "' . $userName . '" (' . $userEmail . ') s\'est inscrit sur la plateforme.');
            $notification->setType('success');
            $notification->setLien($userLink ?: '/admin');
            
            $this->entityManager->persist($notification);
        }
        
        $this->entityManager->flush();
    }

    /**
     * Notifier tous les admins d'un nouvel emprunt créé
     */
    public function notifyNewEmprunt(string $bookTitle, string $userName, string $empruntLink = null): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        
        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setUser($admin);
            $notification->setTitre('Nouvel emprunt créé');
            $notification->setMessage($userName . ' a emprunté le livre "' . $bookTitle . '".');
            $notification->setType('emprunt');
            $notification->setLien($empruntLink ?: '/admin');
            
            $this->entityManager->persist($notification);
        }
        
        $this->entityManager->flush();
    }
}
