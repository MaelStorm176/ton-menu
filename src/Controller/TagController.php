<?php

namespace App\Controller;

use App\Entity\RecipeTags;
use App\Form\TagType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("tag/create", name="tag_create", methods={"GET", "POST"})
     */
    public function create(Request $request): Response
    {
        $tag = new RecipeTags();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);
        //dd($tag);
        if ($form->isSubmitted() && $form->isValid()) {
            $tag = $form->getData();
            $this->entityManager->persist($tag);
            $this->entityManager->flush();
            $this->addFlash('success', 'Le tag a bien été créé');
            return $this->redirectToRoute('admin_tags');
        }
        return $this->render('admin/tags/edit_tags.html.twig', [
            'tagForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("tag/{tag}/edit", name="tag_edit")
     */
    public function edit(RecipeTags $tag): Response
    {
        $tagForm = $this->createForm(TagType::class, $tag);
        return $this->render('admin/tags/edit_tags.html.twig', [
            'tag' => $tag,
            'tagForm' => $tagForm->createView(),
        ]);
    }

    /**
     * @Route("tag/{tag}/delete", name="tag_delete", methods={"DELETE", "POST"})
     */
    public function delete(RecipeTags $tag, \Symfony\Component\HttpFoundation\Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($tag);
            $this->entityManager->flush();
            $this->addFlash('success', 'Tag supprimé avec succès');
        }else{
            $this->addFlash('error', 'Une erreur est survenue');
        }
        return $this->redirectToRoute('admin_tags');
    }

    #[Route('/tag/check-name', name: 'check_name', methods: ['GET'])]
    public function checkName(Request $request): JsonResponse{
        $name = $request->query->get('name');
        if (!$name) {
            return new JsonResponse(['error' => 'No name provided'], 400);
        }
        $tag = $this->getDoctrine()->getRepository(RecipeTags::class)->findOneBy(['name' => $name]);
        if ($tag){
            return new JsonResponse(['success' => false,'message' => 'Ce nom est déjà utilisé']);
        }
        return new JsonResponse(['success' => true,'message' => 'Ce nom est disponible']);
    }
}
