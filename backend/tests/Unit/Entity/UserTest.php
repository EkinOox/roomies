<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour l'entité User
 * Teste les getters, setters et la logique métier de base
 */
class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        // Initialise une nouvelle instance avant chaque test
        $this->user = new User();
    }

    /**
     * Test de la création d'un utilisateur avec les données de base
     */
    public function testUserCreation(): void
    {
        // Arrange & Act - Configuration des données utilisateur
        $this->user->setEmail('test@example.com');
        $this->user->setUsername('testuser');
        $this->user->setPassword('hashedpassword');
        $this->user->setAvatar('img/avatar/1.png');
        $this->user->setRoles(['ROLE_USER']);

        // Assert - Vérification que les données sont bien stockées
        $this->assertEquals('test@example.com', $this->user->getEmail());
        $this->assertEquals('testuser', $this->user->getUsername());
        $this->assertEquals('hashedpassword', $this->user->getPassword());
        $this->assertEquals('img/avatar/1.png', $this->user->getAvatar());
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
    }

    /**
     * Test de la méthode getUserIdentifier (utilisée par Symfony Security)
     */
    public function testGetUserIdentifier(): void
    {
        // Arrange
        $email = 'user@test.com';
        $this->user->setEmail($email);

        // Act & Assert
        $this->assertEquals($email, $this->user->getUserIdentifier());
    }

    /**
     * Test des réles par défaut
     */
    public function testDefaultRoles(): void
    {
        // Act - Les réles par défaut devraient inclure ROLE_USER
        $roles = $this->user->getRoles();

        // Assert
        $this->assertContains('ROLE_USER', $roles);
        $this->assertIsArray($roles);
    }

    /**
     * Test de validation d'email invalide
     */
    public function testInvalidEmail(): void
    {
        // Arrange
        $invalidEmail = 'invalid-email';
        
        // Act
        $this->user->setEmail($invalidEmail);
        
        // Assert - L'email est stocké tel quel (la validation se fait au niveau Symfony)
        $this->assertEquals($invalidEmail, $this->user->getEmail());
    }

    /**
     * Test de la gestion des favoris (collection)
     */
    public function testFavorisCollection(): void
    {
        // Assert - La collection de favoris doit étre initialisée
        $this->assertNotNull($this->user->getFavoris());
        $this->assertCount(0, $this->user->getFavoris());
    }

    /**
     * Test de la date de création automatique
     */
    public function testCreatedAtAutoSet(): void
    {
        // Arrange
        $now = new \DateTimeImmutable();
        $this->user->setCreatedAt($now);

        // Act & Assert
        $this->assertEquals($now, $this->user->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $this->user->getCreatedAt());
    }
}