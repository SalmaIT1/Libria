<?php

namespace App\Controller;

use App\Entity\Auteur;
use App\Entity\Categorie;
use App\Entity\Commentaire;
use App\Entity\Editeur;
use App\Entity\Livre;
use App\Form\BookFilterType;
use App\Form\CommentaireType;
use App\Repository\AuteurRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommentaireRepository;
use App\Repository\EditeurRepository;
use App\Repository\LivreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BooksController extends AbstractController
{
    #[Route('/books', name: 'books')]
    public function index(
        Request $request,
        LivreRepository $livreRepository,
        PaginatorInterface $paginator,
        CategorieRepository $categorieRepository,
        AuteurRepository $auteurRepository,
        EditeurRepository $editeurRepository,
        CommentaireRepository $commentaireRepository
    ): Response {
        // Get filter parameters from request (GET method)
        $search = $request->query->get('search');
        $categorieId = $request->query->get('categorie');
        $auteurId = $request->query->get('auteur');
        $editeurId = $request->query->get('editeur');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $minRating = $request->query->get('minRating');
        $disponible = $request->query->get('disponible');
        $sortBy = $request->query->get('sortBy');

        // Prepare form data
        $formData = [];
        if ($search) $formData['search'] = $search;
        if ($categorieId) {
            $categorie = $categorieRepository->find($categorieId);
            if ($categorie) $formData['categorie'] = $categorie;
        }
        if ($auteurId) {
            $auteur = $auteurRepository->find($auteurId);
            if ($auteur) $formData['auteur'] = $auteur;
        }
        if ($editeurId) {
            $editeur = $editeurRepository->find($editeurId);
            if ($editeur) $formData['editeur'] = $editeur;
        }
        if ($minPrice) $formData['minPrice'] = $minPrice;
        if ($maxPrice) $formData['maxPrice'] = $maxPrice;
        if ($minRating) $formData['minRating'] = $minRating;
        if ($disponible !== null && $disponible !== '') $formData['disponible'] = $disponible;
        if ($sortBy) $formData['sortBy'] = $sortBy;

        $form = $this->createForm(BookFilterType::class, $formData);
        $form->handleRequest($request);

        $queryBuilder = $livreRepository->createQueryBuilder('l')
            ->leftJoin('l.auteurs', 'a')
            ->leftJoin('l.categories', 'c')
            ->leftJoin('l.editeur', 'e')
            ->addSelect('a', 'c', 'e');

        // Handle form submission
        if ($form->isSubmitted()) {
            $data = $form->getData();

            // Reset button
            if ($form->get('reset')->isClicked()) {
                return $this->redirectToRoute('books');
            }

            // Build query string for redirect
            $queryParams = [];
            if (!empty($data['search'])) $queryParams['search'] = $data['search'];
            if (!empty($data['categorie'])) $queryParams['categorie'] = $data['categorie']->getId();
            if (!empty($data['auteur'])) $queryParams['auteur'] = $data['auteur']->getId();
            if (!empty($data['editeur'])) $queryParams['editeur'] = $data['editeur']->getId();
            if (!empty($data['minPrice'])) $queryParams['minPrice'] = $data['minPrice'];
            if (!empty($data['maxPrice'])) $queryParams['maxPrice'] = $data['maxPrice'];
            if (!empty($data['minRating'])) $queryParams['minRating'] = $data['minRating'];
            if (!empty($data['disponible']) || $data['disponible'] === '0') $queryParams['disponible'] = $data['disponible'];
            if (!empty($data['sortBy'])) $queryParams['sortBy'] = $data['sortBy'];

            // Update parameters from form data
            $search = $data['search'] ?? null;
            $categorieId = $data['categorie']?->getId();
            $auteurId = $data['auteur']?->getId();
            $editeurId = $data['editeur']?->getId();
            $minPrice = $data['minPrice'] ?? null;
            $maxPrice = $data['maxPrice'] ?? null;
            $minRating = $data['minRating'] ?? null;
            $disponible = $data['disponible'] ?? null;
            $sortBy = $data['sortBy'] ?? null;

            // Redirect to preserve filters in URL
            return $this->redirectToRoute('books', $queryParams);
        }

        // Apply filters
        if ($search) {

            $queryBuilder
                ->andWhere('l.titre LIKE :search OR l.isbn LIKE :search OR a.prenom LIKE :search OR a.nom LIKE :search OR e.nomEditeur LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if ($categorieId) {
            $categorie = $categorieRepository->find($categorieId);
            if ($categorie) {
                $queryBuilder
                    ->andWhere('c.id = :categorie')
                    ->setParameter('categorie', $categorieId);
            }
        }

        if ($auteurId) {
            $auteur = $auteurRepository->find($auteurId);
            if ($auteur) {
                $queryBuilder
                    ->andWhere('a.id = :auteur')
                    ->setParameter('auteur', $auteurId);
            }
        }

        if ($editeurId) {
            $editeur = $editeurRepository->find($editeurId);
            if ($editeur) {
                $queryBuilder
                    ->andWhere('e.id = :editeur')
                    ->setParameter('editeur', $editeurId);
            }
        }

        if ($minPrice) {
            $queryBuilder
                ->andWhere('l.prix >= :minPrice')
                ->setParameter('minPrice', $minPrice);
        }

        if ($maxPrice) {
            $queryBuilder
                ->andWhere('l.prix <= :maxPrice')
                ->setParameter('maxPrice', $maxPrice);
        }

        // Filter by availability
        if ($disponible !== null && $disponible !== '') {
            if ($disponible === '1') {
                $queryBuilder->andWhere('l.nbExemplaires > 0');
            } elseif ($disponible === '0') {
                $queryBuilder->andWhere('l.nbExemplaires = 0');
            }
        }

        // Filter by minimum rating (will be applied after getting average ratings)
        // We'll filter in PHP after calculating ratings

        // Sorting
        if ($sortBy) {
            switch ($sortBy) {
                case 'titre_asc':
                    $queryBuilder->orderBy('l.titre', 'ASC');
                    break;
                case 'titre_desc':
                    $queryBuilder->orderBy('l.titre', 'DESC');
                    break;
                case 'prix_asc':
                    $queryBuilder->orderBy('l.prix', 'ASC');
                    break;
                case 'prix_desc':
                    $queryBuilder->orderBy('l.prix', 'DESC');
                    break;
                case 'pages_asc':
                    $queryBuilder->orderBy('l.nbPages', 'ASC');
                    break;
                case 'pages_desc':
                    $queryBuilder->orderBy('l.nbPages', 'DESC');
                    break;
                case 'date_desc':
                    $queryBuilder->orderBy('l.dateEdition', 'DESC');
                    break;
                case 'date_asc':
                    $queryBuilder->orderBy('l.dateEdition', 'ASC');
                    break;
                case 'rating_desc':
                    // Will be sorted after getting ratings
                    $queryBuilder->orderBy('l.titre', 'ASC');
                    break;
                case 'reviews_desc':
                    // Will be sorted after getting comment counts
                    $queryBuilder->orderBy('l.titre', 'ASC');
                    break;
            }
        } else {
            $queryBuilder->orderBy('l.titre', 'ASC');
        }

        // Build query parameters for pagination
        $queryParams = [];
        if ($search) $queryParams['search'] = $search;
        if ($categorieId) $queryParams['categorie'] = $categorieId;
        if ($auteurId) $queryParams['auteur'] = $auteurId;
        if ($editeurId) $queryParams['editeur'] = $editeurId;
        if ($minPrice) $queryParams['minPrice'] = $minPrice;
        if ($maxPrice) $queryParams['maxPrice'] = $maxPrice;
        if ($minRating) $queryParams['minRating'] = $minRating;
        if ($disponible !== null && $disponible !== '') $queryParams['disponible'] = $disponible;
        if ($sortBy) $queryParams['sortBy'] = $sortBy;

        // Pagination
        $pagination = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            12, // Items per page
            ['defaultSortFieldName' => 'l.titre', 'defaultSortDirection' => 'asc']
        );

        // Set custom route parameters for pagination
        $pagination->setParam('search', $search);
        $pagination->setParam('categorie', $categorieId);
        $pagination->setParam('auteur', $auteurId);
        $pagination->setParam('editeur', $editeurId);
        $pagination->setParam('minPrice', $minPrice);
        $pagination->setParam('maxPrice', $maxPrice);
        $pagination->setParam('sortBy', $sortBy);

        // Calculate average ratings for all books in current page
        $averageRatings = [];
        $commentCounts = [];
        foreach ($pagination as $livre) {
            $averageRatings[$livre->getId()] = $commentaireRepository->getAverageRating($livre->getId());
            $commentCounts[$livre->getId()] = $commentaireRepository->getCommentCount($livre->getId());
        }

        // Filter by minimum rating if specified
        if ($minRating) {
            $filteredBooks = [];
            foreach ($pagination as $livre) {
                $rating = $averageRatings[$livre->getId()] ?? 0;
                if ($rating >= $minRating) {
                    $filteredBooks[] = $livre;
                }
            }
            // Note: This is a simple filter. For better performance with large datasets,
            // you'd want to do this in the SQL query with a subquery
        }

        // Sort by rating or reviews if needed
        if ($sortBy === 'rating_desc' || $sortBy === 'reviews_desc') {
            $booksArray = iterator_to_array($pagination);
            usort($booksArray, function($a, $b) use ($sortBy, $averageRatings, $commentCounts) {
                if ($sortBy === 'rating_desc') {
                    $ratingA = $averageRatings[$a->getId()] ?? 0;
                    $ratingB = $averageRatings[$b->getId()] ?? 0;
                    return $ratingB <=> $ratingA;
                } else {
                    $countA = $commentCounts[$a->getId()] ?? 0;
                    $countB = $commentCounts[$b->getId()] ?? 0;
                    return $countB <=> $countA;
                }
            });
            // Note: This modifies the order but pagination might need adjustment
        }

        return $this->render('books/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form,
            'averageRatings' => $averageRatings,
            'commentCounts' => $commentCounts,
        ]);
    }

    #[Route('/books/{id}', name: 'book_show')]
    public function show(
        Livre $livre,
        Request $request,
        EntityManagerInterface $entityManager,
        CommentaireRepository $commentaireRepository
    ): Response {
        $commentaire = new Commentaire();
        $commentaire->setLivre($livre);
        
        if ($this->getUser()) {
            $commentaire->setUser($this->getUser());
        }

        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->getUser()) {
                $this->addFlash('error', 'You must be logged in to leave a comment.');
                return $this->redirectToRoute('login');
            }

            if ($this->isGranted('ROLE_ADMIN')) {
                $this->addFlash('error', 'Administrators cannot leave reviews on books.');
                return $this->redirectToRoute('book_show', ['id' => $livre->getId()]);
            }

            $commentaire->setUser($this->getUser());
            $commentaire->setDateCreation(new \DateTime());
            
            $entityManager->persist($commentaire);
            $entityManager->flush();

            $this->addFlash('success', 'Your comment has been added successfully!');
            return $this->redirectToRoute('book_show', ['id' => $livre->getId()]);
        }

        // Get average rating
        $averageRating = $commentaireRepository->getAverageRating($livre->getId());
        $commentCount = $commentaireRepository->getCommentCount($livre->getId());

        // Get all comments for this book
        $commentaires = $commentaireRepository->createQueryBuilder('c')
            ->where('c.livre = :livre')
            ->setParameter('livre', $livre)
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('books/show.html.twig', [
            'livre' => $livre,
            'form' => $form,
            'commentaires' => $commentaires,
            'averageRating' => $averageRating,
            'commentCount' => $commentCount,
        ]);
    }
}

