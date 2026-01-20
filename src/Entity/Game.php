<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraint as Assert;

#[ORM\Entity(repositoryClass: GameRepository::class)]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $difficulty = null;

    #[ORM\Column]
    private ?int $guessNumber = null;

    #[ORM\Column(length: 15)]
    private ?string $winOrLose = null;

    #[ORM\ManyToOne(
        targetEntity: User::class,
        inversedBy: "Game_played"
    )]
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getGuessNumber(): ?int
    {
        return $this->guessNumber;
    }

    public function setGuessNumber(int $guessNumber): static
    {
        $this->guessNumber = $guessNumber;

        return $this;
    }

    public function getWinOrLose(): ?string
    {
        return $this->winOrLose;
    }

    public function setWinOrLose(string $winOrLose): static
    {
        $this->winOrLose = $winOrLose;

        return $this;
    }
}
