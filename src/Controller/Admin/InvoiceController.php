<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Service\FactureService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/invoice')]
class InvoiceController extends AbstractController
{
    public function __construct(private FactureService $factureService)
    {
    }

    #[Route('/generate/{id}', name: 'admin_invoice_generate')]
    public function generate(Commande $commande): Response
    {
        $allowedStatuses = [Commande::STATUS_PAID, Commande::STATUS_PROCESSING, Commande::STATUS_SHIPPED, Commande::STATUS_DELIVERED];
        
        if (!in_array($commande->getStatus(), $allowedStatuses)) {
            $this->addFlash('error', 'Une facture ne peut être générée que pour les commandes payées, en traitement, expédiées ou livrées.');
            return $this->redirectToRoute('admin', [
                'crudAction' => 'detail',
                'crudId' => $commande->getId(),
                'entityFqcn' => Commande::class,
            ]);
        }

        try {
            $facture = $this->factureService->generateFacture($commande);
            $this->addFlash('success', 'Facture générée avec succès: ' . $facture->getNumero());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération de la facture: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin', [
            'crudAction' => 'detail',
            'crudId' => $commande->getId(),
            'entityFqcn' => Commande::class,
        ]);
    }

    #[Route('/download/{id}', name: 'admin_invoice_download')]
    public function download(Commande $commande): Response
    {
        $factures = $commande->getFactures();
        if ($factures->count() === 0) {
            $this->addFlash('error', 'Aucune facture trouvée pour cette commande.');
            return $this->redirectToRoute('admin', [
                'crudAction' => 'detail',
                'crudId' => $commande->getId(),
                'entityFqcn' => Commande::class,
            ]);
        }

        $facture = $factures->first();
        
        try {
            $pdfContent = $this->factureService->getFacturePdfContent($facture);
            
            $response = new Response($pdfContent);
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'attachment; filename="facture_' . $facture->getNumero() . '.pdf"');
            
            return $response;
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du téléchargement de la facture: ' . $e->getMessage());
            return $this->redirectToRoute('admin', [
                'crudAction' => 'detail',
                'crudId' => $commande->getId(),
                'entityFqcn' => Commande::class,
            ]);
        }
    }
}
