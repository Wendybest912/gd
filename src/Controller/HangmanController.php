<?php

namespace App\Controller;

use App\Entity\Game;
use App\Controller\Api\Word as RandomWord;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class HangmanController extends AbstractController
{
    private array $difficulties = [
        'facile' => [
            'name' => 'facile',
            'label' => 'Facile',
            'icon' => '🟢',
            'description' => 'Pour débuter en douceur',
            'wordLength' => '4-5 lettres',
            'minLength' => 4,
            'maxLength' => 5
        ],
        'moyen' => [
            'name' => 'moyen',
            'label' => 'Moyen',
            'icon' => '🟡',
            'description' => 'Un petit défi',
            'wordLength' => '6-7 lettres',
            'minLength' => 6,
            'maxLength' => 7
        ],
        'difficile' => [
            'name' => 'difficile',
            'label' => 'Difficile',
            'icon' => '🔴',
            'description' => 'Pour les courageux',
            'wordLength' => '8-10 lettres',
            'minLength' => 8,
            'maxLength' => 10
        ],
        'impossible' => [
            'name' => 'impossible',
            'label' => 'Impossible',
            'icon' => '🟣',
            'description' => 'Presque insurmontable',
            'wordLength' => '11-14 lettres',
            'minLength' => 11,
            'maxLength' => 14
        ],
        'divin' => [
            'name' => 'divin',
            'label' => 'Divin',
            'icon' => '👑',
            'description' => 'Seuls les dieux y arrivent',
            'wordLength' => '15+ lettres',
            'minLength' => 15,
            'maxLength' => 99
        ]
    ];

    public function __construct(private RandomWord $word)
    {
    }

    private int $maxErrors = 6;

    #[Route('/', name: 'hangman_home')]
    public function index(): Response
    {
        return $this->render('hangman/index.html.twig');
    }

    #[Route('/regles', name: 'hangman_rules')]
    public function howToPlay(): Response
    {
        return $this->render('hangman/rules.html.twig');
    }

    #[Route('/difficultes', name: 'hangman_difficulties')]
    public function difficulties(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('hangman/difficulties.html.twig', [
            'difficulties' => $this->difficulties
        ]);
    }

    #[Route('/jouer/{difficulty}', name: 'hangman_play')]
    public function play(string $difficulty, SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $difficultyConfig = $this->difficulties[$difficulty] ?? $this->difficulties['facile'];
        
        $word = $this->word->getRandomWord(
            $difficultyConfig['minLength'],
            $difficultyConfig['maxLength']
        );
        
        // Stocker en session
        $session->set('hangman_word', $word);
        $session->set('hangman_difficulty', $difficulty);
        $session->set('hangman_found_letters', []);
        $session->set('hangman_used_letters', []);
        $session->set('hangman_errors', 0);
        
        // Rediriger vers la page de jeu
        return $this->redirectToRoute('hangman_game');
    }

    #[Route('/jeu', name: 'hangman_game')]
    public function game(SessionInterface $session): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Récupérer les données de session
        $word = $session->get('hangman_word');
        $difficulty = $session->get('hangman_difficulty');
        $foundLetters = $session->get('hangman_found_letters', []);
        $usedLetters = $session->get('hangman_used_letters', []);
        $errors = $session->get('hangman_errors', 0);
        
        // Si pas de partie en cours, rediriger
        if (!$word) {
            return $this->redirectToRoute('hangman_difficulties');
        }
        
        $difficultyConfig = $this->difficulties[$difficulty] ?? $this->difficulties['facile'];
        
        return $this->render('hangman/play.html.twig', [
            'difficulty' => $difficulty,
            'difficultyLabel' => $difficultyConfig['label'],
            'word' => $word,
            'foundLetters' => $foundLetters,
            'usedLetters' => $usedLetters,
            'errors' => $errors,
            'maxErrors' => $this->maxErrors
        ]);
    }

    #[Route('/guess/{letter}', name: 'hangman_guess')]
    public function guess(string $letter, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Récupérer les données de session
        $word = $session->get('hangman_word');
        $difficulty = $session->get('hangman_difficulty');
        $foundLetters = $session->get('hangman_found_letters', []);
        $usedLetters = $session->get('hangman_used_letters', []);
        $errors = $session->get('hangman_errors', 0);
        
        // Si pas de partie en cours, rediriger
        if (!$word) {
            return $this->redirectToRoute('hangman_difficulties');
        }
        
        // Normaliser la lettre en majuscule
        $letter = strtoupper($letter);
        
        // Si déjà utilisée, ignorer
        if (in_array($letter, $usedLetters)) {
            return $this->redirectToRoute('hangman_game');
        }
        
        // Ajouter aux lettres utilisées
        $usedLetters[] = $letter;
        $session->set('hangman_used_letters', $usedLetters);
        
        // Vérifier si la lettre est dans le mot
        if (str_contains($word, $letter)) {
            // Bonne lettre !
            $foundLetters[] = $letter;
            $session->set('hangman_found_letters', $foundLetters);
            
            // Vérifier victoire
            if ($this->isWordComplete($word, $foundLetters)) {
                $this->saveGame($entityManager, $difficulty, count($usedLetters), 'win');
                $this->clearSession($session);
                return $this->redirectToRoute('hangman_victory', ['word' => $word]);
            }
        } else {
            // Mauvaise lettre !
            $errors++;
            $session->set('hangman_errors', $errors);
            
            // Vérifier défaite
            if ($errors >= $this->maxErrors) {
                $this->saveGame($entityManager, $difficulty, count($usedLetters), 'lose');
                $this->clearSession($session);
                return $this->redirectToRoute('hangman_defeat', ['word' => $word]);
            }
        }
        
        return $this->redirectToRoute('hangman_game');
    }

    #[Route('/victoire/{word}', name: 'hangman_victory')]
    public function victory(string $word): Response
    {
        return $this->render('hangman/victory.html.twig', [
            'word' => $word
        ]);
    }

    #[Route('/defaite/{word}', name: 'hangman_defeat')]
    public function defeat(string $word): Response
    {
        return $this->render('hangman/defeat.html.twig', [
            'word' => $word
        ]);
    }

    // === MÉTHODES PRIVÉES ===

    private function isWordComplete(string $word, array $foundLetters): bool
    {
        foreach (str_split($word) as $letter) {
            if (!in_array($letter, $foundLetters)) {
                return false;
            }
        }
        return true;
    }

    private function clearSession(SessionInterface $session): void
    {
        $session->remove('hangman_word');
        $session->remove('hangman_difficulty');
        $session->remove('hangman_found_letters');
        $session->remove('hangman_used_letters');
        $session->remove('hangman_errors');
    }

    private function saveGame(EntityManagerInterface $entityManager, string $difficulty, int $guessNumber, string $result): void
    {
        if ($this->getUser()) {
            $game = new Game();
            $game->setDifficulty($difficulty);
            $game->setGuessNumber($guessNumber);
            $game->setWinOrLose($result);
            $game->setPlayer($this->getUser());
            
            $entityManager->persist($game);
            $entityManager->flush();
        }
    }
}