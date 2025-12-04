<?php

namespace App\Service;

use App\Entity\Commande;
use App\Entity\Facture;
use App\Repository\FactureRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;

class FactureService
{
    public function __construct(
        private FactureRepository $factureRepository,
        private Environment $twig,
        private ParameterBagInterface $parameterBag,
        private Filesystem $filesystem
    ) {
    }

    public function generateFacture(Commande $commande): Facture
    {
        // Vérifier si une facture existe déjà pour cette commande
        $existingFacture = $this->factureRepository->findByCommande($commande);
        if ($existingFacture) {
            // Mettre à jour le statut de paiement si nécessaire
            $this->updatePaymentStatus($existingFacture, $commande);
            return $existingFacture;
        }

        // Créer une nouvelle facture
        $facture = new Facture();
        $facture->setCommande($commande);
        $facture->calculateAmounts();
        
        // Mettre à jour le statut de paiement
        $this->updatePaymentStatus($facture, $commande);

        // Générer le PDF
        $pdfPath = $this->generatePdf($facture);
        $facture->setFilePath($pdfPath);

        // Sauvegarder la facture
        $this->factureRepository->save($facture, true);

        return $facture;
    }
    
    private function updatePaymentStatus(Facture $facture, Commande $commande): void
    {
        // Une facture est considérée comme payée si la commande a un statut qui indique le paiement
        $paidStatuses = [Commande::STATUS_PAID, Commande::STATUS_PROCESSING, Commande::STATUS_SHIPPED, Commande::STATUS_DELIVERED];
        $isPaid = in_array($commande->getStatus(), $paidStatuses);
        
        $facture->setIsPaid($isPaid);
    }

    public function generatePdf(Facture $facture): string
    {
        // Configuration de DOMPDF
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);

        // Générer le contenu HTML
        $html = $this->twig->render('facture/facture.html.twig', [
            'facture' => $facture,
            'commande' => $facture->getCommande(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Créer le répertoire des factures s'il n'existe pas
        $facturesDir = $this->parameterBag->get('kernel.project_dir') . '/public/factures';
        if (!$this->filesystem->exists($facturesDir)) {
            $this->filesystem->mkdir($facturesDir);
        }

        // Sauvegarder le fichier PDF
        $filename = 'facture_' . $facture->getNumero() . '.pdf';
        $filePath = $facturesDir . '/' . $filename;

        file_put_contents($filePath, $dompdf->output());

        return '/factures/' . $filename;
    }

    public function getFacturePdfContent(Facture $facture): string
    {
        if (!$facture->getFilePath()) {
            throw new \Exception('Aucun fichier PDF trouvé pour cette facture');
        }

        $fullPath = $this->parameterBag->get('kernel.project_dir') . '/public' . $facture->getFilePath();
        
        if (!$this->filesystem->exists($fullPath)) {
            // Régénérer le PDF s'il n'existe plus
            $pdfPath = $this->generatePdf($facture);
            $facture->setFilePath($pdfPath);
            $this->factureRepository->save($facture, true);
            $fullPath = $this->parameterBag->get('kernel.project_dir') . '/public' . $facture->getFilePath();
        }

        return file_get_contents($fullPath);
    }

    public function deleteFacture(Facture $facture): void
    {
        // Supprimer le fichier PDF
        if ($facture->getFilePath()) {
            $fullPath = $this->parameterBag->get('kernel.project_dir') . '/public' . $facture->getFilePath();
            if ($this->filesystem->exists($fullPath)) {
                $this->filesystem->remove($fullPath);
            }
        }

        // Supprimer l'entité
        $this->factureRepository->remove($facture, true);
    }
}
