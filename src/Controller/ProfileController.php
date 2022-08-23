<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Recipe;
use App\Entity\Follower;
use App\Form\ProfileType;
use App\Entity\SavedMenus;
use Symfony\Component\Form\FormError;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    #[Route('/profile', name: 'profile')]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('home');
        }
        if(!$user->getIsVerify()){
            return $this->redirectToRoute('app_logout');
        }


        $oldHash = $user->getPassword();
        $oldPicture = $user->getProfilePicture();
        $manager = $this->getDoctrine()->getManager();

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getProfilePicture() == null) {
                $user->setProfilePicture($oldPicture);
            }
            if ($form->get('password')->getData() != null && $form->get('password')->getData() != "") {
                //hash the new password
                $newHash = $this->passwordEncoder->encodePassword($user, $form->get('password')->getData());
                if ($oldHash != $newHash) {
                    $user->setPassword($newHash);
                }
            } else {
                $user->setPassword($oldHash);
            }

            /** @var UploadedFile $brochureFile */
            $brochureFile = $form->get('profile_picture')->getData();
            $mimeType = ['image/jpeg', 'image/png', 'image/jpg'];
            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($brochureFile && in_array($brochureFile->getMimeType(), $mimeType)) {
                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename =  uniqid($safeFilename . '-') . '.' . $brochureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $brochureFile->move(
                        $this->getParameter('profile_picture'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $fileUploadError = new FormError("Une erreur est survenue lors de l'upload de l'image");
                    $form->get('profile_picture')->addError($fileUploadError);
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setProfilePicture($newFilename);
            }else{
                $user->setProfilePicture($oldPicture);
                $this->addFlash('error', 'Votre photo de profil n\'est pas une image ou n\'est pas valide.');
            }
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Votre profil a bien été mis à jour');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'generated_menus' => $this->getDoctrine()->getRepository(SavedMenus::class)->findBy(['user' => $user], ['createdAt' => 'DESC'], 5),
        ]);
    }

    #[Route('/chef/{id}', name: 'chef_page')]
    public function chefPage($id, PaginatorInterface $paginator, Request $request): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $follower = $this->getDoctrine()->getRepository(Follower::class)->findBy(['chef_id' => $id]);
        //dd(in_array("ROLE_CHIEF", $user->getRoles()));
        if (!$user instanceof User || !in_array("ROLE_CHIEF", $user->getRoles()) || !$user->getIsVerify()) {
            throw $this->createNotFoundException('Aucun chef trouvé avec cet id');
        }

        $countEntrees = $this->getDoctrine()->getRepository(Recipe::class)->count(['user_id' => $user, 'type' => 'ENTREE']);
        $countPlats = $this->getDoctrine()->getRepository(Recipe::class)->count(['user_id' => $user, 'type' => 'PLAT']);
        $countDesserts = $this->getDoctrine()->getRepository(Recipe::class)->count(['user_id' => $user, 'type' => 'DESSERT']);
        $recettes = $paginator->paginate(
            $user->getRecipes(),
            $request->query->getInt('page', 1),
            8
        );
        $bestRecipes = $this->getDoctrine()->getRepository(Recipe::class)->findBestRatedRecipesMadeByAUser($user);
        return $this->render('profile/chef.html.twig', [
            'user' => $user,
            'follower' => $follower,
            'recettes' => $recettes,
            'bestRecipes' => $bestRecipes,
        ]);
    }

    #[Route('/mentions-legales', name: 'mentions_legales')]
    public function mentionsLegales(): Response
    {
        return $this->render('profile/mentions.html.twig');
    }

    #[Route('/follow/{id}', name: 'follow')]
    public function followChef($id): Response
    {
        $user = $this->getUser();
        $chief = $this->getDoctrine()->getRepository(User::class)->find($id);
        if (!$user instanceof User) {
            return $this->redirectToRoute('home');
        }
        if (!$chief instanceof User) {
            throw $this->createNotFoundException('Aucun chef trouvé avec cet id');
        }
        $follow = new Follower();
        $follow->setUserId($user->getId());
        $follow->setChefId($chief->getId());
        $follow->setFollowAt(new \DateTimeImmutable());


        $manager = $this->getDoctrine()->getManager();
        $manager->persist($follow);
        $manager->flush();

        $this->addFlash('success', 'Vous êtes maintenant abonné à ce chef');
        return $this->redirectToRoute('chef_page', ['id' => $id]);
    }

    #[Route('/unfollow/{id}', name: 'unfollow')]
    public function unfollowChef($id): Response
    {
        $user = $this->getUser();
        $chief = $this->getDoctrine()->getRepository(User::class)->find($id);
        if (!$user instanceof User) {
            return $this->redirectToRoute('home');
        }
        if (!$chief instanceof User) {
            throw $this->createNotFoundException('Aucun chef trouvé avec cet id');
        }
        $follow = $this->getDoctrine()->getRepository(Follower::class)->findOneBy(['user_id' => $user->getId(), 'chef_id' => $chief->getId()]);
        if (!$follow instanceof Follower) {
            throw $this->createNotFoundException('Aucun abonnement trouvé');
        }

        $manager = $this->getDoctrine()->getManager();
        $manager->remove($follow);
        $manager->flush();

        $this->addFlash('success', 'Vous n\'êtes plus abonné à ce chef');
        return $this->redirectToRoute('chef_page', ['id' => $id]);
    }

    #[Route('/profile/refresh-key', name: 'refresh_key', methods: ['GET'])]
    public function refreshApiKey(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $user = $this->getUser();
            if (!$user instanceof User || !$user->getIsVerify() || (!$user->hasRole("ROLE_PREMIUM") && !$user->hasRole("ROLE_ADMIN"))) {
                return $this->json(['error' => 'Vous n\'avez pas accès à cette page'], Response::HTTP_FORBIDDEN);
            }
            $user->refreshApiKey();
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            return $this->json(['key' => $user->getApiKey(), 'success' => true]);
        }
        return $this->createNotFoundException('Page not found');
    }
}
