<?php

namespace App\Controller;

use App\Entity\Game;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
    public function historique(ManagerRegistry $doctrine, Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $em = $doctrine->getManager();
        $gameRepo = $em->getRepository(Game::class);

        $tri = $request->query->get('tri', 'id');
        $ordre = $request->query->get('ordre', 'DESC');
        $filtreDifficulte = $request->query->get('difficulte', '');
        $filtreResultat = $request->query->get('resultat', '');

        $trisAutorises = ['id', 'difficulty', 'guessNumber', 'winOrLose'];
        if (!in_array($tri, $trisAutorises)) {
            $tri = 'id';
        }
        if (!in_array($ordre, ['ASC', 'DESC'])) {
            $ordre = 'DESC';
        }

        // Construire les critères de recherche
        $criteres = ['player' => $this->getUser()];

        if ($filtreDifficulte !== '') {
            $criteres['difficulty'] = $filtreDifficulte;
        }
        if ($filtreResultat !== '') {
            $criteres['winOrLose'] = $filtreResultat;
        }

        $myGames = $gameRepo->findBy($criteres, [$tri => $ordre]);

        return $this->render('hangman/historique.html.twig', [
            'games' => $myGames,
            'tri' => $tri,
            'ordre' => $ordre,
            'filtreDifficulte' => $filtreDifficulte,
            'filtreResultat' => $filtreResultat,
        ]);
    }

}