# RAPPORT DÉTAILLÉ - CONSTRUCTION DE L'APPLICATION LIBRIA AVEC SYMFONY

## 1. INITIALISATION DU PROJET SYMFONY

### Commandes de base :
```bash
# Création du projet Symfony
symfony new libria --version=6.3 --webapp

# Installation des dépendances principales
composer require doctrine orm-maker
composer require symfony/orm-pack
composer require easycorp/easyadmin-bundle
composer require symfony/mailer
composer require symfony/twig-bundle
composer require symfony/validator
composer require symfony/security-csrf
composer require symfony/translation
composer require symfony/cache
composer require dompdf/dompdf
```

## 2. GÉNÉRATION DES ENTITÉS AVEC DOCTRINE

### Entité User (authentification) :
```bash
php bin/console make:user
# Réponses :
# - Name: User
# - Identity field: email
# - Password: yes
# - API tokens: no
```

### Entités principales générées :
```bash
# Livre
php bin/console make:entity Livre
# Champs : titre, auteur, isbn, prix, nbPages, description, image, nbExemplaires, disponible

# Auteur
php bin/console make:entity Auteur
# Champs : nom, prenom, biographie, dateNaissance

# Éditeur
php bin/console make:entity Editeur
# Champs : nomEditeur, adresse, telephone

# Catégorie
php bin/console make:entity Categorie
# Champs : nom, description

# Panier et LignePanier
php bin/console make:entity Panier
php bin/console make:entity LignePanier
# Relation many-to-many entre Panier et Livre

# Commande et LigneCommande
php bin/console make:entity Commande
php bin/console make:entity LigneCommande
# Relation one-to-many entre Commande et LigneCommande

# Emprunt
php bin/console make:entity Emprunt
# Champs : dateEmprunt, dateRetourPrevu, dateRetourEffectif

# Commentaire
php bin/console make:entity Commentaire
# Champs : contenu, note, dateCreation

# Notification
php bin/console make:entity Notification
# Champs : message, type, isRead, dateCreation

# StockMovement
php bin/console make:entity StockMovement
# Champs : livre, type, quantity, reason, dateMovement
```

## 3. CONFIGURATION DES RELATIONS

### Exemple de configuration manuelle dans les entités :

**Livre.php :**
```php
#[ORM\ManyToMany(targetEntity: Auteur::class, inversedBy: 'livres')]
#[ORM\JoinTable(name: 'livre_auteur')]
private Collection $auteurs;

#[ORM\ManyToOne(inversedBy: 'livres')]
#[ORM\JoinColumn(nullable: false)]
private ?Categorie $categorie = null;

#[ORM\ManyToOne(inversedBy: 'livres')]
private ?Editeur $editeur = null;

#[ORM\OneToMany(mappedBy: 'livre', targetEntity: Commentaire::class)]
private Collection $commentaires;

#[ORM\OneToMany(mappedBy: 'livre', targetEntity: LignePanier::class)]
private Collection $lignePaniers;

#[ORM\OneToMany(mappedBy: 'livre', targetEntity: LigneCommande::class)]
private Collection $ligneCommandes;
```

**User.php :**
```php
#[ORM\OneToMany(mappedBy: 'user', targetEntity: Commande::class)]
private Collection $commandes;

#[ORM\OneToMany(mappedBy: 'user', targetEntity: Emprunt::class)]
private Collection $emprunts;

#[ORM\OneToMany(mappedBy: 'user', targetEntity: Commentaire::class)]
private Collection $commentaires;

#[ORM\OneToMany(mappedBy: 'user', targetEntity: Notification::class)]
private Collection $notifications;

#[ORM\OneToOne(inversedBy: 'user', targetEntity: Panier::class)]
private ?Panier $panier = null;
```

## 4. GÉNÉRATION DE LA BASE DE DONNÉES

```bash
# Création de la base de données
php bin/console doctrine:database:create

# Génération des migrations
php bin/console make:migration

# Exécution des migrations
php bin/console doctrine:migrations:migrate
```

## 5. INSTALLATION ET CONFIGURATION D'EASYADMIN

```bash
# Installation d'EasyAdmin
composer require easycorp/easyadmin-bundle

# Configuration dans config/packages/easyadmin.yaml
```

