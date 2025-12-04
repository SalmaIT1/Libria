<?php
namespace App\Controller;

use App\Repository\EditeurRepository;
use App\Repository\LivreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PublishersController extends AbstractController
{
    #[Route('/publishers', name: 'publishers')]
    public function index(EditeurRepository $editeurRepository): Response
    {
        $publishers = $editeurRepository->findAll();
        return $this->render('publishers/index.html.twig', [
            'publishers' => $publishers
        ]);
    }

    #[Route('/publishers/{id}', name: 'publisher_show')]
    public function show(int $id, EditeurRepository $editeurRepository, LivreRepository $livreRepository): Response
    {
        $publisher = $editeurRepository->find($id);
        if (!$publisher) {
            throw $this->createNotFoundException('Publisher not found');
        }
        $books = $livreRepository->findBy(['editeur' => $id]);

        return $this->render('publishers/show.html.twig', [
            'publisher' => $publisher,
            'books' => $books
        ]);
    }
}
