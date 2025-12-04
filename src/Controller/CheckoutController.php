<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\LignePanier;
use App\Entity\Panier;
use App\Form\CheckoutType;
use App\Repository\CommandeRepository;
use App\Repository\PanierRepository;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkout')]
class CheckoutController extends AbstractController
{
    public function __construct(
        private PanierRepository $panierRepository,
        private CommandeRepository $commandeRepository,
        private EntityManagerInterface $entityManager,
        private StockService $stockService
    ) {
    }

    #[Route('/', name: 'checkout')]
    public function index(Request $request): Response
    {
        $user = $this->getUser();
        $panier = $this->panierRepository->findByUser($user);

        if (!$panier || $panier->isEmpty()) {
            $this->addFlash('warning', 'Your cart is empty. Please add some books before checkout.');
            return $this->redirectToRoute('cart');
        }

        $form = $this->createForm(CheckoutType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            // Create order
            $commande = new Commande();
            $commande->setUser($user);
            $commande->setTotalAmount($panier->getTotalAmountWithDiscount());
            $commande->setShippingCost('8.00'); // Fixed shipping cost
            $commande->setPaymentMethod($data['paymentMethod']);
            $commande->setShippingAddress($data['shippingAddress']);
            $commande->setBillingAddress($data['billingAddress'] ?? $data['shippingAddress']);
            $commande->setNotes($data['notes'] ?? null);
            
            // Store coupon information if applied
            if ($panier->getCoupon()) {
                $commande->setCouponCode($panier->getCoupon()->getCode());
                $commande->setDiscountAmount($panier->getDiscountAmount());
            }

            // Create order lines from cart
            foreach ($panier->getLignePaniers() as $lignePanier) {
                $ligneCommande = new LigneCommande();
                $ligneCommande->setCommande($commande);
                $ligneCommande->setLivre($lignePanier->getLivre());
                $ligneCommande->setQuantity($lignePanier->getQuantity());
                $ligneCommande->setPrice($lignePanier->getPrice());
                $ligneCommande->setTotal($lignePanier->getTotal());
                
                $this->entityManager->persist($ligneCommande);
            }

            // Handle stock movements using StockService
            $this->stockService->handleOrderStock($commande, false);

            // Increment coupon usage if coupon was applied
            if ($panier->getCoupon()) {
                $panier->getCoupon()->incrementUsage();
            }

            // Clear cart
            foreach ($panier->getLignePaniers() as $lignePanier) {
                $this->entityManager->remove($lignePanier);
            }

            $this->entityManager->persist($commande);
            $this->entityManager->flush();

            $this->addFlash('success', 'Order placed successfully! Your order reference is: ' . $commande->getReference());
            
            return $this->redirectToRoute('order_confirmation', ['reference' => $commande->getReference()]);
        }

        return $this->render('checkout/index.html.twig', [
            'panier' => $panier,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/confirmation/{reference}', name: 'order_confirmation')]
    public function confirmation(string $reference): Response
    {
        $user = $this->getUser();
        $commande = $this->commandeRepository->findOneBy(['reference' => $reference, 'user' => $user]);

        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('checkout/confirmation.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/payment/success/{reference}', name: 'payment_success')]
    public function paymentSuccess(string $reference): Response
    {
        $user = $this->getUser();
        $commande = $this->commandeRepository->findOneBy(['reference' => $reference, 'user' => $user]);

        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        $commande->setStatus(Commande::STATUS_PAID);
        $commande->setPaidAt(new \DateTimeImmutable());
        $commande->setStatus(Commande::STATUS_PROCESSING);
        
        $this->entityManager->flush();

        $this->addFlash('success', 'Payment successful! Your order is now being processed.');
        
        return $this->redirectToRoute('order_confirmation', ['reference' => $reference]);
    }

    #[Route('/payment/cancel/{reference}', name: 'payment_cancel')]
    public function paymentCancel(string $reference): Response
    {
        $user = $this->getUser();
        $commande = $this->commandeRepository->findOneBy(['reference' => $reference, 'user' => $user]);

        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        $commande->setStatus(Commande::STATUS_CANCELLED);
        
        // Restore stock using StockService
        $this->stockService->handleOrderStock($commande, true);
        
        $this->entityManager->flush();

        $this->addFlash('error', 'Payment was cancelled. Your order has been cancelled and stock has been restored.');
        
        return $this->redirectToRoute('cart');
    }
}
