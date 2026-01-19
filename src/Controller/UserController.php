<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/new', name: 'user_connexion' )]
    public function new(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);    

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($user);
        }
        
        return $this->render('hangman/new.html.twig', [
            "form" => $form->createView()
            ]);
    }
}