<?php

namespace App\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Game;

/**
 * Tests d'intégration pour RoomController
 * Teste les endpoints de gestion des rooms avec authentification
 */
class RoomControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $testUser;
    private $testGame;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        
        // Création d'un utilisateur et d'un jeu de test
        $this->createTestData();
    }

    /**
     * Test de création d'une room avec authentification
     */
    public function testCreateRoomWithValidData(): void
    {
        // Arrange - Obtenir un token JWT valide
        $token = $this->getAuthToken();
        
        $roomData = [
            'name' => 'Test Room',
            'gameId' => $this->testGame->getId(),
            'maxPlayers' => 4
        ];

        // Act
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode($roomData)
        );

        // Assert
        $response = $this->client->getResponse();
        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            echo "Create room response: " . $response->getContent() . "\n";
            echo "Create room status: " . $response->getStatusCode() . "\n";
            echo "Token used: " . $token . "\n";
        }
        
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Test Room', $responseData['name']);
        $this->assertEquals(4, $responseData['maxPlayers']);
    }

    /**
     * Test d'accés refusé sans authentification
     */
    public function testCreateRoomWithoutAuth(): void
    {
        // Arrange
        $roomData = [
            'name' => 'Unauthorized Room',
            'game' => 'Morpion',
            'maxPlayers' => 2
        ];

        // Act
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($roomData)
        );

        // Assert
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test de listage des rooms
     */
    public function testListRooms(): void
    {
        // Arrange - Créer quelques rooms de test
        $token = $this->getAuthToken();
        $this->createTestRoom('Room 1');
        $this->createTestRoom('Room 2');

        // Act
        $this->client->request(
            'GET',
            '/api/rooms',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $token]
        );

        // Assert
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertGreaterThanOrEqual(2, count($responseData));
    }

    /**
     * Méthode utilitaire pour créer les données de test
     */
    private function createTestData(): void
    {
        // Nettoyer la base de données d'abord (ordre important � cause des cl�s �trang�res)
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM room');  // Supprimer d'abord les rooms
        $connection->executeStatement('DELETE FROM user');  // Puis les users
        $connection->executeStatement('DELETE FROM game'); // Enfin les games

        // Créer un utilisateur de test avec un mot de passe correctement haché
        $uniqueId = uniqid();
        $userData = [
            'email' => "test{$uniqueId}@room.com",
            'username' => "roomtester{$uniqueId}",
            'password' => 'password123'
        ];

        // Utiliser l'endpoint register pour créer l'utilisateur correctement
        $this->client->request(
            'POST',
            '/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        // Récupérer l'utilisateur créé
        $this->testUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $userData['email']]);

        // Créer un jeu de test
        $this->testGame = new Game();
        $this->testGame->setName('Morpion');
        $this->testGame->setDescription('Test game');
        $this->testGame->setImage('img/games/morpion.jpg');

        $this->entityManager->persist($this->testGame);
        $this->entityManager->flush();
    }

    /**
     * Méthode utilitaire pour obtenir un token d'authentification
     */
    private function getAuthToken(): string
    {
        // Simulation d'une connexion pour obtenir un token
        $this->client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => $this->testUser->getEmail(),
                'password' => 'password123' // Le mot de passe original utilisé lors de l'inscription
            ])
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        if (!isset($response['token'])) {
            // Debug en cas d'échec de login
            echo "Login response: " . $this->client->getResponse()->getContent() . "\n";
            echo "Login status: " . $this->client->getResponse()->getStatusCode() . "\n";
        }
        return $response['token'] ?? '';
    }

    /**
     * Méthode utilitaire pour créer une room de test
     */
    private function createTestRoom(string $name): void
    {
        $token = $this->getAuthToken();
        
        $this->client->request(
            'POST',
            '/api/rooms',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $token
            ],
            json_encode([
                'name' => $name,
                'gameId' => $this->testGame->getId(),
                'maxPlayers' => 4
            ])
        );
    }

    protected function tearDown(): void
    {
        // Fermer l'entity manager pour �viter les fuites m�moire
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
        
        // R�initialiser le kernel pour �viter les conflits
        static::$kernel = null;
        static::$booted = false;
        
        parent::tearDown();
    }
}