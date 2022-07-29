<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/{id}/delete', name: 'delete_comment')]
    public function delete(Comment $comment): Response{
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($comment);
        $entityManager->flush();
        return $this->redirectToRoute('recipe_show', [
            'id' => $comment->getRecette()->getId(),
        ]);
    }
}
