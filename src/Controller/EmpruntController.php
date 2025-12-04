<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Entity\Livre;
use App\Entity\Notification;
use App\Repository\EmpruntRepository;
use App\Service\AdminNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/emprunts')]
class EmpruntController extends AbstractController
{
    private AdminNotificationService $notificationService;

    public function __construct(AdminNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    #[Route('/', name: 'emprunts')]
    public function index(EmpruntRepository $empruntRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $empruntsEnCours = $empruntRepository->findBy(
            ['user' => $user, 'statut' => 'en_cours'],
            ['dateEmprunt' => 'DESC']
        );

        $empruntsRetournes = $empruntRepository->findBy(
            ['user' => $user, 'statut' => 'retourne'],
            ['dateRetourEffectif' => 'DESC'],
            10 // Limiter à 10 derniers
        );

        return $this->render('emprunts/index.html.twig', [
            'empruntsEnCours' => $empruntsEnCours,
            'empruntsRetournes' => $empruntsRetournes,
        ]);
    }

    #[Route('/emprunter/{id}', name: 'emprunt_create', methods: ['POST'])]
    public function emprunter(Livre $livre, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour emprunter un livre.');
            return $this->redirectToRoute('login');
        }

        // Vérifier la disponibilité pour emprunt
        if (!$livre->isAvailableForBorrowing()) {
            if ($livre->getNbExemplaires() < 3) {
                $this->addFlash('error', 'Ce livre n\'a pas assez d\'exemplaires pour être emprunté (minimum 3 requis).');
            } else {
                $this->addFlash('error', 'Tous les exemplaires disponibles sont réservés pour la vente.');
            }
            return $this->redirectToRoute('book_show', ['id' => $livre->getId()]);
        }

        // Vérifier si l'utilisateur a déjà emprunté ce livre et ne l'a pas encore retourné
        $empruntRepository = $entityManager->getRepository(Emprunt::class);
        $empruntExistant = $empruntRepository->findOneBy([
            'user' => $user,
            'livre' => $livre,
            'statut' => 'en_cours'
        ]);

        if ($empruntExistant) {
            $this->addFlash('error', 'Vous avez déjà emprunté ce livre.');
            return $this->redirectToRoute('book_show', ['id' => $livre->getId()]);
        }

        // Créer l'emprunt
        $emprunt = new Emprunt();
        $emprunt->setUser($user);
        $emprunt->setLivre($livre);
        $emprunt->setStatut('en_cours');

        // Réduire le nombre d'exemplaires
        $livre->setNbExemplaires($livre->getNbExemplaires() - 1);

        $entityManager->persist($emprunt);
        $entityManager->flush();

        // Créer une notification pour l'utilisateur
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setTitre('Emprunt confirmé');
        $notification->setMessage('Vous avez emprunté "' . $livre->getTitre() . '". Date de retour prévue : ' . $emprunt->getDateRetourPrevu()->format('d/m/Y'));
        $notification->setType('success');
        $notification->setLien('/emprunts');
        $entityManager->persist($notification);

        // Notifier les admins du nouvel emprunt
        $userName = trim($user->getFirstName() . ' ' . $user->getLastName());
        if (empty($userName)) {
            $userName = $user->getEmail();
        }
        
        $this->notificationService->notifyNewEmprunt(
            $livre->getTitre(),
            $userName,
            '/admin?entityId=' . $emprunt->getId()
        );
        
        $entityManager->flush();

        $this->addFlash('success', 'Livre emprunté avec succès !');
        return $this->redirectToRoute('emprunts');
    }

    #[Route('/retourner/{id}', name: 'emprunt_return', methods: ['POST'])]
    public function retourner(Emprunt $emprunt, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        if (!$user || $emprunt->getUser() !== $user) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('emprunts');
        }

        if ($emprunt->getStatut() === 'retourne') {
            $this->addFlash('error', 'Ce livre a déjà été retourné.');
            return $this->redirectToRoute('emprunts');
        }

        // Marquer comme retourné
        $emprunt->setStatut('retourne');
        $emprunt->setDateRetourEffectif(new \DateTimeImmutable());

        // Augmenter le nombre d'exemplaires
        $livre = $emprunt->getLivre();
        $livre->setNbExemplaires($livre->getNbExemplaires() + 1);

        $entityManager->flush();

        $this->addFlash('success', 'Livre retourné avec succès !');
        return $this->redirectToRoute('emprunts');
    }
}

