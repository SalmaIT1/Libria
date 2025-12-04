<?php

namespace App\Controller;

use App\Entity\Favori;
use App\Entity\Livre;
use App\Repository\FavoriRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FavorisController extends AbstractController
{
    #[Route('/favoris', name: 'favoris')]
    public function index(FavoriRepository $favoriRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('login');
        }

        $favoris = $favoriRepository->findBy(
            ['user' => $user],
            ['dateAjout' => 'DESC']
        );

        return $this->render('favoris/index.html.twig', [
            'favoris' => $favoris,
        ]);
    }

    #[Route('/favoris/toggle/{id}', name: 'favoris_toggle', methods: ['POST'])]
    public function toggle(Livre $livre, EntityManagerInterface $entityManager, FavoriRepository $favoriRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['error' => 'Vous devez être connecté'], Response::HTTP_UNAUTHORIZED);
        }

        $favori = $favoriRepository->findOneBy([
            'user' => $user,
            'livre' => $livre
        ]);

        if ($favori) {
            // Retirer des favoris
            $entityManager->remove($favori);
            $entityManager->flush();
            return $this->json(['added' => false, 'message' => 'Livre retiré des favoris']);
        } else {
            // Ajouter aux favoris
            $favori = new Favori();
            $favori->setUser($user);
            $favori->setLivre($livre);
            $entityManager->persist($favori);
            $entityManager->flush();
            return $this->json(['added' => true, 'message' => 'Livre ajouté aux favoris']);
        }
    }

    #[Route('/favoris/check/{id}', name: 'favoris_check', methods: ['GET'])]
    public function check(Livre $livre, FavoriRepository $favoriRepository): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json(['isFavorite' => false]);
        }

        $favori = $favoriRepository->findOneBy([
            'user' => $user,
            'livre' => $livre
        ]);

        return $this->json(['isFavorite' => $favori !== null]);
    }
}

