<?php

namespace App\Controller;

use App\Repository\CommandeRepository;
use App\Repository\CommentaireRepository;
use App\Repository\EmpruntRepository;
use App\Repository\FavoriRepository;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserDashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'user_dashboard')]
    public function index(
        FavoriRepository $favoriRepository,
        EmpruntRepository $empruntRepository,
        CommentaireRepository $commentaireRepository,
        CommandeRepository $commandeRepository,
        LivreRepository $livreRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Statistiques
        $nombreFavoris = $favoriRepository->count(['user' => $user]);
        $nombreEmprunts = $empruntRepository->count(['user' => $user]);
        $nombreEmpruntsEnCours = $empruntRepository->count(['user' => $user, 'statut' => 'en_cours']);
        $nombreCommentaires = $commentaireRepository->count(['user' => $user]);
        $nombreCommandes = $commandeRepository->count(['user' => $user]);

        // Emprunts en retard
        $empruntsEnRetard = $empruntRepository->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.statut = :statut')
            ->andWhere('e.dateRetourPrevu < :now')
            ->setParameter('user', $user)
            ->setParameter('statut', 'en_cours')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        // Livres les plus empruntés (tous utilisateurs)
        $empruntRepository = $entityManager->getRepository(\App\Entity\Emprunt::class);
        $livresPopulairesData = $empruntRepository->createQueryBuilder('e')
            ->select('IDENTITY(e.livre) as livreId, COUNT(e.id) as nbEmprunts')
            ->groupBy('e.livre')
            ->orderBy('nbEmprunts', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
        
        $livresPopulaires = [];
        foreach ($livresPopulairesData as $row) {
            $livre = $livreRepository->find($row['livreId']);
            if ($livre) {
                $livresPopulaires[] = [
                    'livre' => $livre,
                    'nbEmprunts' => $row['nbEmprunts']
                ];
            }
        }

        // Recommandations basées sur les catégories des favoris
        $favoris = $favoriRepository->findBy(['user' => $user]);
        $categoriesPreferees = [];
        foreach ($favoris as $favori) {
            foreach ($favori->getLivre()->getCategories() as $categorie) {
                $categoriesPreferees[$categorie->getId()] = $categorie;
            }
        }

        $recommandations = [];
        if (!empty($categoriesPreferees)) {
            $categorieIds = array_keys($categoriesPreferees);
            $livresFavorisIds = array_map(function($f) { return $f->getLivre()->getId(); }, $favoris);
            
            $recommandations = $livreRepository->createQueryBuilder('l')
                ->join('l.categories', 'c')
                ->where('c.id IN (:categories)')
                ->andWhere('l.id NOT IN (:favoris)')
                ->andWhere('l.nbExemplaires > 0')
                ->setParameter('categories', $categorieIds)
                ->setParameter('favoris', $livresFavorisIds ?: [0])
                ->setMaxResults(6)
                ->getQuery()
                ->getResult();
        }

        // Si pas assez de recommandations, ajouter des livres récents
        if (count($recommandations) < 6) {
            $livresRecents = $livreRepository->createQueryBuilder('l')
                ->where('l.nbExemplaires > 0')
                ->orderBy('l.dateEdition', 'DESC')
                ->setMaxResults(6 - count($recommandations))
                ->getQuery()
                ->getResult();
            
            $recommandations = array_merge($recommandations, $livresRecents);
            $recommandations = array_slice($recommandations, 0, 6);
        }

        return $this->render('user_dashboard/index.html.twig', [
            'nombreFavoris' => $nombreFavoris,
            'nombreEmprunts' => $nombreEmprunts,
            'nombreEmpruntsEnCours' => $nombreEmpruntsEnCours,
            'nombreCommentaires' => $nombreCommentaires,
            'nombreCommandes' => $nombreCommandes,
            'empruntsEnRetard' => $empruntsEnRetard,
            'livresPopulaires' => $livresPopulaires,
            'recommandations' => $recommandations,
        ]);
    }

    #[Route('/dashboard/orders', name: 'user_orders')]
    public function orders(CommandeRepository $commandeRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        // Récupérer toutes les commandes de l'utilisateur, plus récentes d'abord
        $commandes = $commandeRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('orders/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }
}

