<?php

namespace App\Controller;

use App\Entity\LignePanier;
use App\Entity\Livre;
use App\Entity\Panier;
use App\Entity\Coupon;
use App\Repository\LignePanierRepository;
use App\Repository\LivreRepository;
use App\Repository\PanierRepository;
use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cart')]
class CartController extends AbstractController
{
    public function __construct(
        private PanierRepository $panierRepository,
        private LignePanierRepository $lignePanierRepository,
        private LivreRepository $livreRepository,
        private CouponRepository $couponRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'cart')]
    public function index(): Response
    {
        $user = $this->getUser();
        $panier = $this->getOrCreateCart($user);

        return $this->render('cart/index.html.twig', [
            'panier' => $panier,
        ]);
    }

    #[Route('/add/{id}', name: 'cart_add', methods: ['POST'])]
    public function add(Livre $livre, Request $request): JsonResponse
    {
        $user = $this->getUser();
        $panier = $this->getOrCreateCart($user);

        $quantity = (int) $request->request->get('quantity', 1);
        
        if ($quantity <= 0) {
            return new JsonResponse(['error' => 'Quantity must be greater than 0'], 400);
        }

        if (!$livre->isAvailableForSale($quantity)) {
            $availableCount = $livre->getAvailableForSaleCount();
            if ($availableCount === 0) {
                return new JsonResponse(['error' => 'This book is reserved for library borrowing only'], 400);
            } else {
                return new JsonResponse(['error' => "Only {$availableCount} copies available for sale"], 400);
            }
        }

        $existingLigne = $this->lignePanierRepository->findByPanierAndLivre($panier, $livre);

        if ($existingLigne) {
            $newQuantity = $existingLigne->getQuantity() + $quantity;
            if (!$livre->isAvailableForSale($newQuantity)) {
                $availableCount = $livre->getAvailableForSaleCount();
                return new JsonResponse(['error' => "Only {$availableCount} copies available for sale"], 400);
            }
            $existingLigne->setQuantity($newQuantity);
        } else {
            $lignePanier = new LignePanier();
            $lignePanier->setPanier($panier);
            $lignePanier->setLivre($livre);
            $lignePanier->setQuantity($quantity);
            $lignePanier->setPrice($livre->getPrix());
            
            $this->entityManager->persist($lignePanier);
        }

        $panier->updateTimestamp();
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Book added to cart successfully',
            'cartCount' => $panier->getTotalItems()
        ]);
    }

    #[Route('/update/{id}', name: 'cart_update', methods: ['POST'])]
    public function update(LignePanier $lignePanier, Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if ($lignePanier->getPanier()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        $quantity = (int) $request->request->get('quantity', 1);
        
        if ($quantity <= 0) {
            return new JsonResponse(['error' => 'Quantity must be greater than 0'], 400);
        }

        $livre = $lignePanier->getLivre();
        
        if (!$livre->isAvailableForSale($quantity)) {
            $availableCount = $livre->getAvailableForSaleCount();
            if ($availableCount === 0) {
                return new JsonResponse(['error' => 'This book is reserved for library borrowing only'], 400);
            } else {
                return new JsonResponse(['error' => "Only {$availableCount} copies available for sale"], 400);
            }
        }

        $lignePanier->setQuantity($quantity);
        $lignePanier->getPanier()->updateTimestamp();
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Cart updated successfully',
            'cartCount' => $lignePanier->getPanier()->getTotalItems(),
            'itemTotal' => $lignePanier->getFormattedTotal(),
            'cartTotal' => $lignePanier->getPanier()->getFormattedTotalAmount()
        ]);
    }

    #[Route('/remove/{id}', name: 'cart_remove', methods: ['POST'])]
    public function remove(LignePanier $lignePanier): JsonResponse
    {
        $user = $this->getUser();
        
        if ($lignePanier->getPanier()->getUser() !== $user) {
            return new JsonResponse(['error' => 'Unauthorized'], 403);
        }

        $panier = $lignePanier->getPanier();
        
        $this->entityManager->remove($lignePanier);
        $panier->updateTimestamp();
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Item removed from cart',
            'cartCount' => $panier->getTotalItems(),
            'cartTotal' => $panier->getFormattedTotalAmount()
        ]);
    }

    #[Route('/clear', name: 'cart_clear', methods: ['POST'])]
    public function clear(): JsonResponse
    {
        $user = $this->getUser();
        $panier = $this->getOrCreateCart($user);

        foreach ($panier->getLignePaniers() as $lignePanier) {
            $this->entityManager->remove($lignePanier);
        }

        $panier->updateTimestamp();
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Cart cleared successfully',
            'cartCount' => 0
        ]);
    }

    #[Route('/count', name: 'cart_count')]
    #[IsGranted('ROLE_USER')]
    public function count(): JsonResponse
    {
        $user = $this->getUser();
        $panier = $this->getOrCreateCart($user);

        return new JsonResponse([
            'count' => $panier->getTotalItems()
        ]);
    }

    #[Route('/apply-coupon', name: 'cart_apply_coupon', methods: ['POST'])]
    public function applyCoupon(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $panier = $this->getOrCreateCart($user);
        
        $code = strtoupper($request->request->get('code', ''));
        
        if (!$code) {
            return new JsonResponse(['success' => false, 'message' => 'Code du coupon requis'], 400);
        }

        $coupon = $this->couponRepository->findOneBy(['code' => $code]);

        if (!$coupon) {
            return new JsonResponse(['success' => false, 'message' => 'Coupon invalide'], 404);
        }

        if (!$coupon->isValid()) {
            return new JsonResponse(['success' => false, 'message' => 'Coupon expiré ou invalide'], 400);
        }

        $cartAmount = $panier->getTotalAmount();
        if ($coupon->getMinimumAmount() && $cartAmount < (float)$coupon->getMinimumAmount()) {
            return new JsonResponse(['success' => false, 'message' => 'Montant minimum non atteint'], 400);
        }

        $discount = $coupon->calculateDiscount($cartAmount);

        // Apply coupon to cart
        $panier->setCoupon($coupon);
        $panier->updateTimestamp();
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Coupon appliqué avec succès',
            'coupon' => [
                'code' => $coupon->getCode(),
                'description' => $coupon->getDescription(),
                'formattedValue' => $coupon->getFormattedValue(),
            ],
            'discount' => $discount,
            'discountFormatted' => number_format($discount, 2, ',', ' ') . ' TND',
            'newTotal' => $panier->getTotalAmountWithDiscount(),
            'newTotalFormatted' => $panier->getFormattedTotalAmountWithDiscount()
        ]);
    }

    #[Route('/remove-coupon', name: 'cart_remove_coupon', methods: ['POST'])]
    public function removeCoupon(): JsonResponse
    {
        $user = $this->getUser();
        $panier = $this->getOrCreateCart($user);

        if (!$panier->getCoupon()) {
            return new JsonResponse(['success' => false, 'message' => 'Aucun coupon à supprimer'], 400);
        }

        $panier->setCoupon(null);
        $panier->updateTimestamp();
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Coupon supprimé avec succès',
            'newTotal' => $panier->getTotalAmountWithDiscount(),
            'newTotalFormatted' => $panier->getFormattedTotalAmountWithDiscount()
        ]);
    }

    private function getOrCreateCart($user = null): Panier
    {
        if ($user) {
            $panier = $this->panierRepository->findByUser($user);
            
            if (!$panier) {
                $panier = new Panier();
                $panier->setUser($user);
                $this->entityManager->persist($panier);
                $this->entityManager->flush();
            }
            
            return $panier;
        }
        
        // Pour les utilisateurs non authentifiés, créer un panier temporaire
        $sessionId = $this->getSession()->getId();
        $panier = $this->panierRepository->findBySessionId($sessionId);
        
        if (!$panier) {
            $panier = new Panier();
            $panier->setSessionId($sessionId);
            $this->entityManager->persist($panier);
            $this->entityManager->flush();
        }
        
        return $panier;
    }
}
