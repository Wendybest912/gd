<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as assert;

class User
{
    private $id;

    #[Assert\NotBlank(message: 'Le pseudo est obligatoire')]
    #[Assert\Length(
        min: 3,
        max: 50,
        minMessage: 'Le pseudo est trop court',
        maxMessage: 'Le pseudo est trop long'
    )]
    private  $username = null;

    #[Assert\NotBlank(message: 'L\'email est obligatoire')]
    #[Assert\Email(message: 'L\'email "{{ value }}" n\'est pas valide')]
    private  $email = null;

    private  $password = null;


    #[Assert\PositiveOrZero(message: 'Le nombre de parties ne peut pas être négatif')]
    private int $gamesPlayed = 0;

    #[Assert\PositiveOrZero(message: 'Le nombre de victoires ne peut pas être négatif')]
    private int $gamesWon = 0;


     public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getGamesPlayed(): int
    {
        return $this->gamesPlayed;
    }

    public function setGamesPlayed(int $gamesPlayed): static
    {
        $this->gamesPlayed = $gamesPlayed;
        return $this;
    }

    public function addGamePlayed(): static
    {
        $this->gamesPlayed++;
        return $this;
    }

    public function getGamesWon(): int
    {
        return $this->gamesWon;
    }

    public function setGamesWon(int $gamesWon): static
    {
        $this->gamesWon = $gamesWon;
        return $this;
    }

    public function addGameWon(): static
    {
        $this->gamesWon++;
        return $this;
    }

}