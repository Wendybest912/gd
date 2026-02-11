<?php

namespace App\Controller;

use App\Entity\Game;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stat')]
class StatController extends AbstractController {
    #[Route('/', name: 'statistique_menu')]
    public function statistique(ManagerRegistry $doctrine){
        $em = $doctrine->getManager(); 

        $gameRepo = $em->getRepository(Game::class);

        $myGames = $gameRepo->findBy(['player' => $this->getUser()]);
        $totalGames = count($myGames);
        $totalWins = count($gameRepo->findBy(['winOrLose' => 'win']));
        $totalLosses = count($gameRepo->findBy(['winOrLose' => 'lose']));
        $winRate = $totalGames > 0 ? round(($totalWins / $totalGames) * 100 ) : 0 ;

        return $this->render('hangman/statistique.html.twig', [
            'totalWins' => $totalWins,
            'totalLosses' => $totalLosses,
            'totalGames' => $totalGames,
            'winRate' => $winRate
        ]);

    }

}