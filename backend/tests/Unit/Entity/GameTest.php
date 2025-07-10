<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Game;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité Game
 * Teste la logique métier des jeux
 */
class GameTest extends TestCase
{
    private Game $game;

    protected function setUp(): void
    {
        $this->game = new Game();
    }

    /**
     * Test de création d'un jeu complet
     */
    public function testGameCreation(): void
    {
        // Arrange & Act
        $this->game->setName('Morpion');
        $this->game->setDescription('Le célébre jeu de Tic-Tac-Toe');
        $this->game->setImage('img/games/morpion.jpg');

        // Assert
        $this->assertEquals('Morpion', $this->game->getName());
        $this->assertEquals('Le célébre jeu de Tic-Tac-Toe', $this->game->getDescription());
        $this->assertEquals('img/games/morpion.jpg', $this->game->getImage());
    }

    /**
     * Test avec des valeurs vides (optionnelles)
     */
    public function testGameWithEmptyValues(): void
    {
        // Arrange & Act
        $this->game->setName('Jeu Simple');
        // Utilise des chaénes vides au lieu de null si l'entité ne l'accepte pas
        $this->game->setDescription('');
        $this->game->setImage('');

        // Assert
        $this->assertEquals('Jeu Simple', $this->game->getName());
        $this->assertEquals('', $this->game->getDescription());
        $this->assertEquals('', $this->game->getImage());
    }

    /**
     * Test avec description mais sans image
     */
    public function testGameWithDescriptionOnly(): void
    {
        // Arrange & Act
        $this->game->setName('Jeu Test');
        $this->game->setDescription('Description du jeu de test');
        // Laisse l'image vide
        $this->game->setImage('');

        // Assert
        $this->assertEquals('Jeu Test', $this->game->getName());
        $this->assertEquals('Description du jeu de test', $this->game->getDescription());
        $this->assertEquals('', $this->game->getImage());
    }

    /**
     * Test de la génération automatique d'ID
     */
    public function testGameIdIsNull(): void
    {
        // Assert - L'ID doit étre null avant persistance en base
        $this->assertNull($this->game->getId());
    }

    /**
     * Test de validation des types de données
     */
    public function testGameDataTypes(): void
    {
        // Test avec toutes les données
        $this->game->setName('Test Game');
        $this->game->setDescription('Test Description');
        $this->game->setImage('test/image.jpg');

        // Vérification des types
        $this->assertIsString($this->game->getName());
        $this->assertIsString($this->game->getDescription());
        $this->assertIsString($this->game->getImage());
    }

    /**
     * Test avec nom seulement (champs optionnels vides)
     */
    public function testGameWithNameOnly(): void
    {
        // Arrange & Act - Teste le minimum requis
        $this->game->setName('Jeu Minimal');
        
        // Si description et image sont optionnelles, teste avec valeurs par défaut
        $description = $this->game->getDescription();
        $image = $this->game->getImage();

        // Assert
        $this->assertEquals('Jeu Minimal', $this->game->getName());
        
        // Les champs optionnels peuvent étre null ou chaéne vide selon l'entité
        $this->assertTrue(
            is_null($description) || is_string($description),
            'Description devrait étre null ou string'
        );
        
        $this->assertTrue(
            is_null($image) || is_string($image),
            'Image devrait étre null ou string'
        );
    }
}