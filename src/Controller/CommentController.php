<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\SignalementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/delete/{id}', name: 'delete_comment')]
    public function delete(Comment $comment, SignalementRepository $signalementRepository, Request $request): Response{
        $user = $this->getUser();
        if($user instanceof User && ($user->hasRole("ROLE_ADMIN") || $user->getId() == $comment->getUser()->getId())){
            $signalement = $signalementRepository->findOneBy(['message' => $comment]);
            $entityManager = $this->getDoctrine()->getManager();
            if($signalement){
                $entityManager->remove($signalement);
            }
            $entityManager->remove($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Le commentaire a bien été supprimé');
            return $this->redirect($request->headers->get('referer'));
        }else{
            $this->addFlash('error', 'Vous n\'avez pas les droits pour supprimer ce commentaire');
            return $this->redirectToRoute('login');
        }
    }
}
