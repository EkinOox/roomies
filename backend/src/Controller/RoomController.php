<?php
// src/Controller/RoomController.php

namespace App\Controller;

use App\Entity\Room;
use App\Entity\Game;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Contrôleur de gestion des salles de jeu (rooms)
 * Gère la création, suppression, liste et participation aux rooms
 * Toutes les routes sont préfixées par /api/rooms
 */
#[Route('/api/rooms')]
class RoomController extends AbstractController
{
    /**
     * Crée une nouvelle salle de jeu
     * L'utilisateur authentifié devient automatiquement le propriétaire
     */
    #[Route('', name: 'create_room', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger, // Service pour générer des slugs URL-friendly
        TokenStorageInterface $tokenStorage, // Service pour récupérer l'utilisateur connecté
        SerializerInterface $serializer // Service pour sérialiser les entités en JSON
    ): JsonResponse {
        // Décode les données JSON envoyées par le frontend
        $data = json_decode($request->getContent(), true);

        // Recherche le jeu par son ID dans la base de données
        $game = $em->getRepository(Game::class)->find($data['gameId']);
        if (!$game) {
            return $this->json(['error' => 'Jeu non trouvé'], 400);
        }

        // Récupère l'utilisateur authentifié depuis le token JWT
        /** @var User $owner */
        $owner = $tokenStorage->getToken()?->getUser();
        if (!$owner) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Création de la nouvelle room avec les données fournies
        $room = new Room();
        $room->setName($data['name']);
        $room->setSlug(strtolower($slugger->slug($data['name']))); // Génère un slug pour l'URL
        $room->setCreatedAt(new \DateTimeImmutable());
        $room->setGameType($game->getName()); // Utilise le nom du jeu trouvé
        $room->setGame($game);
        $room->setOwner($owner); // L'utilisateur connecté devient propriétaire
        $room->setMaxPlayers($data['maxPlayers']);

        // Sauvegarde en base de données
        $em->persist($room);
        $em->flush();

        // Retourne la room créée en JSON avec le statut 201 (Created)
        return new JsonResponse(
            $serializer->serialize($room, 'json', ['groups' => 'room:read']),
            201,
            [],
            true // Indique que le contenu est déjà en JSON
        );
    }

    /**
     * Supprime une room par son ID
     * Seul le propriétaire de la room peut la supprimer
     */
    #[Route('/{id}', name: 'delete_room', methods: ['DELETE'])]
    public function deleteRoom(int $id, EntityManagerInterface $em, TokenStorageInterface $tokenStorage): JsonResponse
    {
        // Recherche la room par son ID
        $room = $em->getRepository(Room::class)->find($id);

        if (!$room) {
            return $this->json(['error' => 'Room not found'], 404);
        }

        // Récupère l'utilisateur connecté
        /** @var User $currentUser */
        $currentUser = $tokenStorage->getToken()?->getUser();
        if (!$currentUser) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        // Vérifie que l'utilisateur connecté est le propriétaire de la room
        if ($room->getOwner()->getId() !== $currentUser->getId()) {
            return $this->json(['error' => 'Seul le propriétaire peut supprimer cette room'], 403);
        }

        // Supprime définitivement la room
        $em->remove($room);
        $em->flush();

        return $this->json(['message' => 'Room deleted'], 200);
    }

    /**
     * Liste toutes les rooms disponibles
     * Utilisé pour afficher les salles publiques
     */
    #[Route('', name: 'list_rooms', methods: ['GET'])]
    public function listRooms(EntityManagerInterface $em): JsonResponse
    {
        // Récupère toutes les rooms depuis la base
        $rooms = $em->getRepository(Room::class)->findAll();

        // Retourne avec le groupe de sérialisation pour contrôler les données exposées
        return $this->json($rooms, 200, [], ['groups' => 'room:read']);
    }

    /**
     * Affiche une room spécifique par son ID
     * Utilisé pour charger les détails d'une room avant de la rejoindre
     */
    #[Route('/{id}', name: 'get_room', methods: ['GET'])]
    public function getRoom(int $id, EntityManagerInterface $em): JsonResponse
    {
        // Recherche la room par son ID
        $room = $em->getRepository(Room::class)->find($id);

        if (!$room) {
            return $this->json(['error' => 'Room non trouvée'], 404);
        }

        // Retourne les détails de la room
        return $this->json($room, 200, [], ['groups' => 'room:read']);
    }

    #[Route('/{id}/join', name: 'join_room', methods: ['POST'])]
    public function joinRoom(
        int $id,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer
    ): JsonResponse {
        /** @var User|null $user */
        $user = $tokenStorage->getToken()?->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $room = $em->getRepository(Room::class)->find($id);
        if (!$room) {
            return $this->json(['error' => 'Room introuvable'], 404);
        }

        $isSpectator = false;

        if (!$room->getParticipants()->contains($user)) {
            if ($room->getParticipants()->count() >= $room->getMaxPlayers()) {
                // Ne pas l'ajouter aux participants, mais autoriser l'accès en tant que spectateur
                $isSpectator = true;
            } else {
                $room->addParticipant($user);

                // 🎮 LOGIQUE DE STATUT : Passer à "active" quand la room est pleine
                if ($room->getParticipants()->count() >= $room->getMaxPlayers() && $room->getStatus() === 'waiting') {
                    $room->setStatus('active');
                }

                $em->flush();
            }
        }

        return new JsonResponse(
            [
                'room' => json_decode($serializer->serialize($room, 'json', ['groups' => 'room:read'])),
                'spectator' => $isSpectator,
            ],
            200
        );
    }

    #[Route('/{id}/leave', name: 'leave_room', methods: ['POST'])]
    public function leaveRoom(
        int $id,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        /** @var User|null $user */
        $user = $tokenStorage->getToken()?->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $room = $em->getRepository(Room::class)->find($id);
        if (!$room) {
            return $this->json(['error' => 'Room introuvable'], 404);
        }

        if ($room->getParticipants()->contains($user)) {
            $room->removeParticipant($user);

            // 🎮 LOGIQUE DE STATUT : Revenir à "waiting" si plus assez de joueurs
            if ($room->getParticipants()->count() < $room->getMaxPlayers() && $room->getStatus() === 'active') {
                $room->setStatus('waiting');
            }

            // 🎮 Si plus personne, remettre à "waiting"
            if ($room->getParticipants()->count() === 0 && $room->getStatus() !== 'waiting') {
                $room->setStatus('waiting');
            }

            $em->flush();
        }

        return $this->json(['message' => 'Quitté avec succès'], 200);
    }

    /**
     * Marque une room comme terminée
     * Utilisé quand une partie se termine
     */
    #[Route('/{id}/finish', name: 'finish_room', methods: ['POST'])]
    public function finishRoom(
        int $id,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        /** @var User|null $user */
        $user = $tokenStorage->getToken()?->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $room = $em->getRepository(Room::class)->find($id);
        if (!$room) {
            return $this->json(['error' => 'Room introuvable'], 404);
        }

        // Seul le propriétaire ou un admin peut terminer la partie
        if ($room->getOwner()->getId() !== $user->getId() && !in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->json(['error' => 'Seul le propriétaire peut terminer cette room'], 403);
        }

        $room->setStatus('finished');
        $em->flush();

        return $this->json([
            'message' => 'Partie terminée avec succès',
            'status' => $room->getStatus()
        ], 200);
    }

    /**
     * Redémarre une room (finished → waiting)
     * Permet de relancer une nouvelle partie dans la même room
     */
    #[Route('/{id}/restart', name: 'restart_room', methods: ['POST'])]
    public function restartRoom(
        int $id,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ): JsonResponse {
        /** @var User|null $user */
        $user = $tokenStorage->getToken()?->getUser();
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $room = $em->getRepository(Room::class)->find($id);
        if (!$room) {
            return $this->json(['error' => 'Room introuvable'], 404);
        }

        // Seul le propriétaire peut redémarrer
        if ($room->getOwner()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Seul le propriétaire peut redémarrer cette room'], 403);
        }

        // On peut seulement redémarrer une room terminée
        if ($room->getStatus() !== 'finished') {
            return $this->json(['error' => 'Seule une room terminée peut être redémarrée'], 400);
        }

        $room->setStatus('waiting');
        $em->flush();

        return $this->json([
            'message' => 'Room redémarrée avec succès',
            'status' => $room->getStatus()
        ], 200);
    }
}
