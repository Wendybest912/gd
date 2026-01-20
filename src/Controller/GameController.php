<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class GameController extends AbstractController
{
    #[Route('/game/new', name: 'game_form' )]
    public function new(Request $request): Response
    {
        $game = new Game();

        $form = $this->createForm(GameType::class, $game);    

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            dump($game);
        }
        
        return $this->render('hangman/new.html.twig', [
            "form" => $form->createView()
            ]);
    }
}