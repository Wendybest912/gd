<?php

namespace App\Controller\Api;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Word
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function getRandomWord(int $minLength, int $maxLength): string
    {
        $fallbackWords = [
            'CHAT', 'PAIN', 'LUNE', 'ROSE', 'MAISON', 'JARDIN',
            'SOLEIL', 'CHOCOLAT', 'PAPILLON', 'ORDINATEUR',
            'DEVELOPPEMENT', 'ENVIRONNEMENT', 'ADMINISTRATION',
            'ANTICONSTITUTIONNELLEMENT', 'ELECTROENCEPHALOGRAMME'
        ];

        try {
            for ($i = 0; $i < 10; $i++) {
                $response = $this->httpClient->request(
                    'GET',
                    'https://random-word-api.herokuapp.com/word?lang=fr&number=20'
                );

                $words = $response->toArray();

                foreach ($words as $word) {
                    $word = strtoupper($this->removeAccents($word));
                    $length = strlen($word);

                    if ($length >= $minLength && $length <= $maxLength) {
                        return $word;
                    }
                }
            }
        } catch (\Exception $e) {
        }

        $filtered = array_filter($fallbackWords, function ($word) use ($minLength, $maxLength) {
            $length = strlen($word);
            return $length >= $minLength && $length <= $maxLength;
        });

        if (empty($filtered)) {
            return 'SYMFONY';
        }

        return $filtered[array_rand($filtered)];
    }

    private function removeAccents(string $string): string
    {
        $search  = ['à','â','ä','é','è','ê','ë','ï','î','ô','ö','ù','û','ü','ÿ','ç',
                     'À','Â','Ä','É','È','Ê','Ë','Ï','Î','Ô','Ö','Ù','Û','Ü','Ÿ','Ç'];
        $replace = ['A','A','A','E','E','E','E','I','I','O','O','U','U','U','Y','C',
                     'A','A','A','E','E','E','E','I','I','O','O','U','U','U','Y','C'];

        return str_replace($search, $replace, $string);
    }
}