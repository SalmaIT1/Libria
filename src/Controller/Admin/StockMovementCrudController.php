<?php

namespace App\Controller\Admin;

use App\Entity\StockMovement;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class StockMovementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return StockMovement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Mouvement de Stock')
            ->setEntityLabelInPlural('Historique des Mouvements de Stock')
            ->setPageTitle('index', 'Historique des Mouvements de Stock')
            ->setPageTitle('new', 'Nouveau Mouvement de Stock')
            ->setPageTitle('detail', 'Détails du Mouvement')
            ->setSearchFields(['livre.titre', 'notes', 'reason'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(30)
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Informations du Mouvement');
        yield AssociationField::new('livre', 'Livre')
            ->formatValue(function ($value, $entity) {
                $livre = $entity->getLivre();
                return $livre->getTitre() . ' (ISBN: ' . $livre->getIsbn() . ')';
            });
        
        yield ChoiceField::new('type', 'Type de Mouvement')
            ->setChoices([
                'Augmentation' => StockMovement::TYPE_INCREASE,
                'Diminution' => StockMovement::TYPE_DECREASE,
                'Vente' => StockMovement::TYPE_SALE,
                'Retour' => StockMovement::TYPE_RETURN,
                'Ajustement' => StockMovement::TYPE_ADJUSTMENT,
                'Stock Initial' => StockMovement::TYPE_INITIAL,
            ])
            ->renderAsBadges([
                StockMovement::TYPE_INCREASE => 'success',
                StockMovement::TYPE_RETURN => 'success',
                StockMovement::TYPE_INITIAL => 'info',
                StockMovement::TYPE_ADJUSTMENT => 'warning',
                StockMovement::TYPE_DECREASE => 'warning',
                StockMovement::TYPE_SALE => 'primary',
            ]);

        yield FormField::addPanel('Quantités');
        yield IntegerField::new('quantity', 'Quantité');
        yield IntegerField::new('stockBefore', 'Stock Avant');
        yield IntegerField::new('stockAfter', 'Stock Après');

        yield FormField::addPanel('Informations Complémentaires');
        yield ChoiceField::new('reason', 'Raison')
            ->setChoices([
                'Commande' => StockMovement::REASON_ORDER,
                'Retour Client' => StockMovement::REASON_RETURN,
                'Ajustement Manuel' => StockMovement::REASON_ADJUSTMENT,
                'Inventaire' => StockMovement::REASON_INVENTORY,
                'Produit Endommagé' => StockMovement::REASON_DAMAGE,
                'Nouveau Stock' => StockMovement::REASON_NEW_STOCK,
            ]);
        
        yield TextareaField::new('notes', 'Notes');
        
        yield AssociationField::new('user', 'Utilisateur')
            ->formatValue(function ($value, $entity) {
                $user = $entity->getUser();
                return $user ? $user->getFirstName() . ' ' . $user->getLastName() : 'System';
            });
        
        yield AssociationField::new('commande', 'Commande')
            ->formatValue(function ($value, $entity) {
                $commande = $entity->getCommande();
                return $commande ? $commande->getReference() : '-';
            });

        yield FormField::addPanel('Timestamp');
        yield DateTimeField::new('createdAt', 'Date du Mouvement')
            ->setFormat('dd/MM/yyyy HH:mm');
    }
}
