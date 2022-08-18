<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentController extends AbstractController
{
    #[Route('/delete/{id}', name: 'delete_comment')]
    public function delete(Comment $comment): Response{
        $user = $this->getUser();
        if(in_array("ROLE_ADMIN", $user->getRoles()) XOR $user->getId() == $comment->getUser()->getId()){
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
            return $this->redirectToRoute('recipe_show', [
                'id' => $comment->getRecette()->getId(),
            ]);
        }else{
            return $this->redirectToRoute('login');
        }
    }
}
