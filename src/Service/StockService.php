<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\Livre;
use App\Entity\StockMovement;
use App\Repository\StockMovementRepository;
use Doctrine\ORM\EntityManagerInterface;

class StockService
{
    private StockMovementRepository $stockMovementRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        StockMovementRepository $stockMovementRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->stockMovementRepository = $stockMovementRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Enregistre un mouvement de stock
     */
    public function recordMovement(
        Livre $livre,
        string $type,
        int $quantity,
        ?string $reason = null,
        ?string $notes = null,
        ?User $user = null,
        ?Commande $commande = null
    ): StockMovement {
        $stockBefore = $livre->getNbExemplaires();
        
        // Calculer le nouveau stock
        $stockAfter = match($type) {
            StockMovement::TYPE_INCREASE => $stockBefore + $quantity,
            StockMovement::TYPE_DECREASE => max(0, $stockBefore - $quantity),
            StockMovement::TYPE_SALE => max(0, $stockBefore - $quantity),
            StockMovement::TYPE_RETURN => $stockBefore + $quantity,
            StockMovement::TYPE_ADJUSTMENT => $quantity, // $quantity est le nouveau stock total
            StockMovement::TYPE_INITIAL => $quantity,
            default => $stockBefore,
        };

        // Créer le mouvement
        $movement = new StockMovement();
        $movement->setLivre($livre);
        $movement->setType($type);
        $movement->setQuantity($quantity);
        $movement->setStockBefore($stockBefore);
        $movement->setStockAfter($stockAfter);
        $movement->setReason($reason);
        $movement->setNotes($notes);
        $movement->setUser($user);
        $movement->setCommande($commande);

        // Mettre à jour le stock du livre
        $livre->setNbExemplaires($stockAfter);

        // Sauvegarder
        $this->entityManager->persist($movement);
        $this->entityManager->flush();

        return $movement;
    }

    /**
     * Gère le stock pour une commande
     */
    public function handleOrderStock(Commande $commande, bool $isCancellation = false): void
    {
        foreach ($commande->getLigneCommandes() as $ligneCommande) {
            $livre = $ligneCommande->getLivre();
            $quantity = $ligneCommande->getQuantity();

            if ($isCancellation) {
                // Annulation de commande : on remet le stock
                $this->recordMovement(
                    $livre,
                    StockMovement::TYPE_RETURN,
                    $quantity,
                    StockMovement::REASON_RETURN,
                    'Annulation commande ' . $commande->getReference(),
                    null,
                    $commande
                );
            } else {
                // Vente : on déduit le stock
                $this->recordMovement(
                    $livre,
                    StockMovement::TYPE_SALE,
                    $quantity,
                    StockMovement::REASON_ORDER,
                    'Vente commande ' . $commande->getReference(),
                    null,
                    $commande
                );
            }
        }
    }

    /**
     * Vérifie si le stock est bas pour un livre
     */
    public function isLowStock(Livre $livre, int $threshold = 5): bool
    {
        return $livre->getNbExemplaires() <= $threshold;
    }

    /**
     * Récupère les livres avec stock bas
     */
    public function getLowStockBooks(int $threshold = 5): array
    {
        return $this->stockMovementRepository->findLowStockBooks($threshold);
    }

    /**
     * Ajuste manuellement le stock
     */
    public function adjustStock(Livre $livre, int $newStock, ?string $notes = null, ?User $user = null): StockMovement
    {
        return $this->recordMovement(
            $livre,
            StockMovement::TYPE_ADJUSTMENT,
            $newStock,
            StockMovement::REASON_ADJUSTMENT,
            $notes,
            $user
        );
    }

    /**
     * Ajoute du nouveau stock
     */
    public function addStock(Livre $livre, int $quantity, ?string $notes = null, ?User $user = null): StockMovement
    {
        return $this->recordMovement(
            $livre,
            StockMovement::TYPE_INCREASE,
            $quantity,
            StockMovement::REASON_NEW_STOCK,
            $notes,
            $user
        );
    }

    /**
     * Enregistre une perte/dégât
     */
    public function recordDamage(Livre $livre, int $quantity, ?string $notes = null, ?User $user = null): StockMovement
    {
        return $this->recordMovement(
            $livre,
            StockMovement::TYPE_DECREASE,
            $quantity,
            StockMovement::REASON_DAMAGE,
            $notes,
            $user
        );
    }

    /**
     * Initialise le stock pour un nouveau livre
     */
    public function initializeStock(Livre $livre, ?User $user = null): StockMovement
    {
        return $this->recordMovement(
            $livre,
            StockMovement::TYPE_INITIAL,
            $livre->getNbExemplaires(),
            StockMovement::REASON_NEW_STOCK,
            'Stock initial',
            $user
        );
    }

    /**
     * Obtient l'historique des mouvements pour un livre
     */
    public function getMovementHistory(Livre $livre, int $limit = 50): array
    {
        return $this->stockMovementRepository->findByLivre($livre, $limit);
    }

    /**
     * Obtient les statistiques de stock
     */
    public function getStockStats(): array
    {
        return $this->stockMovementRepository->getStockStats();
    }

    /**
     * Obtient les livres les plus vendus
     */
    public function getTopMovingBooks(int $limit = 10): array
    {
        return $this->stockMovementRepository->getTopMovingBooks($limit);
    }

    /**
     * Obtient les mouvements récents
     */
    public function getRecentMovements(int $limit = 20): array
    {
        return $this->stockMovementRepository->getRecentMovements($limit);
    }

    /**
     * Vérifie les alertes de stock bas
     */
    public function checkLowStockAlerts(int $threshold = 5): array
    {
        $lowStockBooks = $this->getLowStockBooks($threshold);
        $alerts = [];

        foreach ($lowStockBooks as $book) {
            $alerts[] = [
                'livre_id' => $book['id'],
                'titre' => $book['titre'],
                'current_stock' => $book['currentStock'],
                'movement_count' => $book['movementCount'],
                'severity' => $book['currentStock'] <= 2 ? 'critical' : 'warning',
                'message' => $book['currentStock'] <= 2 
                    ? "Stock critique : {$book['currentStock']} exemplaire(s) restant(s)"
                    : "Stock bas : {$book['currentStock']} exemplaire(s) restant(s)"
            ];
        }

        return $alerts;
    }
}
