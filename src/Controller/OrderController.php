<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use App\Service\FactureService;
use App\Service\StockService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/orders')]
#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    public function __construct(
        private CommandeRepository $commandeRepository,
        private StockService $stockService,
        private FactureService $factureService,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'orders')]
    public function index(): Response
    {
        $user = $this->getUser();
        $commandes = $this->commandeRepository->findByUser($user);

        return $this->render('orders/index.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/{reference}', name: 'order_show')]
    public function show(string $reference): Response
    {
        $user = $this->getUser();
        $commande = $this->commandeRepository->findOneBy(['reference' => $reference, 'user' => $user]);

        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('orders/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{reference}/cancel', name: 'order_cancel', methods: ['POST'])]
    public function cancel(string $reference): Response
    {
        $user = $this->getUser();
        $commande = $this->commandeRepository->findOneBy(['reference' => $reference, 'user' => $user]);

        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        if (!$commande->canBeCancelled()) {
            $this->addFlash('error', 'This order cannot be cancelled.');
            return $this->redirectToRoute('order_show', ['reference' => $commande->getReference()]);
        }

        $commande->setStatus(Commande::STATUS_CANCELLED);
        
        // Restore stock using StockService
        $this->stockService->handleOrderStock($commande, true);
        
        $this->entityManager->flush();

        $this->addFlash('success', 'Order cancelled successfully. Stock has been restored.');
        
        return $this->redirectToRoute('orders');
    }

    #[Route('/{reference}/invoice', name: 'order_invoice')]
    public function downloadInvoice(string $reference): Response
    {
        $user = $this->getUser();
        $commande = $this->commandeRepository->findOneBy(['reference' => $reference, 'user' => $user]);

        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        // Vérifier si la commande a été payée (statut 'paid')
        if ($commande->getStatus() !== Commande::STATUS_PAID) {
            $this->addFlash('error', 'La facture n\'est disponible que pour les commandes payées.');
            return $this->redirectToRoute('order_show', ['reference' => $reference]);
        }

        try {
            // Générer ou récupérer la facture
            $facture = $this->factureService->generateFacture($commande);
            
            // Obtenir le contenu PDF
            $pdfContent = $this->factureService->getFacturePdfContent($facture);
            
            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename="facture_' . $facture->getNumero() . '.pdf"');
            
            return $response;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération de la facture: ' . $e->getMessage());
            return $this->redirectToRoute('order_show', ['reference' => $reference]);
        }
    }
}
