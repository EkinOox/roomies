<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Tests d'intégration pour AuthController
 * Teste les endpoints d'authentification avec une vraie base de données de test
 */
class AuthControllerTest extends WebTestCase
{
    private $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // Crée un client de test pour simuler les requétes HTTP
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Nettoyer la base de données avant chaque test
        $this->cleanDatabase();
    }

    protected function tearDown(): void
    {
        // Nettoyer après chaque test
        $this->cleanDatabase();
        parent::tearDown();
    }

    /**
     * Nettoie la base de données de test
     */
    private function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM user');
        $connection->executeStatement('DELETE FROM room');
    }

    /**
     * Test d'inscription avec des données valides
     */
    public function testRegisterWithValidData(): void
    {
        // Arrange - Données d'inscription valides avec un nom unique
        $uniqueId = uniqid();
        $userData = [
            'email' => "test{$uniqueId}@example.com",
            'username' => "testuser{$uniqueId}",
            'password' => 'password123'
        ];

        // Act - Envoi de la requéte d'inscription
        $this->client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        // Assert - Vérification de la réponse
        $response = $this->client->getResponse();
        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            echo "Response content: " . $response->getContent() . "\n";
        }
        
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Utilisateur créé', $responseData['message']);
    }

    /**
     * Test d'inscription avec des données valides
     */
    public function testRegisterWithValidData(): void
    {
        // Arrange - Données d'inscription valides avec un nom unique
        $uniqueId = uniqid();
        $userData = [
            'email' => "test{$uniqueId}@example.com",
            'username' => "testuser{$uniqueId}",
            'password' => 'password123'
        ];

        // Act - Envoi de la requéte d'inscription
        $this->client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        // Assert - Vérification de la réponse
        $response = $this->client->getResponse();
        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            echo "Response content: " . $response->getContent() . "\n";
        }
        
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Utilisateur créé', $responseData['message']);
    }

    /**
     * Test d'inscription avec des données manquantes
     */
    public function testRegisterWithMissingData(): void
    {
        // Arrange - Données incomplétes
        $userData = [
            'email' => 'test@example.com',
            // username et password manquants
        ];

        // Act
        $this->client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        // Assert
        $response = $this->client->getResponse();
        if ($response->getStatusCode() !== Response::HTTP_BAD_REQUEST) {
            echo "Response content: " . $response->getContent() . "\n";
        }
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    /**
     * Test de connexion avec des identifiants valides
     */
    public function testLoginWithValidCredentials(): void
    {
        // Arrange - Créer d'abord un utilisateur avec un ID unique
        $uniqueId = uniqid();
        $this->createTestUser("login{$uniqueId}@test.com", "loginuser{$uniqueId}", 'password123');

        $loginData = [
            'email' => "login{$uniqueId}@test.com",
            'password' => 'password123'
        ];

        // Act
        $this->client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        // Assert
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertNotEmpty($responseData['token']);
    }

    /**
     * Test de connexion avec des identifiants invalides
     */
    public function testLoginWithInvalidCredentials(): void
    {
        // Arrange
        $loginData = [
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpassword'
        ];

        // Act
        $this->client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        // Assert
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test de connexion avec des identifiants valides
     */
    public function testLoginWithValidCredentials(): void
    {
        // Arrange - Créer d'abord un utilisateur avec un ID unique
        $uniqueId = uniqid();
        $this->createTestUser("login{$uniqueId}@test.com", "loginuser{$uniqueId}", 'password123');

        $loginData = [
            'email' => "login{$uniqueId}@test.com",
            'password' => 'password123'
        ];

        // Act
        $this->client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        // Assert
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $responseData);
        $this->assertNotEmpty($responseData['token']);
    }

    /**
     * Test de connexion avec des identifiants invalides
     */
    public function testLoginWithInvalidCredentials(): void
    {
        // Arrange
        $loginData = [
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpassword'
        ];

        // Act
        $this->client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        // Assert
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test des requétes CORS OPTIONS
     */
    public function testCorsOptionsRequest(): void
    {
        // Act
        $this->client->request('OPTIONS', '/login');

        // Assert
        $this->assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Méthode utilitaire pour créer un utilisateur de test
     */
    private function createTestUser(string $email, string $username, string $password): void
    {
        $userData = [
            'email' => $email,
            'username' => $username,
            'password' => $password
        ];

        $this->client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );
    }
}