<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\AdminNotificationService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;
    private AdminNotificationService $notificationService;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        AdminNotificationService $notificationService
    ) {
        $this->passwordHasher = $passwordHasher;
        $this->notificationService = $notificationService;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users');
    }

    public function configureFields(string $pageName): iterable
    {
        $passwordField = Field::new('plainPassword', 'Password')
            ->setFormType(PasswordType::class)
            ->setFormTypeOption('mapped', false)
            ->setRequired($pageName === Crud::PAGE_NEW)
            ->onlyOnForms()
            ->setHelp($pageName === Crud::PAGE_EDIT ? 'Leave blank to keep current password' : '');

        return [
            IdField::new('id')->hideOnForm(),
            EmailField::new('email'),
            TextField::new('firstName'),
            TextField::new('lastName'),
            $passwordField,
            ArrayField::new('roles'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->updatePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
        
        // Notifier les admins du nouvel utilisateur inscrit (sauf si c'est un admin)
        if ($entityInstance instanceof User && !in_array('ROLE_ADMIN', $entityInstance->getRoles())) {
            $userName = trim($entityInstance->getFirstName() . ' ' . $entityInstance->getLastName());
            if (empty($userName)) {
                $userName = $entityInstance->getEmail();
            }
            
            $this->notificationService->notifyNewUser(
                $userName,
                $entityInstance->getEmail(),
                '/admin?crudAction=edit&crudId=filters&entityId=' . $entityInstance->getId()
            );
        }
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->updatePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function updatePassword($entityInstance): void
    {
        $request = $this->getContext()->getRequest();
        $formData = $request->request->all();
        
        $plainPassword = null;
        if (isset($formData['User']['plainPassword']) && !empty($formData['User']['plainPassword'])) {
            $plainPassword = $formData['User']['plainPassword'];
        }
        
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($entityInstance, $plainPassword);
            $entityInstance->setPassword($hashedPassword);
        }
    }
}

