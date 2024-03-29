<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Demande;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use App\Repository\RecipeRepository;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(RecipeRepository $recipeRepository): Response
    {
        $user = $this->getUser();
        if($user instanceof User && !$user->getIsVerify()) {
            return $this->redirectToRoute('app_logout');
        }

        $recipes = $recipeRepository->getRandomRecipes("PLAT", 3);
        $randomRecipes = $recipeRepository->getRandomRecipes("PLAT", 8);
        $countRecipes = $recipeRepository->count([]);
        try{
            $countChiefs = $recipeRepository->countChiefs();
        }catch (\Exception $e) {
            $countChiefs = 0;
        }
        return $this->render('home/index.html.twig', [
            'recipes' => $recipes,
            'randomRecipes' => $randomRecipes,
            'countRecipes' => $countRecipes,
            'countChiefs' => $countChiefs,
        ]);
    }

    #[Route('/demande', name: 'demande')]
    public function demande(Request $request, SluggerInterface $slugger, DemandeRepository $demandeRepository): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(DemandeType::class);
        $form->handleRequest($request);

        $demande = $demandeRepository->findOneBy(['user' => $user->getId()]);
        if ($demande) {
            $demandes = 1;
        }else{
            $demandes = 0;
        }

        if ($form->isSubmitted() && $form->isValid()) {

            $brochureFile = $form->get('document')->getData();

            $mimeType = ['application/pdf', 'application/x-pdf', 'application/acrobat', 'applications/vnd.pdf', 'text/pdf', 'text/x-pdf'];
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile && in_array($brochureFile->getMimeType(), $mimeType)) {
                $demande = new Demande();
                $demande->setUser($user);
                $demande->setSendAt(new DateTimeImmutable());
                $demande->setAccept(0);

                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename =  uniqid($safeFilename . '-') . '.' . $brochureFile->guessExtension();
                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('document'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $fileUploadError = new FormError("Une erreur est survenue lors de l'upload de l'image");
                    $form->get('document')->addError($fileUploadError);
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $demande->setDocument($newFilename);

                $demande->setName($form->get('name')->getData());
                $demande->setMessage($form->get('message')->getData());
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($demande);
                $entityManager->flush();

                $this->addFlash('success', 'Votre demande a bien été envoyée');
                return $this->redirectToRoute('home');
            }else{
                $fileUploadError = new FormError("Veuillez uploader un fichier PDF");
                $form->get('document')->addError($fileUploadError);
                $this->addFlash('Error', 'Votre photo de profil n\'est pas une image ou n\'est pas valide.');
            }
        }
        return $this->render('profile/demande.html.twig', [
            "form" => $form->createView(),
            "demande" => $demandes,
        ]);
    }
}
