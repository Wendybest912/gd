<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{
    #[Route('/login', name: 'login')]
    public function login(AuthenticationUtils $utils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('hangman_home');
        }
        
        $error = $utils->getLastAuthenticationError();
        return $this->render('security/login.html.twig', ["error" => $error]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout()
    {
        
    }
}

