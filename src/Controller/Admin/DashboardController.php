<?php

namespace App\Controller\Admin;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Commentaire;
use App\Entity\Coupon;
use App\Entity\Editeur;
use App\Entity\Livre;
use App\Entity\StockMovement;
use App\Entity\User;
use App\Repository\CommandeRepository;
use App\Repository\CouponRepository;
use App\Repository\StockMovementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    private CommandeRepository $commandeRepository;
    private CouponRepository $couponRepository;
    private StockMovementRepository $stockMovementRepository;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CommandeRepository $commandeRepository,
        CouponRepository $couponRepository,
        StockMovementRepository $stockMovementRepository,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->commandeRepository = $commandeRepository;
        $this->couponRepository = $couponRepository;
        $this->stockMovementRepository = $stockMovementRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/admin/analytics', name: 'admin_analytics')]
    public function analytics(): Response
    {
        // Statistiques générales
        $stats = $this->getGeneralStats();
        
        // Chiffre d'affaires par période
        $revenueStats = $this->getRevenueStats();
        
        // Produits les plus vendus
        $topProducts = $this->getTopSellingProducts();
        
        // Statistiques coupons
        $couponStats = $this->getCouponStats();
        
        // Alertes stock
        $stockAlerts = $this->getStockAlerts();
        
        // Mouvements récents
        $recentMovements = $this->stockMovementRepository->getRecentMovements(10);

        return $this->render('admin/analytics.html.twig', [
            'stats' => $stats,
            'revenueStats' => $revenueStats,
            'topProducts' => $topProducts,
            'couponStats' => $couponStats,
            'stockAlerts' => $stockAlerts,
            'recentMovements' => $recentMovements,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Libria Admin')
            ->setFaviconPath('favicon.ico')
            ->setTranslationDomain('admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Analytics');
        yield MenuItem::linkToRoute('Analytics Dashboard', 'fa fa-chart-line', 'admin_analytics');
        yield MenuItem::section('Library Management');
        yield MenuItem::linkToCrud('Books', 'fa fa-book', Livre::class);
        yield MenuItem::linkToCrud('Authors', 'fa fa-user', Auteur::class);
        yield MenuItem::linkToCrud('Publishers', 'fa fa-building', Editeur::class);
        yield MenuItem::linkToCrud('Categories', 'fa fa-tags', Categorie::class);
        yield MenuItem::linkToCrud('Reviews', 'fa fa-star', Commentaire::class);
        yield MenuItem::section('Stock Management');
        yield MenuItem::linkToCrud('Stock Movements', 'fa fa-exchange-alt', StockMovement::class);
        yield MenuItem::section('Orders & Shipping');
        yield MenuItem::linkToCrud('Orders & Shipping', 'fa fa-shopping-cart', Commande::class);
        yield MenuItem::section('Marketing & Sales');
        yield MenuItem::linkToCrud('Coupons & Promotions', 'fa fa-ticket', Coupon::class);
        yield MenuItem::section('User Management');
        yield MenuItem::linkToCrud('Users', 'fa fa-users', User::class);
        yield MenuItem::section('Navigation');
        yield MenuItem::linkToRoute('Back to Website', 'fa fa-arrow-left', 'home');
        yield MenuItem::linkToLogout('Logout', 'fa fa-sign-out');
    }

    private function getGeneralStats(): array
    {
        $totalUsers = $this->userRepository->count([]);
        $totalOrders = $this->commandeRepository->count([]);
        $totalBooks = $this->entityManager->getRepository(Livre::class)->count([]);
        
        // Chiffre d'affaires total
        $qb = $this->commandeRepository->createQueryBuilder('c')
            ->select('SUM(c.totalAmount) as total')
            ->where('c.status IN (:statuses)')
            ->setParameter('statuses', [Commande::STATUS_PAID, Commande::STATUS_PROCESSING, Commande::STATUS_SHIPPED, Commande::STATUS_DELIVERED]);
        $totalRevenue = $qb->getQuery()->getSingleScalarResult() ?? 0;

        // Commandes du mois
        $monthlyOrders = $this->commandeRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->where('c.createdAt >= :date')
            ->setParameter('date', new \DateTime('first day of this month'))
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return [
            'totalUsers' => $totalUsers,
            'totalOrders' => $totalOrders,
            'totalBooks' => $totalBooks,
            'totalRevenue' => $totalRevenue,
            'monthlyOrders' => $monthlyOrders,
        ];
    }

    private function getRevenueStats(): array
    {
        // Chiffre d'affaires par mois (12 derniers mois)
        $monthlyRevenue = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = new \DateTime("first day of -$i month");
            $endDate = new \DateTime("last day of -$i month");
            
            $qb = $this->commandeRepository->createQueryBuilder('c')
                ->select('SUM(c.totalAmount) as revenue', 'COUNT(c.id) as orders')
                ->where('c.createdAt BETWEEN :start AND :end')
                ->andWhere('c.status IN (:statuses)')
                ->setParameter('start', $date)
                ->setParameter('end', $endDate)
                ->setParameter('statuses', [Commande::STATUS_PAID, Commande::STATUS_PROCESSING, Commande::STATUS_SHIPPED, Commande::STATUS_DELIVERED]);
            
            $result = $qb->getQuery()->getSingleResult();
            $monthlyRevenue[] = [
                'month' => $date->format('M Y'),
                'revenue' => $result['revenue'] ?? 0,
                'orders' => $result['orders'] ?? 0,
            ];
        }

        return $monthlyRevenue;
    }

    private function getTopSellingProducts(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('l.titre', 'COUNT(lc.id) as salesCount', 'SUM(lc.quantity) as totalQuantity')
            ->from(Livre::class, 'l')
            ->join('l.ligneCommandes', 'lc')
            ->join('lc.commande', 'c')
            ->where('c.status IN (:statuses)')
            ->setParameter('statuses', [Commande::STATUS_PAID, Commande::STATUS_PROCESSING, Commande::STATUS_SHIPPED, Commande::STATUS_DELIVERED])
            ->groupBy('l.id, l.titre')
            ->orderBy('totalQuantity', 'DESC')
            ->setMaxResults(10);

        return $qb->getQuery()->getResult();
    }

    private function getCouponStats(): array
    {
        $totalCoupons = $this->couponRepository->count([]);
        $activeCoupons = $this->couponRepository->count(['isActive' => true]);
        
        // Taux d'utilisation
        $qb = $this->couponRepository->createQueryBuilder('c')
            ->select('SUM(c.usedCount) as totalUses', 'SUM(c.maxUses) as maxUses')
            ->where('c.isActive = true');
        $usage = $qb->getQuery()->getSingleResult();
        
        $totalUses = $usage['totalUses'] ?? 0;
        $maxUses = $usage['maxUses'] ?? 0;
        $usageRate = $maxUses > 0 ? ($totalUses / $maxUses) * 100 : 0;

        // Coupons les plus utilisés
        $topCoupons = $this->couponRepository->createQueryBuilder('c')
            ->where('c.usedCount > 0')
            ->orderBy('c.usedCount', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return [
            'totalCoupons' => $totalCoupons,
            'activeCoupons' => $activeCoupons,
            'totalUses' => $totalUses,
            'usageRate' => round($usageRate, 2),
            'topCoupons' => $topCoupons,
        ];
    }

    private function getStockAlerts(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('l.id', 'l.titre', 'l.nbExemplaires', 'COUNT(sm.id) as movementCount')
            ->from(Livre::class, 'l')
            ->leftJoin('App\Entity\StockMovement', 'sm', 'WITH', 'sm.livre = l.id')
            ->where('l.nbExemplaires <= :threshold')
            ->setParameter('threshold', 5)
            ->groupBy('l.id, l.titre, l.nbExemplaires')
            ->orderBy('l.nbExemplaires', 'ASC')
            ->setMaxResults(10);

        $lowStockBooks = $qb->getQuery()->getResult();

        $alerts = [];
        foreach ($lowStockBooks as $book) {
            $severity = $book['nbExemplaires'] <= 2 ? 'critical' : 'warning';
            $alerts[] = [
                'id' => $book['id'],
                'titre' => $book['titre'],
                'stock' => $book['nbExemplaires'],
                'severity' => $severity,
                'message' => $severity === 'critical' 
                    ? "Stock critique : {$book['nbExemplaires']} exemplaire(s)"
                    : "Stock bas : {$book['nbExemplaires']} exemplaire(s)"
            ];
        }

        return $alerts;
    }
}

