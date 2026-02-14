<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

final class UserController extends AbstractController
{
    #[Route("/user/create", name: "user_create")]
    public function create(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('hangman_home');
        }
        
        $product = new User($userPasswordHasher); // Utilisateur vide 
        $form = $this->createForm(UserType::class, $product);

        // Traitement pour hydrater l'objet "$product" vide
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $doctrine->getManager();
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute("login");
        }

        return $this->render("user/form.html.twig", [
            "form" => $form->createView()
        ]);
    }

    #[Route("/profil/avatar", name: "user_avatar")]
    public function uploadAvatar(Request $request, ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $form = $this->createFormBuilder()
            ->add('avatar', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('avatar')->getData();

            if ($file) {
                $fileName = uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/avatars',
                        $fileName
                    );

                    $user->setAvatar($fileName);
                    $doctrine->getManager()->flush();

                    $this->addFlash('success', 'Avatar mis à jour !');
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload.');
                }
            }

            return $this->redirectToRoute('user_avatar');
        }

        return $this->render('user/avatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route("/profil", name:"user_profil")]
    public function profil() : Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->render('user/profil.html.twig');
    }   

    /*#[Route("/admin/create", name: "admin_create")]
    public function createAdmin(ManagerRegistry $doctrine, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $admin = new User($userPasswordHasher);
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword(
            "admin"
        );

        $em = $doctrine->getManager();
        $em->persist($admin);
        $em->flush();

        $this->addFlash('success', ' Admin créé ');
        return $this->redirectToRoute('login');
    }*/
}

