<?php

namespace App\Controller\Admin;

use App\Entity\Coupon;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class CouponCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Coupon::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Coupon')
            ->setEntityLabelInPlural('Coupons & Promotions')
            ->setPageTitle('index', 'Gestion des Coupons')
            ->setPageTitle('new', 'Créer un nouveau Coupon')
            ->setPageTitle('edit', 'Modifier un Coupon')
            ->setPageTitle('detail', 'Détails du Coupon')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['code', 'description'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_EDIT, Action::SAVE_AND_CONTINUE, function (Action $action) {
                return $action->setLabel('Save and continue');
            })
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER, function (Action $action) {
                return $action->setLabel('Save and add another');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->displayIf(function (Coupon $coupon) {
                    return $coupon->getUsedCount() === 0;
                });
            });
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm()
                ->setLabel('ID'),
                
            TextField::new('code')
                ->setLabel('Code du Coupon')
                ->setHelp('Code unique (ex: WELCOME10, SUMMER2024)')
                ->setRequired(true)
                ->setMaxLength(20),
                
            ChoiceField::new('type')
                ->setLabel('Type de Réduction')
                ->setChoices([
                    'Pourcentage (%)' => 'percentage',
                    'Montant Fixe (TND)' => 'fixed',
                ])
                ->setRequired(true)
                ->renderExpanded(false),
                
            NumberField::new('value')
                ->setLabel('Valeur de Réduction')
                ->setHelp('Pourcentage (ex: 10) ou Montant fixe (ex: 5.00)')
                ->setRequired(true)
                ->setNumDecimals(2),
                
            TextareaField::new('description')
                ->setLabel('Description')
                ->setHelp('Description visible par le client (ex: 10% de réduction sur votre première commande)')
                ->hideOnIndex()
                ->setRequired(false),
                
            NumberField::new('minimumAmount')
                ->setLabel('Montant Minimum d\'Achat (TND)')
                ->setHelp('Montant minimum requis pour utiliser ce coupon (laisser vide si aucun minimum)')
                ->setNumDecimals(2)
                ->setRequired(false),
                
            IntegerField::new('maxUses')
                ->setLabel('Nombre d\'Utilisations Maximales')
                ->setHelp('Laissez 0 pour illimité')
                ->setRequired(false)
                ->setFormTypeOption('empty_data', 0),
                
            IntegerField::new('usedCount')
                ->setLabel('Nombre d\'Utilisations')
                ->setHelp('Combien de fois ce coupon a été utilisé')
                ->hideOnForm()
                ->setFormTypeOption('disabled', true),
                
            DateTimeField::new('createdAt')
                ->setLabel('Date de Création')
                ->hideOnForm()
                ->setFormat('dd/MM/yyyy HH:mm'),
                
            DateTimeField::new('expiresAt')
                ->setLabel('Date d\'Expiration')
                ->setHelp('Laissez vide si le coupon n\'expire jamais')
                ->setRequired(false)
                ->setFormat('dd/MM/yyyy HH:mm'),
                
            BooleanField::new('isActive')
                ->setLabel('Actif')
                ->setHelp('Désactivez pour rendre le coupon inutilisable temporairement')
                ->renderAsSwitch(false),
        ];
    }
}
