<?php

namespace App\Controller\Admin;

use App\Entity\Livre;
use App\Service\AdminNotificationService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Doctrine\ORM\EntityManagerInterface;

class LivreCrudController extends AbstractCrudController
{
    private AdminNotificationService $notificationService;

    public function __construct(AdminNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public static function getEntityFqcn(): string
    {
        return Livre::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('titre'),
            TextField::new('isbn'),
            ImageField::new('image')
                ->setBasePath('/uploads/books/')
                ->setUploadDir('public/uploads/books/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setRequired(false),
            IntegerField::new('nbPages'),
            DateField::new('dateEdition'),
            IntegerField::new('nbExemplaires'),
            NumberField::new('prix')->setLabel('Price (TND)'),
            AssociationField::new('editeur'),
            AssociationField::new('auteurs')->setFormTypeOptions([
                'multiple' => true,
                'by_reference' => false,
            ]),
            AssociationField::new('categories')->setFormTypeOptions([
                'multiple' => true,
                'by_reference' => false,
            ]),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        
        // Notifier les admins du nouveau livre ajouté
        if ($entityInstance instanceof Livre) {
            $this->notificationService->notifyNewBook(
                $entityInstance->getTitre(),
                '/admin?crudAction=edit&crudId=filters&entityId=' . $entityInstance->getId()
            );
            
            // Vérifier si le stock est faible
            if ($entityInstance->getNbExemplaires() <= 3 && $entityInstance->getNbExemplaires() > 0) {
                $this->notificationService->notifyLowStock(
                    $entityInstance->getTitre(),
                    $entityInstance->getNbExemplaires(),
                    '/admin?crudAction=edit&crudId=filters&entityId=' . $entityInstance->getId()
                );
            }
        }
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::updateEntity($entityManager, $entityInstance);
        
        // Vérifier si le stock est faible après mise à jour
        if ($entityInstance instanceof Livre && $entityInstance->getNbExemplaires() <= 3 && $entityInstance->getNbExemplaires() > 0) {
            $this->notificationService->notifyLowStock(
                $entityInstance->getTitre(),
                $entityInstance->getNbExemplaires(),
                '/admin?crudAction=edit&crudId=filters&entityId=' . $entityInstance->getId()
            );
        }
    }
}

