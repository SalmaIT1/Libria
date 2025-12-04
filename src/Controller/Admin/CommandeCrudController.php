<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Service\FactureService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommandeCrudController extends AbstractCrudController
{
    public function __construct(private FactureService $factureService)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Commande::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Gestion des Commandes')
            ->setPageTitle('index', 'Commandes & Livraisons')
            ->setPageTitle('new', 'Créer une Commande')
            ->setPageTitle('edit', 'Modifier une Commande')
            ->setPageTitle('detail', 'Détails de la Commande')
            ->setSearchFields(['reference', 'user.email', 'user.firstName', 'user.lastName'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        // Ajouter l'action de génération de facture
        $generateInvoiceAction = Action::new('generateInvoice', 'Générer Facture', 'bi bi-file-earmark-pdf')
            ->displayIf(fn ($entity) => in_array($entity->getStatus(), [Commande::STATUS_PAID, Commande::STATUS_PROCESSING, Commande::STATUS_SHIPPED, Commande::STATUS_DELIVERED]))
            ->setCssClass('btn btn-primary')
            ->linkToUrl(function (Commande $entity) {
                return '/admin/invoice/generate/' . $entity->getId();
            });

        // Ajouter l'action de téléchargement de facture
        $downloadInvoiceAction = Action::new('downloadInvoice', 'Télécharger Facture', 'bi bi-download')
            ->displayIf(fn ($entity) => $entity->getFactures()->count() > 0)
            ->setCssClass('btn btn-success')
            ->linkToUrl(function (Commande $entity) {
                return '/admin/invoice/download/' . $entity->getId();
            });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $generateInvoiceAction)
            ->add(Crud::PAGE_DETAIL, $downloadInvoiceAction)
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier');
            })
            ->update(Crud::PAGE_DETAIL, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier la commande');
            })
            ->add(Crud::PAGE_DETAIL, Action::new('shipOrder', 'Marquer comme Expédiée', 'bi bi-truck')
                ->displayIf(fn ($entity) => $entity->canBeShipped())
                ->setCssClass('btn btn-success')
                ->linkToCrudAction('shipOrder'))
            ->add(Crud::PAGE_DETAIL, Action::new('deliverOrder', 'Marquer comme Livrée', 'bi bi-check-circle')
                ->displayIf(fn ($entity) => $entity->canBeDelivered())
                ->setCssClass('btn btn-info')
                ->linkToCrudAction('deliverOrder'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Informations Commande');
        yield TextField::new('reference', 'Référence')->onlyOnDetail();
        yield AssociationField::new('user', 'Client')
            ->formatValue(function ($value, $entity) {
                return $entity->getUser()->getFirstName() . ' ' . $entity->getUser()->getLastName() . ' (' . $entity->getUser()->getEmail() . ')';
            });
        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'En Attente' => Commande::STATUS_PENDING,
                'Payée' => Commande::STATUS_PAID,
                'En Traitement' => Commande::STATUS_PROCESSING,
                'Expédiée' => Commande::STATUS_SHIPPED,
                'Livrée' => Commande::STATUS_DELIVERED,
                'Annulée' => Commande::STATUS_CANCELLED,
            ])
            ->renderAsBadges([
                Commande::STATUS_PENDING => 'warning',
                Commande::STATUS_PAID => 'info',
                Commande::STATUS_PROCESSING => 'primary',
                Commande::STATUS_SHIPPED => 'success',
                Commande::STATUS_DELIVERED => 'success',
                Commande::STATUS_CANCELLED => 'danger',
            ]);

        yield FormField::addPanel('Informations Financières');
        yield MoneyField::new('totalAmount', 'Montant Total')->setCurrency('TND');
        yield MoneyField::new('shippingCost', 'Frais de Livraison')->setCurrency('TND');
        yield TextField::new('grandTotal', 'Grand Total')
            ->formatValue(function ($value, $entity) {
                return $entity->getGrandTotal() . ' TND';
            })
            ->onlyOnDetail();
        yield ChoiceField::new('paymentMethod', 'Méthode de Paiement')
            ->setChoices([
                'Carte de Crédit' => 'credit_card',
                'Carte de Débit' => 'debit_card',
                'PayPal' => 'paypal',
                'Virement Bancaire' => 'bank_transfer',
            ]);

        yield FormField::addPanel('Informations Livraison');
        yield TextField::new('shippingAddress', 'Adresse de Livraison');
        yield TextField::new('billingAddress', 'Adresse de Facturation');
        yield TextField::new('trackingNumber', 'Numéro de Suivi');
        yield TextareaField::new('notes', 'Notes');

        yield FormField::addPanel('Timeline');
        yield DateTimeField::new('createdAt', 'Créée le')->onlyOnDetail();
        yield DateTimeField::new('paidAt', 'Payée le')->onlyOnDetail();
        yield DateTimeField::new('shippedAt', 'Expédiée le')->onlyOnDetail();
        yield DateTimeField::new('deliveredAt', 'Livrée le')->onlyOnDetail();

        yield AssociationField::new('ligneCommandes', 'Articles Commandés')
            ->hideOnForm()
            ->formatValue(function ($value, $entity) {
                return count($entity->getLigneCommandes()) . ' articles';
            });
    }

    public function shipOrder(Commande $commande)
    {
        if (!$commande->canBeShipped()) {
            $this->addFlash('error', 'Cette commande ne peut pas être expédiée.');
            return $this->redirectToRoute('admin', [
                'crudAction' => 'detail',
                'crudId' => $commande->getId(),
                'entityFqcn' => Commande::class,
            ]);
        }

        $commande->setStatus(Commande::STATUS_SHIPPED);
        $commande->setShippedAt(new \DateTimeImmutable());
        
        // Generate tracking number if not exists
        if (!$commande->getTrackingNumber()) {
            $commande->setTrackingNumber('TRK-' . strtoupper(uniqid()));
        }

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', 'Commande marquée comme expédiée avec numéro de suivi: ' . $commande->getTrackingNumber());

        return $this->redirectToRoute('admin', [
            'crudAction' => 'detail',
            'crudId' => $commande->getId(),
            'entityFqcn' => Commande::class,
        ]);
    }

    public function deliverOrder(Commande $commande)
    {
        if (!$commande->canBeDelivered()) {
            $this->addFlash('error', 'Cette commande ne peut pas être marquée comme livrée.');
            return $this->redirectToRoute('admin', [
                'crudAction' => 'detail',
                'crudId' => $commande->getId(),
                'entityFqcn' => Commande::class,
            ]);
        }

        $commande->setStatus(Commande::STATUS_DELIVERED);
        $commande->setDeliveredAt(new \DateTimeImmutable());

        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', 'Commande marquée comme livrée.');

        return $this->redirectToRoute('admin', [
            'crudAction' => 'detail',
            'crudId' => $commande->getId(),
            'entityFqcn' => Commande::class,
        ]);
    }

    public function generateInvoice(Commande $commande)
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

    public function downloadInvoice(Commande $commande): Response
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
