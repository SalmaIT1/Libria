<?php
namespace App\Controller;

use App\Repository\AuteurRepository;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class AuthorsController extends AbstractController
{
    #[Route('/authors', name: 'authors')]
    public function index(AuteurRepository $auteurRepository): Response
    {
        $authors = $auteurRepository->findAll();
        return $this->render('authors/index.html.twig', [
            'authors' => $authors
        ]);
    }

    #[Route('/authors/{id}', name: 'author_show')]
    public function show(int $id, AuteurRepository $auteurRepository, LivreRepository $livreRepository): Response
    {
        $author = $auteurRepository->find($id);
        if (!$author) {
            throw $this->createNotFoundException('Auteur not found');
        }
        $books = $livreRepository->createQueryBuilder('l')
            ->join('l.auteurs', 'a')
            ->where('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()->getResult();

        return $this->render('authors/show.html.twig', [
            'author' => $author,
            'books' => $books
        ]);
    }
}