### Génération des CRUD Controllers :
```bash
# Dashboard principal (généré automatiquement)
php bin/console make:admin:dashboard

# CRUD pour chaque entité
php bin/console make:admin:crud Livre
php bin/console make:admin:crud Auteur
php bin/console make:admin:crud Editeur
php bin/console make:admin:crud Categorie
php bin/console make:admin:crud User
php bin/console make:admin:crud Commande
php bin/console make:admin:crud Emprunt
php bin/console make:admin:crud Commentaire
php bin/console make:admin:crud Notification
php bin/console make:admin:crud LigneCommande
```

### Configuration EasyAdmin dans DashboardController.php :
```php
class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Libria - Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Livres', 'fa fa-book', Livre::class);
        yield MenuItem::linkToCrud('Auteurs', 'fa fa-user', Auteur::class);
        yield MenuItem::linkToCrud('Éditeurs', 'fa fa-building', Editeur::class);
        yield MenuItem::linkToCrud('Catégories', 'fa fa-tag', Categorie::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-users', User::class);
        yield MenuItem::linkToCrud('Commandes', 'fa fa-shopping-cart', Commande::class);
        yield MenuItem::linkToCrud('Emprunts', 'fa fa-exchange', Emprunt::class);
        yield MenuItem::linkToCrud('Commentaires', 'fa fa-comments', Commentaire::class);
        yield MenuItem::linkToCrud('Notifications', 'fa fa-bell', Notification::class);
    }
}
```

## 6. GÉNÉRATION DES FORMULAIRES

```bash
# Génération automatique des formulaires
php bin/console make:form LivreType Livre
php bin/console make:form AuteurType Auteur
php bin/console make:form EditeurType Editeur
php bin/console make:form CategorieType Categorie
php bin/console make:form UserType User
php bin/console make:form CheckoutType Commande
php bin/console make:form CommentaireType Commentaire
```

### Exemple de formulaire généré (LivreType.php) :
```php
class LivreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('isbn', TextType::class)
            ->add('prix', MoneyType::class)
            ->add('nbPages', IntegerType::class)
            ->add('description', TextareaType::class)
            ->add('nbExemplaires', IntegerType::class)
            ->add('disponible', CheckboxType::class)
            ->add('auteur', EntityType::class, [
                'class' => Auteur::class,
                'choice_label' => 'nom',
            ])
            ->add('categorie', EntityType::class, [
                'class' => Categorie::class,
                'choice_label' => 'nom',
            ])
            ->add('editeur', EntityType::class, [
                'class' => Editeur::class,
                'choice_label' => 'nomEditeur',
            ]);
    }
}
```

## 7. SYSTÈME D'AUTHENTIFICATION

```bash
# Génération du système d'authentification
php bin/console make:auth
# Options choisies :
# - Login form authenticator
# - Always need authentication
# - Remember me feature
```

### Configuration dans security.yaml :
```yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
        main:
            lazy: true
            provider: app_user_provider
            custom_authenticator: App\Security\AppAuthenticator
            remember_me: secret
                secret: '%kernel.secret%'
                lifetime: 604800
                path: /
                domain: ~
            logout:
                path: app_logout
                target: home
    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }
        - { path: ^/, roles: ROLE_USER }
```

## 8. GÉNÉRATION DES SERVICES

### Services créés manuellement :

**AdminNotificationService.php :**
```php
namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdminNotificationService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function notifyNewBook(string $bookTitle): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setMessage("Nouveau livre ajouté : $bookTitle");
            $notification->setType('book_added');
            $notification->setUser($admin);
            $this->entityManager->persist($notification);
        }
        $this->entityManager->flush();
    }

    public function notifyLowStock(string $bookTitle, int $stock): void
    {
        $admins = $this->userRepository->findByRole('ROLE_ADMIN');
        foreach ($admins as $admin) {
            $notification = new Notification();
            $notification->setMessage("Stock faible pour : $bookTitle ($stock exemplaires)");
            $notification->setType('low_stock');
            $notification->setUser($admin);
            $this->entityManager->persist($notification);
        }
        $this->entityManager->flush();
    }
}
```

