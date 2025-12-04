<?php

namespace App\Controller\Admin;

use App\Entity\LigneCommande;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class LigneCommandeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return LigneCommande::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Ligne de Commande')
            ->setEntityLabelInPlural('Détails des Commandes')
            ->setPageTitle('index', 'Détails des Commandes')
            ->setPageTitle('detail', 'Détail de la Ligne de Commande')
            ->setSearchFields(['livre.titre', 'commande.reference'])
            ->setDefaultSort(['commande.createdAt' => 'DESC'])
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
        yield FormField::addPanel('Informations Commande');
        yield AssociationField::new('commande', 'Commande')
            ->formatValue(function ($value, $entity) {
                $commande = $entity->getCommande();
                return $commande->getReference() . ' - ' . $commande->getUser()->getFirstName() . ' ' . $commande->getUser()->getLastName();
            });
        yield DateTimeField::new('commande.createdAt', 'Date Commande')
            ->formatValue(function ($value, $entity) {
                return $entity->getCommande()->getCreatedAt()->format('d/m/Y H:i');
            });

        yield FormField::addPanel('Informations Produit');
        yield AssociationField::new('livre', 'Livre')
            ->formatValue(function ($value, $entity) {
                $livre = $entity->getLivre();
                return $livre->getTitre() . ' - ' . implode(', ', array_map(function($auteur) { 
                    return $auteur->getPrenom() . ' ' . $auteur->getNom(); 
                }, $livre->getAuteurs()->toArray()));
            });
        yield TextField::new('livre.isbn', 'ISBN')->onlyOnDetail();
        yield TextField::new('livre.prix', 'Prix Unitaire')->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                return $entity->getLivre()->getPrix() . ' TND';
            });

        yield FormField::addPanel('Informations Ligne');
        yield NumberField::new('quantite', 'Quantité');
        yield MoneyField::new('prix', 'Prix Unitaire')->setCurrency('TND');
        yield MoneyField::new('total', 'Total Ligne')
            ->setCurrency('TND')
            ->formatValue(function ($value, $entity) {
                return $entity->getQuantity() * $entity->getLivre()->getPrix();
            });

        yield FormField::addPanel('Statut');
        yield TextField::new('status', 'Statut')
            ->formatValue(function ($value, $entity) {
                $status = $entity->getCommande()->getStatus();
                $statusLabels = [
                    'pending' => 'En Attente',
                    'paid' => 'Payée',
                    'processing' => 'En Traitement',
                    'shipped' => 'Expédiée',
                    'delivered' => 'Livrée',
                    'cancelled' => 'Annulée'
                ];
                return $statusLabels[$status] ?? $status;
            });
    }
}
