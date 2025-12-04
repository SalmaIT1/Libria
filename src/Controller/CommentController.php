<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\Livre;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('/edit/{id}', name: 'comment_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Check if user owns this comment (admins cannot edit other users' comments)
        if ($commentaire->getUser() !== $user) {
            $this->addFlash('error', 'You can only edit your own comments.');
            return $this->redirectToRoute('book_show', ['id' => $commentaire->getLivre()->getId()]);
        }

        if ($request->isMethod('POST')) {
            $contenu = $request->request->get('contenu');
            $rating = $request->request->get('rating');

            // Validate input
            if (empty($contenu) || $rating < 1 || $rating > 5) {
                if ($request->isXmlHttpRequest()) {
                    return new JsonResponse(['error' => 'Please provide valid comment and rating (1-5).']);
                }
                $this->addFlash('error', 'Please provide valid comment and rating (1-5).');
                return $this->redirectToRoute('book_show', ['id' => $commentaire->getLivre()->getId()]);
            }

            $commentaire->setContenu($contenu);
            $commentaire->setRating($rating);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true, 'message' => 'Comment updated successfully!']);
            }
            $this->addFlash('success', 'Comment updated successfully!');
            return $this->redirectToRoute('book_show', ['id' => $commentaire->getLivre()->getId()]);
        }

        return $this->redirectToRoute('book_show', ['id' => $commentaire->getLivre()->getId()]);
    }

    #[Route('/delete/{id}', name: 'comment_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        
        // Check if user owns this comment OR is admin
        if ($commentaire->getUser() !== $user && !is_granted('ROLE_ADMIN')) {
            $this->addFlash('error', 'You can only delete your own comments.');
            return $this->redirectToRoute('book_show', ['id' => $commentaire->getLivre()->getId()]);
        }

        $livreId = $commentaire->getLivre()->getId();
        
        $entityManager->remove($commentaire);
        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true, 'message' => 'Comment deleted successfully!']);
        }
        
        $this->addFlash('success', 'Comment deleted successfully!');
        return $this->redirectToRoute('book_show', ['id' => $livreId]);
    }
}
