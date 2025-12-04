<?php

namespace App\DataFixtures;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Commentaire;
use App\Entity\Coupon;
use App\Entity\Editeur;
use App\Entity\Emprunt;
use App\Entity\Favori;
use App\Entity\LigneCommande;
use App\Entity\Livre;
use App\Entity\Notification;
use App\Entity\StockMovement;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Créer beaucoup d'utilisateurs
        $usersData = [
            ['email' => 'admin@libria.com', 'firstName' => 'Admin', 'lastName' => 'User', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']],
            ['email' => 'jean.dupont@libria.com', 'firstName' => 'Jean', 'lastName' => 'Dupont', 'roles' => ['ROLE_USER']],
            ['email' => 'marie.martin@libria.com', 'firstName' => 'Marie', 'lastName' => 'Martin', 'roles' => ['ROLE_USER']],
            ['email' => 'pierre.bernard@libria.com', 'firstName' => 'Pierre', 'lastName' => 'Bernard', 'roles' => ['ROLE_USER']],
            ['email' => 'sophie.durand@libria.com', 'firstName' => 'Sophie', 'lastName' => 'Durand', 'roles' => ['ROLE_USER']],
            ['email' => 'lucas.moreau@libria.com', 'firstName' => 'Lucas', 'lastName' => 'Moreau', 'roles' => ['ROLE_USER']],
            ['email' => 'emma.lefebvre@libria.com', 'firstName' => 'Emma', 'lastName' => 'Lefebvre', 'roles' => ['ROLE_USER']],
            ['email' => 'thomas.simon@libria.com', 'firstName' => 'Thomas', 'lastName' => 'Simon', 'roles' => ['ROLE_USER']],
            ['email' => 'laura.roux@libria.com', 'firstName' => 'Laura', 'lastName' => 'Roux', 'roles' => ['ROLE_USER']],
            ['email' => 'antoine.vincent@libria.com', 'firstName' => 'Antoine', 'lastName' => 'Vincent', 'roles' => ['ROLE_USER']],
            ['email' => 'camille.fournier@libria.com', 'firstName' => 'Camille', 'lastName' => 'Fournier', 'roles' => ['ROLE_USER']],
            ['email' => 'hugo.girard@libria.com', 'firstName' => 'Hugo', 'lastName' => 'Girard', 'roles' => ['ROLE_USER']],
            ['email' => 'lea.leroy@libria.com', 'firstName' => 'Léa', 'lastName' => 'Leroy', 'roles' => ['ROLE_USER']],
            ['email' => 'maxime.roy@libria.com', 'firstName' => 'Maxime', 'lastName' => 'Roy', 'roles' => ['ROLE_USER']],
            ['email' => 'chloe.blanc@libria.com', 'firstName' => 'Chloé', 'lastName' => 'Blanc', 'roles' => ['ROLE_USER']],
            ['email' => 'alexandre.noir@libria.com', 'firstName' => 'Alexandre', 'lastName' => 'Noir', 'roles' => ['ROLE_USER']],
            ['email' => 'julie.rouge@libria.com', 'firstName' => 'Julie', 'lastName' => 'Rouge', 'roles' => ['ROLE_USER']],
            ['email' => 'nicolas.vert@libria.com', 'firstName' => 'Nicolas', 'lastName' => 'Vert', 'roles' => ['ROLE_USER']],
            ['email' => 'sarah.bleu@libria.com', 'firstName' => 'Sarah', 'lastName' => 'Bleu', 'roles' => ['ROLE_USER']],
            ['email' => 'quentin.jaune@libria.com', 'firstName' => 'Quentin', 'lastName' => 'Jaune', 'roles' => ['ROLE_USER']],
            ['email' => 'lisa.rose@libria.com', 'firstName' => 'Lisa', 'lastName' => 'Rose', 'roles' => ['ROLE_USER']],
            ['email' => 'romain.violet@libria.com', 'firstName' => 'Romain', 'lastName' => 'Violet', 'roles' => ['ROLE_USER']],
            ['email' => 'manon.orange@libria.com', 'firstName' => 'Manon', 'lastName' => 'Orange', 'roles' => ['ROLE_USER']],
            ['email' => 'clement.marron@libria.com', 'firstName' => 'Clément', 'lastName' => 'Marron', 'roles' => ['ROLE_USER']],
            ['email' => 'elise.gris@libria.com', 'firstName' => 'Élise', 'lastName' => 'Gris', 'roles' => ['ROLE_USER']],
        ];

        $users = [];
        foreach ($usersData as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setRoles($userData['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'test123'));
            $manager->persist($user);
            $users[] = $user;
        }

        // Créer beaucoup d'éditeurs
        $editeursData = [
            ['nom' => 'Gallimard', 'pays' => 'France', 'adresse' => '5 rue Sébastien-Bottin, 75007 Paris', 'telephone' => '+33 1 49 54 42 00'],
            ['nom' => 'Flammarion', 'pays' => 'France', 'adresse' => '87 quai Panhard et Levassor, 75013 Paris', 'telephone' => '+33 1 40 51 31 00'],
            ['nom' => 'Éditions du Seuil', 'pays' => 'France', 'adresse' => '27 rue Jacob, 75006 Paris', 'telephone' => '+33 1 40 46 50 50'],
            ['nom' => 'Hachette Livre', 'pays' => 'France', 'adresse' => '43 quai de Grenelle, 75015 Paris', 'telephone' => '+33 1 43 92 30 00'],
            ['nom' => 'Albin Michel', 'pays' => 'France', 'adresse' => '22 rue Huyghens, 75014 Paris', 'telephone' => '+33 1 42 79 10 00'],
            ['nom' => 'Robert Laffont', 'pays' => 'France', 'adresse' => '24 avenue Marceau, 75008 Paris', 'telephone' => '+33 1 53 67 14 00'],
            ['nom' => 'Actes Sud', 'pays' => 'France', 'adresse' => '18 rue Séguier, 75006 Paris', 'telephone' => '+33 1 55 42 63 00'],
            ['nom' => 'Grasset', 'pays' => 'France', 'adresse' => '61 rue des Saints-Pères, 75006 Paris', 'telephone' => '+33 1 44 39 22 00'],
            ['nom' => 'Stock', 'pays' => 'France', 'adresse' => '12 avenue d\'Iéna, 75116 Paris', 'telephone' => '+33 1 44 43 70 00'],
            ['nom' => 'Fayard', 'pays' => 'France', 'adresse' => '13 rue du Montparnasse, 75006 Paris', 'telephone' => '+33 1 45 49 82 00'],
            ['nom' => 'JC Lattès', 'pays' => 'France', 'adresse' => '17 rue Jacob, 75006 Paris', 'telephone' => '+33 1 44 41 74 00'],
            ['nom' => 'Denoël', 'pays' => 'France', 'adresse' => '9 rue du Cherche-Midi, 75006 Paris', 'telephone' => '+33 1 42 22 33 11'],
            ['nom' => 'Pocket', 'pays' => 'France', 'adresse' => '12 avenue d\'Iéna, 75116 Paris', 'telephone' => '+33 1 44 43 70 00'],
            ['nom' => 'J\'ai Lu', 'pays' => 'France', 'adresse' => '87 quai Panhard et Levassor, 75013 Paris', 'telephone' => '+33 1 40 51 31 00'],
            ['nom' => 'Folio', 'pays' => 'France', 'adresse' => '5 rue Sébastien-Bottin, 75007 Paris', 'telephone' => '+33 1 49 54 42 00'],
        ];

        $editeurs = [];
        foreach ($editeursData as $edData) {
            $editeur = new Editeur();
            $editeur->setNomEditeur($edData['nom']);
            $editeur->setPays($edData['pays']);
            $editeur->setAdresse($edData['adresse']);
            $editeur->setTelephone($edData['telephone']);
            $manager->persist($editeur);
            $editeurs[] = $editeur;
        }

        // Créer beaucoup de catégories
        $categoriesData = [
            ['designation' => 'Roman', 'description' => 'Œuvres de fiction narrative'],
            ['designation' => 'Science-Fiction', 'description' => 'Littérature de science-fiction'],
            ['designation' => 'Fantasy', 'description' => 'Littérature fantastique'],
            ['designation' => 'Policier', 'description' => 'Romans policiers et thrillers'],
            ['designation' => 'Biographie', 'description' => 'Récits de vie'],
            ['designation' => 'Histoire', 'description' => 'Ouvrages historiques'],
            ['designation' => 'Philosophie', 'description' => 'Ouvrages philosophiques'],
            ['designation' => 'Poésie', 'description' => 'Recueils de poèmes'],
            ['designation' => 'Théâtre', 'description' => 'Pièces de théâtre'],
            ['designation' => 'Essai', 'description' => 'Essais et réflexions'],
            ['designation' => 'Jeunesse', 'description' => 'Littérature jeunesse'],
            ['designation' => 'Manga', 'description' => 'Mangas et bandes dessinées'],
            ['designation' => 'Développement Personnel', 'description' => 'Livres de développement personnel'],
        ];

        $categories = [];
        foreach ($categoriesData as $catData) {
            $categorie = new Categorie();
            $categorie->setDesignation($catData['designation']);
            $categorie->setDescription($catData['description']);
            $manager->persist($categorie);
            $categories[] = $categorie;
        }

        // Créer beaucoup d'auteurs
        $auteursData = [
            ['prenom' => 'Victor', 'nom' => 'Hugo', 'biographie' => 'Écrivain, poète et homme politique français, considéré comme l\'un des plus importants écrivains de langue française.'],
            ['prenom' => 'Gustave', 'nom' => 'Flaubert', 'biographie' => 'Écrivain français, auteur notamment de Madame Bovary.'],
            ['prenom' => 'Marcel', 'nom' => 'Proust', 'biographie' => 'Écrivain français, auteur de À la recherche du temps perdu.'],
            ['prenom' => 'Albert', 'nom' => 'Camus', 'biographie' => 'Écrivain, philosophe et journaliste français, prix Nobel de littérature en 1957.'],
            ['prenom' => 'Jean-Paul', 'nom' => 'Sartre', 'biographie' => 'Philosophe, écrivain et dramaturge français, prix Nobel de littérature en 1964.'],
            ['prenom' => 'Simone', 'nom' => 'de Beauvoir', 'biographie' => 'Philosophe, romancière et essayiste française, figure du féminisme.'],
            ['prenom' => 'Jules', 'nom' => 'Verne', 'biographie' => 'Écrivain français, pionnier de la science-fiction.'],
            ['prenom' => 'Émile', 'nom' => 'Zola', 'biographie' => 'Écrivain et journaliste français, chef de file du naturalisme.'],
            ['prenom' => 'Antoine', 'nom' => 'de Saint-Exupéry', 'biographie' => 'Écrivain, poète et aviateur français, auteur du Petit Prince.'],
            ['prenom' => 'Marguerite', 'nom' => 'Duras', 'biographie' => 'Écrivaine, dramaturge et cinéaste française.'],
            ['prenom' => 'Franz', 'nom' => 'Kafka', 'biographie' => 'Écrivain pragois de langue allemande, auteur de La Métamorphose.'],
            ['prenom' => 'George', 'nom' => 'Orwell', 'biographie' => 'Écrivain et journaliste britannique, auteur de 1984 et La Ferme des animaux.'],
            ['prenom' => 'Ernest', 'nom' => 'Hemingway', 'biographie' => 'Écrivain et journaliste américain, prix Nobel de littérature en 1954.'],
            ['prenom' => 'Fyodor', 'nom' => 'Dostoevsky', 'biographie' => 'Écrivain russe, auteur de Crime et Châtiment et Les Frères Karamazov.'],
            ['prenom' => 'Leo', 'nom' => 'Tolstoy', 'biographie' => 'Écrivain russe, auteur de Guerre et Paix et Anna Karénine.'],
            ['prenom' => 'Jane', 'nom' => 'Austen', 'biographie' => 'Romancière anglaise, auteure d\'Orgueil et Préjugés.'],
            ['prenom' => 'Charles', 'nom' => 'Dickens', 'biographie' => 'Écrivain britannique, auteur de Oliver Twist et David Copperfield.'],
            ['prenom' => 'Mark', 'nom' => 'Twain', 'biographie' => 'Écrivain et humoriste américain, auteur des Aventures de Tom Sawyer.'],
            ['prenom' => 'Agatha', 'nom' => 'Christie', 'biographie' => 'Écrivaine britannique, reine du roman policier.'],
            ['prenom' => 'Arthur', 'nom' => 'Conan Doyle', 'biographie' => 'Écrivain écossais, créateur de Sherlock Holmes.'],
            ['prenom' => 'J.K.', 'nom' => 'Rowling', 'biographie' => 'Écrivaine britannique, auteure de la série Harry Potter.'],
            ['prenom' => 'Stephen', 'nom' => 'King', 'biographie' => 'Écrivain américain, maître du roman d\'horreur et de suspense.'],
            ['prenom' => 'Dan', 'nom' => 'Brown', 'biographie' => 'Écrivain américain, auteur du Da Vinci Code.'],
            ['prenom' => 'Paulo', 'nom' => 'Coelho', 'biographie' => 'Écrivain brésilien, auteur de L\'Alchimiste.'],
            ['prenom' => 'Haruki', 'nom' => 'Murakami', 'biographie' => 'Écrivain japonais, auteur de Kafka sur le rivage.'],
            ['prenom' => 'Isabel', 'nom' => 'Allende', 'biographie' => 'Écrivaine chilienne, auteure de La Maison aux esprits.'],
            ['prenom' => 'Umberto', 'nom' => 'Eco', 'biographie' => 'Écrivain et philosophe italien, auteur du Nom de la rose.'],
            ['prenom' => 'Milan', 'nom' => 'Kundera', 'biographie' => 'Écrivain tchèque naturalisé français, auteur de L\'Insoutenable Légèreté de l\'être.'],
            ['prenom' => 'Gabriel', 'nom' => 'García Márquez', 'biographie' => 'Écrivain colombien, prix Nobel de littérature, auteur de Cent ans de solitude.'],
            ['prenom' => 'Toni', 'nom' => 'Morrison', 'biographie' => 'Écrivaine américaine, prix Nobel de littérature en 1993.'],
        ];

        $auteurs = [];
        foreach ($auteursData as $autData) {
            $auteur = new Auteur();
            $auteur->setPrenom($autData['prenom']);
            $auteur->setNom($autData['nom']);
            $auteur->setBiographie($autData['biographie']);
            $manager->persist($auteur);
            $auteurs[] = $auteur;
        }

        // Créer beaucoup de livres avec descriptions
        $livresData = [
            [
                'titre' => 'Les Misérables',
                'isbn' => '978-2-07-036789-0',
                'nbPages' => 1488,
                'dateEdition' => new \DateTime('1862-01-01'),
                'nbExemplaires' => 15,
                'prix' => '25.90',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[0]],
                'categories' => [$categories[0], $categories[5]],
                'image' => 'les-miserables.jpg',
                'description' => 'Les Misérables est un roman de Victor Hugo paru en 1862. L\'histoire se déroule en France au début du XIXe siècle et suit la vie de Jean Valjean, un ancien forçat qui cherche la rédemption. Le roman explore des thèmes de justice, de misère sociale, et d\'amour à travers plusieurs personnages emblématiques comme Fantine, Cosette, et Javert.'
            ],
            [
                'titre' => 'Madame Bovary',
                'isbn' => '978-2-08-070789-1',
                'nbPages' => 432,
                'dateEdition' => new \DateTime('1857-01-01'),
                'nbExemplaires' => 12,
                'prix' => '12.50',
                'editeur' => $editeurs[1],
                'auteurs' => [$auteurs[1]],
                'categories' => [$categories[0]],
                'image' => 'madame-bovary.jpg',
                'description' => 'Madame Bovary est un roman de Gustave Flaubert paru en 1857. L\'œuvre raconte l\'histoire d\'Emma Bovary, une femme mariée qui sombre dans l\'ennui et cherche à échapper à sa vie provinciale médiocre à travers des aventures amoureuses et des dépenses excessives. Le roman est considéré comme un chef-d\'œuvre du réalisme littéraire.'
            ],
            [
                'titre' => 'À la recherche du temps perdu',
                'isbn' => '978-2-07-011190-5',
                'nbPages' => 2400,
                'dateEdition' => new \DateTime('1913-01-01'),
                'nbExemplaires' => 8,
                'prix' => '45.00',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[2]],
                'categories' => [$categories[0]],
                'image' => 'recherche-temps-perdu.jpg',
                'description' => 'À la recherche du temps perdu est une œuvre monumentale de Marcel Proust publiée entre 1913 et 1927. Cette suite romanesque en sept volumes explore la mémoire involontaire, le temps, l\'art, et la société française de la Belle Époque à travers les yeux du narrateur.'
            ],
            [
                'titre' => 'L\'Étranger',
                'isbn' => '978-2-07-036002-0',
                'nbPages' => 186,
                'dateEdition' => new \DateTime('1942-01-01'),
                'nbExemplaires' => 20,
                'prix' => '8.90',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[3]],
                'categories' => [$categories[0], $categories[6]],
                'image' => 'letranger.jpg',
                'description' => 'L\'Étranger est un roman d\'Albert Camus publié en 1942. L\'histoire est racontée à la première personne par Meursault, un employé de bureau algérois qui commet un meurtre apparemment sans motif. Le roman explore des thèmes d\'absurdité, d\'indifférence, et de la condition humaine.'
            ],
            [
                'titre' => 'La Peste',
                'isbn' => '978-2-07-036003-7',
                'nbPages' => 320,
                'dateEdition' => new \DateTime('1947-01-01'),
                'nbExemplaires' => 18,
                'prix' => '10.50',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[3]],
                'categories' => [$categories[0]],
                'image' => 'la-peste.jpg',
                'description' => 'La Peste est un roman d\'Albert Camus publié en 1947. L\'histoire se déroule dans la ville d\'Oran en Algérie, frappée par une épidémie de peste. Le roman suit le docteur Rieux et d\'autres personnages dans leur lutte contre la maladie, explorant des thèmes de solidarité, de résistance, et de sens face à l\'absurdité.'
            ],
            [
                'titre' => 'L\'Être et le Néant',
                'isbn' => '978-2-07-029388-5',
                'nbPages' => 722,
                'dateEdition' => new \DateTime('1943-01-01'),
                'nbExemplaires' => 10,
                'prix' => '28.00',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[4]],
                'categories' => [$categories[6]],
                'image' => 'etre-neant.jpg',
                'description' => 'L\'Être et le Néant est un essai philosophique de Jean-Paul Sartre publié en 1943. Cette œuvre majeure de l\'existentialisme explore les concepts de liberté, de responsabilité, de mauvaise foi, et de la relation entre l\'être-en-soi et l\'être-pour-soi.'
            ],
            [
                'titre' => 'Le Deuxième Sexe',
                'isbn' => '978-2-07-032351-2',
                'nbPages' => 960,
                'dateEdition' => new \DateTime('1949-01-01'),
                'nbExemplaires' => 14,
                'prix' => '22.00',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[5]],
                'categories' => [$categories[6], $categories[9]],
                'image' => 'deuxieme-sexe.jpg',
                'description' => 'Le Deuxième Sexe est un essai de Simone de Beauvoir publié en 1949. Cette œuvre fondatrice du féminisme moderne examine la condition des femmes à travers l\'histoire, la littérature, la psychanalyse, et la philosophie, et pose la question : "Qu\'est-ce qu\'une femme ?"'
            ],
            [
                'titre' => 'Vingt mille lieues sous les mers',
                'isbn' => '978-2-08-070789-2',
                'nbPages' => 512,
                'dateEdition' => new \DateTime('1870-01-01'),
                'nbExemplaires' => 16,
                'prix' => '14.90',
                'editeur' => $editeurs[1],
                'auteurs' => [$auteurs[6]],
                'categories' => [$categories[1]],
                'image' => 'vingt-mille-lieues.jpg',
                'description' => 'Vingt mille lieues sous les mers est un roman de Jules Verne publié en 1870. L\'histoire suit les aventures du professeur Aronnax, de son domestique Conseil, et du harponneur Ned Land à bord du Nautilus, le sous-marin du mystérieux capitaine Nemo.'
            ],
            [
                'titre' => 'Le Tour du monde en quatre-vingts jours',
                'isbn' => '978-2-08-070789-3',
                'nbPages' => 288,
                'dateEdition' => new \DateTime('1873-01-01'),
                'nbExemplaires' => 22,
                'prix' => '11.50',
                'editeur' => $editeurs[1],
                'auteurs' => [$auteurs[6]],
                'categories' => [$categories[1]],
                'image' => 'tour-du-monde.jpg',
                'description' => 'Le Tour du monde en quatre-vingts jours est un roman de Jules Verne publié en 1873. L\'histoire suit Phileas Fogg, un gentleman anglais qui parie qu\'il peut faire le tour du monde en 80 jours, accompagné de son valet français Passepartout.'
            ],
            [
                'titre' => 'Germinal',
                'isbn' => '978-2-07-036789-1',
                'nbPages' => 592,
                'dateEdition' => new \DateTime('1885-01-01'),
                'nbExemplaires' => 13,
                'prix' => '15.90',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[7]],
                'categories' => [$categories[0], $categories[5]],
                'image' => 'germinal.jpg',
                'description' => 'Germinal est un roman d\'Émile Zola publié en 1885. Treizième volume de la série Les Rougon-Macquart, le roman décrit la vie des mineurs dans le nord de la France au XIXe siècle et leur lutte pour de meilleures conditions de travail. C\'est une œuvre majeure du naturalisme.'
            ],
            [
                'titre' => 'Le Petit Prince',
                'isbn' => '978-2-07-061275-8',
                'nbPages' => 96,
                'dateEdition' => new \DateTime('1943-01-01'),
                'nbExemplaires' => 30,
                'prix' => '7.90',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[8]],
                'categories' => [$categories[0], $categories[10]],
                'image' => 'petit-prince.jpg',
                'description' => 'Le Petit Prince est une œuvre d\'Antoine de Saint-Exupéry publiée en 1943. Ce conte philosophique raconte l\'histoire d\'un jeune prince qui voyage de planète en planète et rencontre différents personnages, explorant des thèmes d\'amitié, d\'amour, de perte, et de sens de la vie.'
            ],
            [
                'titre' => 'L\'Amant',
                'isbn' => '978-2-02-006984-9',
                'nbPages' => 160,
                'dateEdition' => new \DateTime('1984-01-01'),
                'nbExemplaires' => 11,
                'prix' => '9.50',
                'editeur' => $editeurs[2],
                'auteurs' => [$auteurs[9]],
                'categories' => [$categories[0]],
                'image' => 'lamant.jpg',
                'description' => 'L\'Amant est un roman autobiographique de Marguerite Duras publié en 1984. L\'œuvre raconte l\'histoire d\'amour entre une jeune fille française de 15 ans et un riche Chinois de 27 ans dans l\'Indochine coloniale des années 1930.'
            ],
            [
                'titre' => '1984',
                'isbn' => '978-2-07-036822-1',
                'nbPages' => 438,
                'dateEdition' => new \DateTime('1949-06-08'),
                'nbExemplaires' => 25,
                'prix' => '12.90',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[11]],
                'categories' => [$categories[1], $categories[6]],
                'image' => null,
                'description' => '1984 est un roman dystopique de George Orwell publié en 1949. L\'histoire se déroule dans un État totalitaire où le Parti contrôle tous les aspects de la vie. Winston Smith, le protagoniste, travaille au ministère de la Vérité et commence à remettre en question le système. Le roman explore des thèmes de surveillance, de manipulation, et de résistance.'
            ],
            [
                'titre' => 'La Ferme des animaux',
                'isbn' => '978-2-07-036823-8',
                'nbPages' => 128,
                'dateEdition' => new \DateTime('1945-08-17'),
                'nbExemplaires' => 20,
                'prix' => '8.50',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[11]],
                'categories' => [$categories[1], $categories[6]],
                'image' => null,
                'description' => 'La Ferme des animaux est une fable satirique de George Orwell publiée en 1945. Les animaux d\'une ferme se révoltent contre leur propriétaire humain et établissent leur propre société, mais les cochons prennent progressivement le contrôle. C\'est une allégorie de la révolution russe et de la montée du stalinisme.'
            ],
            [
                'titre' => 'Le Vieil Homme et la Mer',
                'isbn' => '978-2-07-036824-5',
                'nbPages' => 128,
                'dateEdition' => new \DateTime('1952-09-01'),
                'nbExemplaires' => 18,
                'prix' => '9.00',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[12]],
                'categories' => [$categories[0]],
                'image' => null,
                'description' => 'Le Vieil Homme et la Mer est une nouvelle d\'Ernest Hemingway publiée en 1952. L\'histoire raconte la lutte épique d\'un vieux pêcheur cubain, Santiago, avec un énorme marlin dans le golfe du Mexique. L\'œuvre a valu à Hemingway le prix Pulitzer et le prix Nobel de littérature.'
            ],
            [
                'titre' => 'Crime et Châtiment',
                'isbn' => '978-2-07-036825-2',
                'nbPages' => 671,
                'dateEdition' => new \DateTime('1866-01-01'),
                'nbExemplaires' => 12,
                'prix' => '18.50',
                'editeur' => $editeurs[3],
                'auteurs' => [$auteurs[13]],
                'categories' => [$categories[0], $categories[6]],
                'image' => null,
                'description' => 'Crime et Châtiment est un roman de Fiodor Dostoïevski publié en 1866. L\'histoire suit Raskolnikov, un étudiant pauvre de Saint-Pétersbourg qui assassine une vieille prêteuse sur gages et sa sœur, puis lutte avec sa culpabilité et sa conscience. Le roman explore des thèmes de moralité, de rédemption, et de psychologie humaine.'
            ],
            [
                'titre' => 'Guerre et Paix',
                'isbn' => '978-2-07-036826-9',
                'nbPages' => 1572,
                'dateEdition' => new \DateTime('1869-01-01'),
                'nbExemplaires' => 8,
                'prix' => '35.00',
                'editeur' => $editeurs[3],
                'auteurs' => [$auteurs[14]],
                'categories' => [$categories[0], $categories[5]],
                'image' => null,
                'description' => 'Guerre et Paix est un roman de Léon Tolstoï publié entre 1865 et 1869. Cette œuvre monumentale suit plusieurs familles aristocratiques russes pendant les guerres napoléoniennes. Le roman combine fiction historique, philosophie, et analyse psychologique, explorant des thèmes de guerre, d\'amour, de destin, et de libre arbitre.'
            ],
            [
                'titre' => 'Anna Karénine',
                'isbn' => '978-2-07-036827-6',
                'nbPages' => 864,
                'dateEdition' => new \DateTime('1877-01-01'),
                'nbExemplaires' => 15,
                'prix' => '22.00',
                'editeur' => $editeurs[3],
                'auteurs' => [$auteurs[14]],
                'categories' => [$categories[0]],
                'image' => null,
                'description' => 'Anna Karénine est un roman de Léon Tolstoï publié en 1877. L\'histoire suit Anna Karénine, une femme mariée de la haute société russe qui entame une relation adultère avec le comte Vronski. Le roman explore des thèmes d\'amour, de passion, de jalousie, et de conventions sociales dans la Russie du XIXe siècle.'
            ],
            [
                'titre' => 'Orgueil et Préjugés',
                'isbn' => '978-2-07-036828-3',
                'nbPages' => 432,
                'dateEdition' => new \DateTime('1813-01-28'),
                'nbExemplaires' => 20,
                'prix' => '13.50',
                'editeur' => $editeurs[4],
                'auteurs' => [$auteurs[15]],
                'categories' => [$categories[0]],
                'image' => null,
                'description' => 'Orgueil et Préjugés est un roman de Jane Austen publié en 1813. L\'histoire suit Elizabeth Bennet, une jeune femme intelligente et indépendante, et sa relation avec le riche et orgueilleux M. Darcy. Le roman est une satire sociale de la société anglaise de la Régence et explore des thèmes d\'amour, de classe sociale, et de mariage.'
            ],
            [
                'titre' => 'Oliver Twist',
                'isbn' => '978-2-07-036829-0',
                'nbPages' => 512,
                'dateEdition' => new \DateTime('1838-01-01'),
                'nbExemplaires' => 16,
                'prix' => '14.90',
                'editeur' => $editeurs[4],
                'auteurs' => [$auteurs[16]],
                'categories' => [$categories[0], $categories[5]],
                'image' => null,
                'description' => 'Oliver Twist est un roman de Charles Dickens publié en 1838. L\'histoire suit Oliver, un orphelin qui échappe à un orphelinat et se retrouve dans les rues de Londres, où il rencontre des criminels et des personnages marginaux. Le roman critique la société victorienne et la pauvreté.'
            ],
            [
                'titre' => 'Les Aventures de Tom Sawyer',
                'isbn' => '978-2-07-036830-6',
                'nbPages' => 288,
                'dateEdition' => new \DateTime('1876-12-01'),
                'nbExemplaires' => 22,
                'prix' => '11.00',
                'editeur' => $editeurs[4],
                'auteurs' => [$auteurs[17]],
                'categories' => [$categories[0], $categories[10]],
                'image' => null,
                'description' => 'Les Aventures de Tom Sawyer est un roman de Mark Twain publié en 1876. L\'histoire suit Tom Sawyer, un jeune garçon espiègle qui vit des aventures dans une petite ville du Mississippi au XIXe siècle. Le roman capture l\'innocence et l\'aventure de l\'enfance.'
            ],
            [
                'titre' => 'Le Meurtre de Roger Ackroyd',
                'isbn' => '978-2-07-036831-3',
                'nbPages' => 256,
                'dateEdition' => new \DateTime('1926-06-01'),
                'nbExemplaires' => 14,
                'prix' => '10.50',
                'editeur' => $editeurs[5],
                'auteurs' => [$auteurs[18]],
                'categories' => [$categories[3]],
                'image' => null,
                'description' => 'Le Meurtre de Roger Ackroyd est un roman policier d\'Agatha Christie publié en 1926. C\'est le troisième roman mettant en scène Hercule Poirot. L\'histoire se déroule dans un village anglais et présente un narrateur inhabituel qui a fait de ce roman l\'un des plus célèbres de Christie.'
            ],
            [
                'titre' => 'Les Aventures de Sherlock Holmes',
                'isbn' => '978-2-07-036832-0',
                'nbPages' => 320,
                'dateEdition' => new \DateTime('1892-10-14'),
                'nbExemplaires' => 18,
                'prix' => '12.00',
                'editeur' => $editeurs[5],
                'auteurs' => [$auteurs[19]],
                'categories' => [$categories[3]],
                'image' => null,
                'description' => 'Les Aventures de Sherlock Holmes est un recueil de nouvelles d\'Arthur Conan Doyle publié en 1892. Le livre contient douze histoires mettant en scène le détective Sherlock Holmes et son ami le docteur Watson, résolvant des mystères à Londres à la fin du XIXe siècle.'
            ],
            [
                'titre' => 'Harry Potter à l\'école des sorciers',
                'isbn' => '978-2-07-054120-4',
                'nbPages' => 320,
                'dateEdition' => new \DateTime('1997-06-26'),
                'nbExemplaires' => 35,
                'prix' => '15.90',
                'editeur' => $editeurs[6],
                'auteurs' => [$auteurs[20]],
                'categories' => [$categories[2], $categories[10]],
                'image' => null,
                'description' => 'Harry Potter à l\'école des sorciers est le premier tome de la série Harry Potter de J.K. Rowling, publié en 1997. L\'histoire suit Harry Potter, un jeune sorcier qui découvre qu\'il est célèbre dans le monde magique et commence sa première année à l\'école de sorcellerie Poudlard.'
            ],
            [
                'titre' => 'Shining',
                'isbn' => '978-2-07-036833-7',
                'nbPages' => 512,
                'dateEdition' => new \DateTime('1977-01-28'),
                'nbExemplaires' => 20,
                'prix' => '16.50',
                'editeur' => $editeurs[7],
                'auteurs' => [$auteurs[21]],
                'categories' => [$categories[3]],
                'image' => null,
                'description' => 'Shining est un roman d\'horreur de Stephen King publié en 1977. L\'histoire suit Jack Torrance, un écrivain qui devient gardien d\'un hôtel isolé dans les montagnes du Colorado avec sa famille. L\'hôtel s\'avère hanté et les événements terrifiants commencent à se produire.'
            ],
            [
                'titre' => 'Le Da Vinci Code',
                'isbn' => '978-2-226-13868-0',
                'nbPages' => 560,
                'dateEdition' => new \DateTime('2003-03-18'),
                'nbExemplaires' => 28,
                'prix' => '18.90',
                'editeur' => $editeurs[8],
                'auteurs' => [$auteurs[22]],
                'categories' => [$categories[3]],
                'image' => null,
                'description' => 'Le Da Vinci Code est un thriller de Dan Brown publié en 2003. L\'histoire suit Robert Langdon, un symbologiste de Harvard, et Sophie Neveu, une cryptologue, alors qu\'ils tentent de résoudre un meurtre au Louvre et découvrent un complot impliquant la société secrète du Prieuré de Sion.'
            ],
            [
                'titre' => 'L\'Alchimiste',
                'isbn' => '978-2-226-03180-0',
                'nbPages' => 192,
                'dateEdition' => new \DateTime('1988-01-01'),
                'nbExemplaires' => 25,
                'prix' => '9.90',
                'editeur' => $editeurs[9],
                'auteurs' => [$auteurs[23]],
                'categories' => [$categories[0], $categories[9]],
                'image' => null,
                'description' => 'L\'Alchimiste est un roman de Paulo Coelho publié en 1988. L\'histoire suit Santiago, un jeune berger andalou qui part en voyage pour trouver un trésor caché près des pyramides d\'Égypte. Le roman est une fable philosophique sur la poursuite de ses rêves et la découverte de soi.'
            ],
            [
                'titre' => 'Kafka sur le rivage',
                'isbn' => '978-2-07-031902-8',
                'nbPages' => 608,
                'dateEdition' => new \DateTime('2002-09-12'),
                'nbExemplaires' => 17,
                'prix' => '19.90',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[24]],
                'categories' => [$categories[0], $categories[2]],
                'image' => null,
                'description' => 'Kafka sur le rivage est un roman de Haruki Murakami publié en 2002. L\'histoire alterne entre deux récits : celui de Kafka Tamura, un garçon de 15 ans qui fuit sa maison, et celui de Nakata, un vieil homme qui peut parler aux chats. Le roman explore des thèmes de destin, de mémoire, et de réalité.'
            ],
            [
                'titre' => 'La Maison aux esprits',
                'isbn' => '978-2-226-01350-9',
                'nbPages' => 512,
                'dateEdition' => new \DateTime('1982-01-01'),
                'nbExemplaires' => 16,
                'prix' => '17.50',
                'editeur' => $editeurs[10],
                'auteurs' => [$auteurs[25]],
                'categories' => [$categories[0], $categories[2]],
                'image' => null,
                'description' => 'La Maison aux esprits est le premier roman d\'Isabel Allende, publié en 1982. L\'histoire suit trois générations de la famille Trueba au Chili, mêlant réalisme magique, politique, et histoire personnelle. Le roman explore des thèmes d\'amour, de perte, de politique, et de résilience.'
            ],
            [
                'titre' => 'Le Nom de la rose',
                'isbn' => '978-2-213-01784-4',
                'nbPages' => 640,
                'dateEdition' => new \DateTime('1980-01-01'),
                'nbExemplaires' => 13,
                'prix' => '20.00',
                'editeur' => $editeurs[11],
                'auteurs' => [$auteurs[26]],
                'categories' => [$categories[3], $categories[5]],
                'image' => null,
                'description' => 'Le Nom de la rose est un roman d\'Umberto Eco publié en 1980. L\'histoire se déroule dans une abbaye bénédictine italienne en 1327, où le moine franciscain Guillaume de Baskerville et son novice Adso enquêtent sur une série de meurtres mystérieux. Le roman combine mystère, histoire, et philosophie.'
            ],
            [
                'titre' => 'L\'Insoutenable Légèreté de l\'être',
                'isbn' => '978-2-07-032345-1',
                'nbPages' => 384,
                'dateEdition' => new \DateTime('1984-01-01'),
                'nbExemplaires' => 15,
                'prix' => '14.50',
                'editeur' => $editeurs[2],
                'auteurs' => [$auteurs[27]],
                'categories' => [$categories[0], $categories[6]],
                'image' => null,
                'description' => 'L\'Insoutenable Légèreté de l\'être est un roman de Milan Kundera publié en 1984. L\'histoire suit quatre personnages à Prague pendant le Printemps de Prague de 1968. Le roman explore des thèmes philosophiques sur la légèreté et le poids, l\'amour, la politique, et le sens de la vie.'
            ],
            [
                'titre' => 'Cent ans de solitude',
                'isbn' => '978-2-07-036836-4',
                'nbPages' => 448,
                'dateEdition' => new \DateTime('1967-05-30'),
                'nbExemplaires' => 19,
                'prix' => '16.00',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[28]],
                'categories' => [$categories[0], $categories[2]],
                'image' => null,
                'description' => 'Cent ans de solitude est un roman de Gabriel García Márquez publié en 1967. L\'histoire suit sept générations de la famille Buendía dans le village fictif de Macondo. Le roman est considéré comme un chef-d\'œuvre du réalisme magique et a valu à son auteur le prix Nobel de littérature.'
            ],
            [
                'titre' => 'Beloved',
                'isbn' => '978-2-07-036837-1',
                'nbPages' => 352,
                'dateEdition' => new \DateTime('1987-09-01'),
                'nbExemplaires' => 14,
                'prix' => '15.50',
                'editeur' => $editeurs[0],
                'auteurs' => [$auteurs[29]],
                'categories' => [$categories[0], $categories[5]],
                'image' => null,
                'description' => 'Beloved est un roman de Toni Morrison publié en 1987. L\'histoire se déroule après la guerre de Sécession et suit Sethe, une ancienne esclave qui est hantée par le fantôme de sa fille. Le roman explore les traumatismes de l\'esclavage et a valu à Morrison le prix Pulitzer.'
            ],
            [
                'titre' => 'La Métamorphose',
                'isbn' => '978-2-07-036838-8',
                'nbPages' => 128,
                'dateEdition' => new \DateTime('1915-10-15'),
                'nbExemplaires' => 21,
                'prix' => '8.00',
                'editeur' => $editeurs[2],
                'auteurs' => [$auteurs[10]],
                'categories' => [$categories[0], $categories[6]],
                'image' => null,
                'description' => 'La Métamorphose est une nouvelle de Franz Kafka publiée en 1915. L\'histoire commence lorsque Gregor Samsa se réveille transformé en un énorme insecte. La nouvelle explore des thèmes d\'aliénation, d\'absurdité, et de la condition humaine dans la société moderne.'
            ],
        ];

        $livres = [];
        foreach ($livresData as $livreData) {
            $livre = new Livre();
            $livre->setTitre($livreData['titre']);
            $livre->setIsbn($livreData['isbn']);
            $livre->setNbPages($livreData['nbPages']);
            $livre->setDateEdition($livreData['dateEdition']);
            $livre->setNbExemplaires($livreData['nbExemplaires']);
            $livre->setPrix($livreData['prix']);
            $livre->setEditeur($livreData['editeur']);
            if (isset($livreData['image'])) {
                $livre->setImage($livreData['image']);
            }
            if (isset($livreData['description'])) {
                $livre->setDescription($livreData['description']);
            }

            foreach ($livreData['auteurs'] as $auteur) {
                $livre->addAuteur($auteur);
            }

            foreach ($livreData['categories'] as $categorie) {
                $livre->addCategorie($categorie);
            }

            $manager->persist($livre);
            $livres[] = $livre;
        }

        // Créer des commentaires pour les livres
        $commentairesTextes = [
            'Excellent livre ! Je le recommande vivement.',
            'Une lecture captivante du début à la fin.',
            'Un chef-d\'œuvre de la littérature.',
            'Très bien écrit, l\'histoire est passionnante.',
            'Un peu long mais très intéressant.',
            'J\'ai adoré ce livre, je l\'ai lu plusieurs fois.',
            'Bonne lecture, mais pas mon préféré.',
            'Un classique à lire absolument.',
            'L\'auteur a un style unique et captivant.',
            'Histoire originale et bien racontée.',
            'Un peu décevant par rapport à mes attentes.',
            'Très bon livre, je le recommande.',
            'Lecture agréable, personnages bien développés.',
            'Un must-read pour tous les amateurs de littérature.',
            'Excellent, j\'ai hâte de lire la suite.',
        ];

        foreach ($livres as $index => $livre) {
            // Créer 2-5 commentaires par livre
            $nbCommentaires = rand(2, 5);
            for ($i = 0; $i < $nbCommentaires; $i++) {
                $commentaire = new Commentaire();
                $commentaire->setLivre($livre);
                $commentaire->setUser($users[rand(1, count($users) - 1)]); // Utilisateurs sauf admin
                $commentaire->setContenu($commentairesTextes[array_rand($commentairesTextes)]);
                $commentaire->setRating(rand(3, 5));
                $commentaire->setDateCreation(new \DateTime('-' . rand(1, 180) . ' days'));
                $manager->persist($commentaire);
            }
        }

        // Créer des favoris
        foreach ($users as $user) {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                // Chaque utilisateur a 3-8 favoris
                $nbFavoris = min(rand(3, 8), count($livres));
                if ($nbFavoris > 0) {
                    $livresFavoris = (array) array_rand($livres, $nbFavoris);
                    if (!is_array($livresFavoris)) {
                        $livresFavoris = [$livresFavoris];
                    }
                    foreach ($livresFavoris as $livreIndex) {
                        $favori = new Favori();
                        $favori->setUser($user);
                        $favori->setLivre($livres[$livreIndex]);
                        $favori->setDateAjout(new \DateTime('-' . rand(1, 90) . ' days'));
                        $manager->persist($favori);
                    }
                }
            }
        }

        // Créer des emprunts
        foreach ($users as $user) {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                // Chaque utilisateur a 0-5 emprunts
                $nbEmprunts = min(rand(0, 5), count($livres));
                if ($nbEmprunts > 0) {
                    $livresEmpruntes = (array) array_rand($livres, $nbEmprunts);
                    if (!is_array($livresEmpruntes)) {
                        $livresEmpruntes = [$livresEmpruntes];
                    }
                    foreach ($livresEmpruntes as $livreIndex) {
                        $livre = $livres[$livreIndex];
                        if ($livre->getNbExemplaires() > 0) {
                            $emprunt = new Emprunt();
                            $emprunt->setUser($user);
                            $emprunt->setLivre($livre);
                            $emprunt->setDateEmprunt(new \DateTime('-' . rand(1, 60) . ' days'));
                            $emprunt->setDateRetourPrevu((clone $emprunt->getDateEmprunt())->modify('+30 days'));
                            
                            // 70% des emprunts sont encore en cours, 30% sont retournés
                            if (rand(1, 10) <= 3) {
                                $emprunt->setStatut('retourne');
                                $emprunt->setDateRetourEffectif((clone $emprunt->getDateRetourPrevu())->modify('-' . rand(0, 10) . ' days'));
                            } else {
                                $emprunt->setStatut('en_cours');
                                $livre->setNbExemplaires($livre->getNbExemplaires() - 1);
                            }
                            
                            $manager->persist($emprunt);
                        }
                    }
                }
            }
        }

        // Créer des notifications
        $notificationsTypes = [
            ['type' => 'success', 'titre' => 'Emprunt confirmé', 'message' => 'Votre emprunt a été enregistré avec succès.'],
            ['type' => 'info', 'titre' => 'Nouveau livre disponible', 'message' => 'Un nouveau livre dans vos catégories préférées est maintenant disponible.'],
            ['type' => 'warning', 'titre' => 'Rappel d\'emprunt', 'message' => 'N\'oubliez pas de retourner votre livre emprunté.'],
            ['type' => 'emprunt', 'titre' => 'Livre emprunté', 'message' => 'Vous avez emprunté un nouveau livre.'],
        ];

        foreach ($users as $user) {
            if (!in_array('ROLE_ADMIN', $user->getRoles())) {
                // Chaque utilisateur a 2-8 notifications
                $nbNotifications = rand(2, 8);
                for ($i = 0; $i < $nbNotifications; $i++) {
                    $notifData = $notificationsTypes[array_rand($notificationsTypes)];
                    $notification = new Notification();
                    $notification->setUser($user);
                    $notification->setTitre($notifData['titre']);
                    $notification->setMessage($notifData['message']);
                    $notification->setType($notifData['type']);
                    $notification->setLu(rand(1, 10) <= 3); // 30% sont lues
                    $notification->setDateCreation(new \DateTime('-' . rand(1, 30) . ' days'));
                    if (rand(1, 2) === 1) {
                        $notification->setLien('/books');
                    }
                    $manager->persist($notification);
                }
            }
        }

        // Créer des coupons de test
        $couponsData = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => '10.00',
                'description' => '10% de réduction sur votre première commande',
                'minimumAmount' => null,
                'maxUses' => 100,
                'expiresAt' => new \DateTime('+6 months'),
            ],
            [
                'code' => 'SAVE20',
                'type' => 'percentage', 
                'value' => '20.00',
                'description' => '20% de réduction sur toute commande supérieure à 50 TND',
                'minimumAmount' => '50.00',
                'maxUses' => 50,
                'expiresAt' => new \DateTime('+3 months'),
            ],
            [
                'code' => 'FLAT5',
                'type' => 'fixed',
                'value' => '5.00',
                'description' => '5 TND de réduction immédiate',
                'minimumAmount' => null,
                'maxUses' => 200,
                'expiresAt' => new \DateTime('+1 year'),
            ],
            [
                'code' => 'SUMMER15',
                'type' => 'percentage',
                'value' => '15.00',
                'description' => 'Offre spéciale été : 15% de réduction',
                'minimumAmount' => '30.00',
                'maxUses' => 75,
                'expiresAt' => new \DateTime('+2 months'),
            ],
            [
                'code' => 'VIP25',
                'type' => 'percentage',
                'value' => '25.00',
                'description' => 'Exclusif VIP : 25% de réduction',
                'minimumAmount' => '100.00',
                'maxUses' => 25,
                'expiresAt' => new \DateTime('+4 months'),
            ],
            [
                'code' => 'STUDENT',
                'type' => 'fixed',
                'value' => '3.00',
                'description' => 'Réduction étudiante : 3 TND de réduction',
                'minimumAmount' => '20.00',
                'maxUses' => 150,
                'expiresAt' => new \DateTime('+8 months'),
            ],
        ];

        foreach ($couponsData as $couponData) {
            $coupon = new Coupon();
            $coupon->setCode($couponData['code']);
            $coupon->setType($couponData['type']);
            $coupon->setValue($couponData['value']);
            $coupon->setDescription($couponData['description']);
            $coupon->setMinimumAmount($couponData['minimumAmount']);
            $coupon->setMaxUses($couponData['maxUses']);
            $coupon->setExpiresAt($couponData['expiresAt']);
            $coupon->setIsActive(true);
            
            $manager->persist($coupon);
        }

        // Créer les mouvements de stock
        $stockMovementsData = [
            // Mouvements pour "Beloved" (premier livre)
            [
                'livre_titre' => 'Les Misérables',
                'type' => 'ENTREE',
                'quantity' => 50,
                'reason' => 'Stock initial',
                'date' => new \DateTime('2024-01-01'),
            ],
            [
                'livre_titre' => 'Les Misérables',
                'type' => 'SORTIE',
                'quantity' => 1,
                'reason' => 'Vente unitaire',
                'date' => new \DateTime('2024-01-15'),
            ],
            // Mouvements pour "À la recherche du temps perdu"
            [
                'livre_titre' => 'À la recherche du temps perdu',
                'type' => 'ENTREE',
                'quantity' => 30,
                'reason' => 'Stock initial',
                'date' => new \DateTime('2024-01-01'),
            ],
            [
                'livre_titre' => 'À la recherche du temps perdu',
                'type' => 'SORTIE',
                'quantity' => 1,
                'reason' => 'Vente unitaire',
                'date' => new \DateTime('2024-01-20'),
            ],
            // Mouvements pour "1984"
            [
                'livre_titre' => '1984',
                'type' => 'ENTREE',
                'quantity' => 40,
                'reason' => 'Stock initial',
                'date' => new \DateTime('2024-01-01'),
            ],
            [
                'livre_titre' => '1984',
                'type' => 'SORTIE',
                'quantity' => 1,
                'reason' => 'Vente unitaire',
                'date' => new \DateTime('2024-01-25'),
            ],
            // Mouvements additionnels pour simuler plus d'activité
            [
                'livre_titre' => 'L\'Étranger',
                'type' => 'ENTREE',
                'quantity' => 25,
                'reason' => 'Réapprovisionnement',
                'date' => new \DateTime('2024-02-01'),
            ],
            [
                'livre_titre' => 'L\'Étranger',
                'type' => 'SORTIE',
                'quantity' => 3,
                'reason' => 'Ventes multiples',
                'date' => new \DateTime('2024-02-10'),
            ],
            [
                'livre_titre' => 'Le Vieil Homme et la Mer',
                'type' => 'ENTREE',
                'quantity' => 20,
                'reason' => 'Stock initial',
                'date' => new \DateTime('2024-01-01'),
            ],
            [
                'livre_titre' => 'Germinal',
                'type' => 'SORTIE',
                'quantity' => 2,
                'reason' => 'Vente groupée',
                'date' => new \DateTime('2024-02-15'),
            ],
        ];

        foreach ($stockMovementsData as $movementData) {
            $livre = $manager->getRepository(Livre::class)->findOneBy(['titre' => $movementData['livre_titre']]);
            if ($livre) {
                $stockMovement = new StockMovement();
                $stockMovement->setLivre($livre);
                $stockMovement->setType($movementData['type']);
                $stockMovement->setQuantity($movementData['quantity']);
                $stockMovement->setReason($movementData['reason']);
                $stockMovement->setCreatedAt($movementData['date']);
                
                $manager->persist($stockMovement);
                
                // Mettre à jour le stock du livre
                if ($movementData['type'] === 'ENTREE') {
                    $livre->setNbExemplaires($livre->getNbExemplaires() + $movementData['quantity']);
                } else {
                    $livre->setNbExemplaires(max(0, $livre->getNbExemplaires() - $movementData['quantity']));
                }
            }
        }

        // Créer des commandes avec les livres spécifiés
        $commandesData = [
            [
                'user_email' => 'jean.dupont@libria.com',
                'status' => 'delivered',
                'created_at' => new \DateTime('2024-01-15'),
                'items' => [
                    ['livre_titre' => 'Les Misérables', 'quantity' => 1, 'price' => 29.99],
                ],
            ],
            [
                'user_email' => 'marie.martin@libria.com',
                'status' => 'delivered',
                'created_at' => new \DateTime('2024-01-20'),
                'items' => [
                    ['livre_titre' => 'À la recherche du temps perdu', 'quantity' => 1, 'price' => 35.50],
                ],
            ],
            [
                'user_email' => 'pierre.bernard@libria.com',
                'status' => 'delivered',
                'created_at' => new \DateTime('2024-01-25'),
                'items' => [
                    ['livre_titre' => '1984', 'quantity' => 1, 'price' => 19.99],
                ],
            ],
            [
                'user_email' => 'sophie.durand@libria.com',
                'status' => 'processing',
                'created_at' => new \DateTime('2024-02-10'),
                'items' => [
                    ['livre_titre' => 'L\'Étranger', 'quantity' => 3, 'price' => 15.99],
                ],
            ],
            [
                'user_email' => 'lucas.moreau@libria.com',
                'status' => 'shipped',
                'created_at' => new \DateTime('2024-02-15'),
                'items' => [
                    ['livre_titre' => 'Germinal', 'quantity' => 2, 'price' => 22.50],
                ],
            ],
        ];

        foreach ($commandesData as $commandeData) {
            $user = $manager->getRepository(User::class)->findOneBy(['email' => $commandeData['user_email']]);
            if ($user) {
                $commande = new Commande();
                $commande->setUser($user);
                $commande->setStatus($commandeData['status']);
                $commande->setCreatedAt($commandeData['created_at']);
                $commande->setShippingCost(5.00);
                
                $totalAmount = 0;
                
                foreach ($commandeData['items'] as $itemData) {
                    $livre = $manager->getRepository(Livre::class)->findOneBy(['titre' => $itemData['livre_titre']]);
                    if ($livre) {
                        $ligneCommande = new LigneCommande();
                        $ligneCommande->setCommande($commande);
                        $ligneCommande->setLivre($livre);
                        $ligneCommande->setQuantity($itemData['quantity']);
                        $ligneCommande->setPrice($itemData['price']);
                        $ligneCommande->setTotal($itemData['price'] * $itemData['quantity']);
                        
                        $manager->persist($ligneCommande);
                        $totalAmount += $itemData['price'] * $itemData['quantity'];
                    }
                }
                
                $commande->setTotalAmount($totalAmount);
                $manager->persist($commande);
            }
        }

        $manager->flush();
    }
}
