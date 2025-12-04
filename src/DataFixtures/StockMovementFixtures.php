<?php

namespace App\DataFixtures;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\StockMovement;
use App\Entity\User;
use App\Entity\Livre;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StockMovementFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Créer les mouvements de stock
        $stockMovementsData = [
            // Mouvements pour "Les Misérables" (correspond à "Beloved")
            [
                'livre_titre' => 'Les Misérables',
                'type' => 'ENTREE',
                'quantity' => 50,
                'reason' => 'Stock initial',
                'date' => new \DateTime('2024-01-01'),
            ],
            [
                'livre_titre' => 'Les Misérables',
                'type' => 'SORTIE',
                'quantity' => 1,
                'reason' => 'Vente unitaire',
                'date' => new \DateTime('2024-01-15'),
            ],
            // Mouvements pour "À la recherche du temps perdu"
            [
                'livre_titre' => 'À la recherche du temps perdu',
                'type' => 'ENTREE',
                'quantity' => 30,
                'reason' => 'Stock initial',
                'date' => new \DateTime('2024-01-01'),
            ],
            [
                'livre_titre' => 'À la recherche du temps perdu',
                'type' => 'SORTIE',
                'quantity' => 1,
                'reason' => 'Vente unitaire',
                'date' => new \DateTime('2024-01-20'),
            ],
            // Mouvements pour "1984"
            [
                'livre_titre' => '1984',
                'type' => 'ENTREE',
                'quantity' => 40,
                'reason' => 'Stock initial',
                'date' => new \DateTime('2024-01-01'),
            ],
            [
                'livre_titre' => '1984',
                'type' => 'SORTIE',
                'quantity' => 1,
                'reason' => 'Vente unitaire',
                'date' => new \DateTime('2024-01-25'),
            ],
            // Mouvements additionnels pour simuler plus d'activité
            [
                'livre_titre' => 'L\'Étranger',
                'type' => 'ENTREE',
                'quantity' => 25,
                'reason' => 'Réapprovisionnement',
                'date' => new \DateTime('2024-02-01'),
            ],
            [
                'livre_titre' => 'L\'Étranger',
                'type' => 'SORTIE',
                'quantity' => 3,
                'reason' => 'Ventes multiples',
                'date' => new \DateTime('2024-02-10'),
            ],
            [
                'livre_titre' => 'Le Vieil Homme et la Mer',
                'type' => 'ENTREE',
                'quantity' => 20,
                'reason' => 'Stock initial',
                'date' => new \DateTime('2024-01-01'),
            ],
            [
                'livre_titre' => 'Germinal',
                'type' => 'SORTIE',
                'quantity' => 2,
                'reason' => 'Vente groupée',
                'date' => new \DateTime('2024-02-15'),
            ],
        ];

        foreach ($stockMovementsData as $movementData) {
            $livre = $manager->getRepository(Livre::class)->findOneBy(['titre' => $movementData['livre_titre']]);
            if ($livre) {
                $stockBefore = $livre->getNbExemplaires();
                
                $stockMovement = new StockMovement();
                $stockMovement->setLivre($livre);
                $stockMovement->setType($movementData['type']);
                $stockMovement->setQuantity($movementData['quantity']);
                $stockMovement->setReason($movementData['reason']);
                $stockMovement->setCreatedAt($movementData['date']);
                $stockMovement->setStockBefore($stockBefore);
                
                // Calculate stock after movement
                if ($movementData['type'] === 'ENTREE') {
                    $stockAfter = $stockBefore + $movementData['quantity'];
                    $livre->setNbExemplaires($stockAfter);
                } else {
                    $stockAfter = max(0, $stockBefore - $movementData['quantity']);
                    $livre->setNbExemplaires($stockAfter);
                }
                
                $stockMovement->setStockAfter($stockAfter);
                
                $manager->persist($stockMovement);
                $manager->persist($livre);
            }
        }

        // Créer des commandes avec les livres spécifiés
        $commandesData = [
            [
                'user_email' => 'jean.dupont@libria.com',
                'status' => 'delivered',
                'created_at' => new \DateTimeImmutable('2024-01-15'),
                'items' => [
                    ['livre_titre' => 'Les Misérables', 'quantity' => 1, 'price' => 29.99],
                ],
            ],
            [
                'user_email' => 'marie.martin@libria.com',
                'status' => 'delivered',
                'created_at' => new \DateTimeImmutable('2024-01-20'),
                'items' => [
                    ['livre_titre' => 'À la recherche du temps perdu', 'quantity' => 1, 'price' => 35.50],
                ],
            ],
            [
                'user_email' => 'pierre.bernard@libria.com',
                'status' => 'delivered',
                'created_at' => new \DateTimeImmutable('2024-01-25'),
                'items' => [
                    ['livre_titre' => '1984', 'quantity' => 1, 'price' => 19.99],
                ],
            ],
            [
                'user_email' => 'sophie.durand@libria.com',
                'status' => 'processing',
                'created_at' => new \DateTimeImmutable('2024-02-10'),
                'items' => [
                    ['livre_titre' => 'L\'Étranger', 'quantity' => 3, 'price' => 15.99],
                ],
            ],
            [
                'user_email' => 'lucas.moreau@libria.com',
                'status' => 'shipped',
                'created_at' => new \DateTimeImmutable('2024-02-15'),
                'items' => [
                    ['livre_titre' => 'Germinal', 'quantity' => 2, 'price' => 22.50],
                ],
            ],
        ];

        foreach ($commandesData as $commandeData) {
            $user = $manager->getRepository(User::class)->findOneBy(['email' => $commandeData['user_email']]);
            if ($user) {
                $commande = new Commande();
                $commande->setUser($user);
                $commande->setStatus($commandeData['status']);
                $commande->setCreatedAt($commandeData['created_at']);
                $commande->setShippingCost(5.00);
                
                $totalAmount = 0;
                
                foreach ($commandeData['items'] as $itemData) {
                    $livre = $manager->getRepository(Livre::class)->findOneBy(['titre' => $itemData['livre_titre']]);
                    if ($livre) {
                        $ligneCommande = new LigneCommande();
                        $ligneCommande->setCommande($commande);
                        $ligneCommande->setLivre($livre);
                        $ligneCommande->setQuantity($itemData['quantity']);
                        $ligneCommande->setPrice($itemData['price']);
                        $ligneCommande->setTotal($itemData['price'] * $itemData['quantity']);
                        
                        $manager->persist($ligneCommande);
                        $totalAmount += $itemData['price'] * $itemData['quantity'];
                    }
                }
                
                $commande->setTotalAmount($totalAmount);
                $manager->persist($commande);
            }
        }

        $manager->flush();
    }
}
