<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Entity\Signalement;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SignalementController extends AbstractController
{
    #[Route('/signalement/{id}', name: 'send_signalement')]
    public function send_signalement(Comment $comment): Response
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        if(!$user instanceof User){
            $this->addFlash('error', 'Vous devez être connecté pour signaler un commentaire');
            return $this->redirectToRoute('login');
        }

        $signalementRepository = $this->getDoctrine()->getRepository(Signalement::class);
        $report = $signalementRepository->findOneBy(['message' => $comment]);

        if($report){
            $userSignalements = $report->getUserSignalement();
            foreach($userSignalements as $userSignalement){
                if($userSignalement->getId() == $user->getId()){
                    return $this->redirectToRoute('recipe_show', [
                        'id' => $comment->getRecette()->getId(),
                    ]);
                }
            }
            $userSignalements[] = $user;
            $report->setUserSignalement($userSignalements);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($report);
        }
        else{
            $userArray = array();
            $userArray[] = $user;
            $signalement = new Signalement();
            $signalement->setMessage($comment);
            $signalement->setNbSignalement(1);
            $signalement->setTraiter(false);
            $signalement->setCreateAt(new DateTimeImmutable());
            $signalement->setUpdateAt(new DateTimeImmutable());
            $signalement->setUserSignalement($userArray);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($signalement);
        }
        $entityManager->flush();
        $this->addFlash('success', 'Le commentaire a bien été signalé');
        return $this->redirectToRoute('recipe_show', [
            'id' => $comment->getRecette()->getId(),
        ]);
    }
}
