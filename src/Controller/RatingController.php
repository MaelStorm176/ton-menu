<?php

namespace App\Controller;

use App\Entity\Rating;
use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RatingController extends AbstractController
{
    #[Route('/rate/{id}/{value}', name: 'rate')]
    public function index(Request $request, $id, $value)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $repository = $this->getDoctrine()->getRepository(Recipe::class);
        $recipe = $repository->find($id);

        $rating = $this->getDoctrine()->getRepository(Rating::class);
        $rate = $rating->findBy([
            "recette" => $recipe, 
            "user" => $user]);

        //var_dump($liked);
        if ($request->isXmlHttpRequest() && $rate == []) {
            $rate = new Rating();
    
            $rate->setUser($user);
            $rate->setRecette($recipe);
            $rate->setRate($value);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rate);
            $entityManager->flush();

            return new JsonResponse($rating);
        }else{
            //$id_like = $liked[0]->getId();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($rate[0]);
            $entityManager->flush();

            $rate = new Rating();
            $rate->setUser($user);
            $rate->setRecette($recipe);
            $rate->setRate($value);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($rate);
            $entityManager->flush();

            return new JsonResponse($rating);
        }

        return $this->redirectToRoute('/');
    }
}
