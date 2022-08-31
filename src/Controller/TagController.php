<?php

namespace App\Controller;

use App\Entity\RecipeTags;
use App\Form\TagType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        return $this->redirectToRoute('admin_index');
    }
}