**StockService.php :**
```php
namespace App\Service;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Entity\StockMovement;
use Doctrine\ORM\EntityManagerInterface;

class StockService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function handleOrderStock(Commande $commande, bool $isCancellation = false): void
    {
        foreach ($commande->getLigneCommandes() as $ligneCommande) {
            $livre = $ligneCommande->getLivre();
            $quantity = $ligneCommande->getQuantity();

            if ($isCancellation) {
                // Annulation de commande : on remet le stock
                $this->recordMovement(
                    $livre,
                    StockMovement::TYPE_RETURN,
                    $quantity,
                    StockMovement::REASON_RETURN,
                    'Annulation commande ' . $commande->getReference(),
                    null,
                    $commande->getReference()
                );
            } else {
                // Commande validée : on déduit le stock
                $this->recordMovement(
                    $livre,
                    StockMovement::TYPE_SALE,
                    $quantity,
                    StockMovement::REASON_SALE,
                    'Vente commande ' . $commande->getReference(),
                    null,
                    $commande->getReference()
                );
            }
        }
    }

    private function recordMovement(
        Livre $livre,
        string $type,
        int $quantity,
        string $reason,
        string $description,
        ?User $user = null,
        ?string $reference = null
    ): StockMovement {
        $movement = new StockMovement();
        $movement->setLivre($livre);
        $movement->setType($type);
        $movement->setQuantity($quantity);
        $movement->setReason($reason);
        $movement->setDescription($description);
        $movement->setDateMovement(new \DateTime());
        
        if ($user) {
            $movement->setUser($user);
        }
        
        if ($reference) {
            $movement->setReference($reference);
        }

        $this->entityManager->persist($movement);
        $this->entityManager->flush();

        return $movement;
    }
}
```

**FactureService.php :**
```php
namespace App\Service;

use App\Entity\Commande;
use App\Entity\Facture;
use Dompdf\Dompdf;
use Dompdf\Options;

class FactureService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function generateFacture(Commande $commande): Facture
    {
        $facture = new Facture();
        $facture->setCommande($commande);
        $facture->setNumero('FAC-' . $commande->getReference());
        $facture->setCreatedAt(new \DateTime());
        
        // Calcul des montants
        $montantHT = $commande->getTotal();
        $tva = $montantHT * 0.2; // TVA 20%
        $montantTTC = $montantHT + $tva;
        
        $facture->setMontantHT($montantHT);
        $facture->setMontantTVA($tva);
        $facture->setMontantTTC($montantTTC);

        $this->entityManager->persist($facture);
        $this->entityManager->flush();

        return $facture;
    }

    public function getFacturePdfContent(Facture $facture): string
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        $html = $this->renderFactureHtml($facture);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function renderFactureHtml(Facture $facture): string
    {
        // Template Twig pour la facture
        return $this->twig->render('facture/facture.html.twig', [
            'facture' => $facture,
            'commande' => $facture->getCommande()
        ]);
    }
}
```

## 9. FIXTURES DE DONNÉES

```bash
# Génération des fixtures
php bin/console make:fixture AppFixtures
php bin/console make:fixture StockMovementFixtures

# Chargement des fixtures
php bin/console doctrine:fixtures:load
```

