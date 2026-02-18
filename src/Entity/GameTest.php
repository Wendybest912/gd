<?php

namespace App\Tests\Entity;

use App\Entity\Game;
use PHPUnit\Framework\TestCase;

class GameTest extends TestCase
{
    public function testDifficulty(): void
    {
        $game = new Game();
        $game->setDifficulty('easy');

        $this->assertEquals('easy', $game->getDifficulty());
    }

    public function testGuessNumber(): void
    {
        $game = new Game();
        $game->setGuessNumber(5);

        $this->assertEquals(5, $game->getGuessNumber());
    }

}