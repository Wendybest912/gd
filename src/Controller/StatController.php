<?php

namespace App\Controller;

use App\Entity\Game;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('/historique', name: 'statistique_historique')]
    public function historique(ManagerRegistry $doctrine): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $doctrine->getManager();
        $gameRepo = $em->getRepository(Game::class);

        $myGames = $gameRepo->findBy(
            ['player' => $this->getUser()],
            ['id' => 'DESC']
        );

        return $this->render('hangman/historique.html.twig', [
            'games' => $myGames,
        ]);
    }

}