### Exemple de fixtures (AppFixtures.php) :
```php
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des catégories
        $categories = [
            'Roman' => 'Livres de fiction',
            'Science-Fiction' => 'Livres de science-fiction',
            'Policier' => 'Romans policiers',
            'Biographie' => 'Biographies et autobiographies',
            'Informatique' => 'Livres sur la programmation et l\'informatique'
        ];

        $categoryObjects = [];
        foreach ($categories as $name => $description) {
            $category = new Categorie();
            $category->setNom($name);
            $category->setDescription($description);
            $manager->persist($category);
            $categoryObjects[$name] = $category;
        }

        // Création des auteurs
        $auteurs = [
            ['Victor', 'Hugo', 'Écrivain français du XIXème siècle'],
            ['J.K.', 'Rowling', 'Auteure britannique célèbre pour Harry Potter'],
            ['Stephen', 'King', 'Maître américain de l\'horreur'],
            ['Agatha', 'Christie', 'Reine du roman policier'],
            ['Isaac', 'Asimov', 'Pionnier de la science-fiction']
        ];

        $auteurObjects = [];
        foreach ($auteurs as [$prenom, $nom, $bio]) {
            $auteur = new Auteur();
            $auteur->setPrenom($prenom);
            $auteur->setNom($nom);
            $auteur->setBiographie($bio);
            $auteur->setDateNaissance(new \DateTime('1900-01-01'));
            $manager->persist($auteur);
            $auteurObjects[$prenom . ' ' . $nom] = $auteur;
        }

        // Création des livres
        $livres = [
            ['Les Misérables', '978-2-07-041947-8', 24.99, 1232, 'Roman classique français', 50, true, 'Victor Hugo', 'Roman'],
            ['Harry Potter à l\'école des sorciers', '978-2-07-054427-1', 19.99, 309, 'Premier tome de la célèbre saga', 100, true, 'J.K. Rowling', 'Science-Fiction'],
            ['Ça', '978-2-07-041947-8', 22.99, 1105, 'Roman d\'horreur épique', 30, true, 'Stephen King', 'Policier'],
            ['Meurtre de l\'Orient Express', '978-2-253-12345-6', 18.99, 256, 'Enquête d\'Hercule Poirot', 40, true, 'Agatha Christie', 'Policier'],
            ['Fondation', '978-2-207-23456-7', 21.99, 244, 'Cycle de science-fiction', 25, true, 'Isaac Asimov', 'Science-Fiction']
        ];

        foreach ($livres as [$titre, $isbn, $prix, $nbPages, $description, $nbExemplaires, $disponible, $auteurNom, $categorieNom]) {
            $livre = new Livre();
            $livre->setTitre($titre);
            $livre->setIsbn($isbn);
            $livre->setPrix($prix);
            $livre->setNbPages($nbPages);
            $livre->setDescription($description);
            $livre->setNbExemplaires($nbExemplaires);
            $livre->setDisponible($disponible);
            $livre->addAuteur($auteurObjects[$auteurNom]);
            $livre->setCategorie($categoryObjects[$categorieNom]);
            $manager->persist($livre);
        }

        // Création de l'administrateur
        $admin = new User();
        $admin->setEmail('admin@libria.com');
        $admin->setPassword('$2y$13$92cUNdrEzP4z7eJ9I8Q6A.9N2Y3Q4R5S6T7U8V9W0X1Y2Z3');
        $admin->setFirstName('Admin');
        $admin->setLastName('Libria');
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $manager->flush();
    }
}
```

## 10. GÉNÉRATION DES CONTRÔLLERS PERSONNALISÉS

```bash
# Controllers principaux
php bin/console make:controller BooksController
php bin/console make:controller CartController
php bin/console make:controller CheckoutController
php bin/console make:controller EmpruntController
php bin/console make:controller NotificationController
php bin/console make:controller OrderController
php bin/console make:controller CommentController
```

### Exemple de controller (BooksController.php) :
```php
#[Route('/books')]
class BooksController extends AbstractController
{
    #[Route('/', name: 'books')]
    public function index(Request $request, LivreRepository $livreRepository): Response
    {
        $search = $request->query->get('search');
        $categorie = $request->query->get('categorie');
        $sortBy = $request->query->get('sortBy', 'titre');
        $order = $request->query->get('order', 'asc');
        
        $livres = $livreRepository->findByFilters($search, $categorie, $sortBy, $order);
        $categories = $this->getDoctrine()->getRepository(Categorie::class)->findAll();
        
        return $this->render('books/index.html.twig', [
            'livres' => $livres,
            'categories' => $categories,
            'currentSearch' => $search,
            'currentCategorie' => $categorie,
            'currentSortBy' => $sortBy,
            'currentOrder' => $order,
        ]);
    }

    #[Route('/{id}', name: 'book_show')]
    public function show(Livre $livre, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('error', 'Vous devez être connecté pour laisser un commentaire.');
                return $this->redirectToRoute('login');
            }
            
            if ($this->isGranted('ROLE_ADMIN')) {
                $this->addFlash('error', 'Les administrateurs ne peuvent pas laisser de commentaires.');
                return $this->redirectToRoute('book_show', ['id' => $livre->getId()]);
            }

            $commentaire->setLivre($livre);
            $commentaire->setUser($this->getUser());
            $commentaire->setDateCreation(new \DateTime());
            
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Votre commentaire a été ajouté avec succès.');
            return $this->redirectToRoute('book_show', ['id' => $livre->getId()]);
        }

        return $this->render('books/show.html.twig', [
            'livre' => $livre,
            'commentaires' => $livre->getCommentaires(),
            'form' => $form->createView(),
        ]);
    }
}
```

