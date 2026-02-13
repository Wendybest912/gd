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

