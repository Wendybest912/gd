<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    #[Route('/user/new', name: 'user_connexion' )]
    public function new(): Response
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);    
        
        return $this->render('hangman/new.html.twig', [
            "form" => $form->createView()
            ]);
    }
}