## 11. SYSTÈME DE TRADUCTION

```bash
# Installation du composant de traduction
composer require symfony/translation

# Création des fichiers de traduction
mkdir -p translations/
# fichiers : messages.fr.yaml, messages.en.yaml
```

### Configuration dans config/packages/translation.yaml :
```yaml
framework:
    default_locale: fr
    translator:
        default_path: '%kernel.project_dir%/translations'
        fallbacks:
            - fr
```

### Exemple de fichiers de traduction :
**messages.fr.yaml :**
```yaml
book.title: Titre
book.author: Auteur
book.price: Prix
book.description: Description
cart.add: Ajouter au panier
cart.checkout: Commander
```

**messages.en.yaml :**
```yaml
book.title: Title
book.author: Author
book.price: Price
book.description: Description
cart.add: Add to cart
cart.checkout: Checkout
```

## 12. GÉNÉRATION DES FACTURES PDF

### Installation :
```bash
composer require dompdf/dompdf
```

### Template de facture (templates/facture/facture.html.twig) :
```html
<!DOCTYPE html>
<html>
<head>
    <title>Facture {{ facture.numero }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .invoice-info { margin-bottom: 20px; }
        .invoice-number { font-weight: bold; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LIBRIA</h1>
        <p>Bibliothèque en ligne</p>
    </div>

    <div class="invoice-info">
        <div class="invoice-number">FACTURE N° {{ facture.numero }}</div>
        <div>Date: {{ facture.createdAt|date('d/m/Y') }}</div>
        <div>Client: {{ commande.user.firstName }} {{ commande.user.lastName }}</div>
        <div>Email: {{ commande.user.email }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Livre</th>
                <th>Quantité</th>
                <th>Prix unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            {% for ligne in commande.ligneCommandes %}
            <tr>
                <td>{{ ligne.livre.titre }}</td>
                <td>{{ ligne.quantity }}</td>
                <td>{{ ligne.price }} TND</td>
                <td>{{ ligne.total }} TND</td>
            </tr>
            {% endfor %}
        </tbody>
    </table>

    <table>
        <tr>
            <td>Total HT:</td>
            <td align="right">{{ facture.getFormattedMontantHT() }}</td>
        </tr>
        <tr>
            <td>TVA (20%):</td>
            <td align="right">{{ facture.getFormattedMontantTVA() }}</td>
        </tr>
        <tr>
            <td class="total">Total TTC:</td>
            <td align="right">{{ facture.getFormattedMontantTTC() }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>Merci pour votre achat !</p>
        <p>La présente facture est conservée sur support numérique pendant une durée de dix ans.</p>
    </div>
</body>
</html>
```

## 13. VALIDATION ET SÉCURITÉ

### Installation des validations :
```bash
composer require symfony/validator
composer require symfony/security-csrf
```

### Exemple de validation dans les entités :
```php
use Symfony\Component\Validator\Constraints as Assert;

class Livre
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le titre doit faire au moins 3 caractères')]
    private ?string $titre = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: 'L\'ISBN ne peut pas être vide')]
    #[Assert\Isbn(message: 'L\'ISBN n\'est pas valide')]
    private ?string $isbn = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Le prix ne peut pas être vide')]
    #[Assert\Positive(message: 'Le prix doit être positif')]
    private ?string $prix = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le nombre de pages ne peut pas être vide')]
    #[Assert\Positive(message: 'Le nombre de pages doit être positif')]
    private ?int $nbPages = null;
}
```

## 14. MAILING SYSTEM

### Configuration du mailer :
```bash
composer require symfony/mailer
```

### Configuration dans config/packages/mailer.yaml :
```yaml
framework:
    mailer:
        dsn: '%env(MAILER_DSN)%'
```

