<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

    private array $words = [
        'CHAT', 'PAIN', 'LUNE', 'ROSE', 'BLEU', 'NOIR', 'VERT', 'BEAU', 'JOUR', 'NUIT',
        'PONT', 'ARBRE', 'FLEUR', 'TERRE', 'CIEL', 'MONDE', 'ROUGE', 'JAUNE',
        
        'MAISON', 'JARDIN', 'SOLEIL', 'ETOILE', 'ORANGE', 'VIOLET', 'MUSIQUE', 'CHANSON',
        'TABLEAU', 'FENETRE', 'BOUQUET', 'CUISINE', 'CHAMBRE', 'VOITURE', 'MONTAGE',
        
        'CHOCOLAT', 'ELEPHANT', 'CROCODILE', 'PAPILLON', 'ORDINATEUR', 'BIBLIOTHEQUE',
        'RESTAURANT', 'APPARTEMENT', 'TELEPHONE', 'TELEVISION', 'PRINTEMPS',
        
        'EXTRAORDINAIRE', 'ANTICONSTITUTION', 'DEVELOPPEMENT', 'GOUVERNEMENT',
        'ENVIRONNEMENT', 'ADMINISTRATION', 'TRANSFORMATION', 'COMMUNICATION',
        
        'ANTICONSTITUTIONNELLEMENT', 'INTERDISCIPLINARITE', 'ELECTROENCEPHALOGRAMME',
        'INTERNATIONALISATION', 'CONSTITUTIONNELLEMENT', 'CONTRAVENTIONNELLE'
    ];

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
        return $this->render('hangman/difficulties.html.twig', [
            'difficulties' => $this->difficulties
        ]);
    }

    #[Route('/jouer/{difficulty}', name: 'hangman_play')]
    public function play(string $difficulty): Response
    {
        $difficultyConfig = $this->difficulties[$difficulty];
        
        $filteredWords = array_filter($this->words, function($word) use ($difficultyConfig) {
            $length = strlen($word);
            return $length >= $difficultyConfig['minLength'] && $length <= $difficultyConfig['maxLength'];
        });
        
        $word = $filteredWords[array_rand($filteredWords)];
        

        $foundLetters = [];
        $usedLetters = [];
        $errors = 0;
        
        return $this->render('hangman/play.html.twig', [
            'difficulty' => $difficultyConfig['label'],
            'word' => $word,
            'foundLetters' => $foundLetters,
            'usedLetters' => $usedLetters,
            'errors' => $errors,
            'maxErrors' => 6
        ]);
    }

}