### Exemple d'utilisation :
```php
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NotificationService
{
    public function __construct(
        private MailerInterface $mailer
    ) {}

    public function sendOrderConfirmation(Commande $commande): void
    {
        $email = (new Email())
            ->from('noreply@libria.com')
            ->to($commande->getUser()->getEmail())
            ->subject('Confirmation de votre commande ' . $commande->getReference())
            ->html($this->renderView('emails/order_confirmation.html.twig', [
                'commande' => $commande
            ]));

        $this->mailer->send($email);
    }
}
```

## 15. COMMANDES PERSONNALISÉES

```bash
# Création de commandes
php bin/console make:command CreateAdminCommand
php bin/console make:command DownloadBookCoversCommand
```

### Exemple de commande (CreateAdminCommand.php) :
```php
#[AsCommand('app:create-admin')]
class CreateAdminCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Crée un utilisateur administrateur')
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'administrateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe de l\'administrateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('Administrateur créé avec succès !');

        return Command::SUCCESS;
    }
}
```

## 16. OPTIMISATIONS ET PERFORMANCE

```bash
# Cache
composer require symfony/cache

# Asset management
composer install --no-dev --optimize-autoloader
```

### Configuration du cache :
```yaml
# config/packages/cache.yaml
framework:
    cache:
        app: '%kernel.cache_dir%/app.cache'
        system: '%kernel.cache_dir%/system.cache'
```

## 17. CONFIGURATION AVANCÉE

### config/services.yaml :
```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\Service\:
        resource: '../src/Service/'
        exclude: '../src/Service/{DependencyInjection,EntityListener,}'

    App\Controller\:
        resource: '../src/Controller/'
        tags: ['controller.service_arguments']

    App\AdminNotificationService:
        arguments:
            $userRepository: '@App\Repository\UserRepository'
            $entityManager: '@Doctrine\ORM\EntityManagerInterface'
```

## 18. DOCKER ET DÉPLOIEMENT

### Dockerfile :
```dockerfile
FROM php:8.1-fpm
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    && docker-php-ext-install pdo_mysql zip gd
RUN pecl install redis && docker-php-ext-enable redis
WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --optimize-autoloader
EXPOSE 9000
CMD ["php-fpm"]
```

### docker-compose.yml :
```yaml
version: '3.8'
services:
    php:
        build: .
        ports:
            - "9000:9000"
        volumes:
            - .:/var/www/html
        depends_on:
            - mysql
            - redis
    
    mysql:
        image: mysql:8.0
        environment:
            MYSQL_ROOT_PASSWORD: root
            MYSQL_DATABASE: libria
        ports:
            - "3306:3306"
        volumes:
            - mysql_data:/var/lib/mysql
    
    redis:
        image: redis:7-alpine
        ports:
            - "6379:6379"

volumes:
    mysql_data:
```

---

## RÉSUMÉ DES OUTILS SYMFONY UTILISÉS

1. **make:entity** - Génération des entités avec leurs attributs et relations
2. **make:form** - Génération automatique des formulaires basés sur les entités
3. **make:controller** - Génération des contrôleurs avec routes et méthodes de base
4. **make:user** - Génération du système d'authentification utilisateur
5. **make:auth** - Configuration complète de l'authentification avec login/logout
6. **make:admin:dashboard** - Dashboard EasyAdmin pour l'administration
7. **make:admin:crud** - CRUD automatiques pour chaque entité dans EasyAdmin
8. **make:migration** - Génération des migrations de base de données
9. **make:fixture** - Fixtures pour peupler la base de données
10. **make:command** - Commandes console personnalisées

## ARCHITECTURE GLOBALE

- **Entités** : 15 entités principales (User, Livre, Auteur, Editeur, Categorie, Panier, Commande, Emprunt, Commentaire, Notification, etc.)
- **Controllers** : 8 contrôleurs principaux + CRUD EasyAdmin
- **Services** : 3 services métiers (AdminNotificationService, StockService, FactureService)
- **Templates** : Templates Twig pour tous les écrans de l'application
- **Forms** : Formulaires automatiques et personnalisés
- **Security** : Authentification complète avec rôles USER/ADMIN
- **Admin** : Interface d'administration EasyAdmin complète

Chaque élément de l'application a été généré soit automatiquement via les commandes Symfony, soit manuellement en suivant les meilleures pratiques du framework Symfony 6.3.
