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

#[Route('/api/rooms')]
class RoomController extends AbstractController
{
    #[Route('', name: 'create_room', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        TokenStorageInterface $tokenStorage,
        SerializerInterface $serializer
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $game = $em->getRepository(Game::class)->findOneBy(['name' => $data['game']]);
        if (!$game) {
            return $this->json(['error' => 'Jeu non trouvé'], 400);
        }

        /** @var User $owner */
        $owner = $tokenStorage->getToken()?->getUser();
        if (!$owner) {
            return $this->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $room = new Room();
        $room->setName($data['name']);
        $room->setSlug(strtolower($slugger->slug($data['name'])));
        $room->setCreatedAt(new \DateTimeImmutable());
        $room->setGameType($data['game']);
        $room->setGame($game);
        $room->setOwner($owner);
        $room->setMaxPlayers($data['maxPlayers']);

        $em->persist($room);
        $em->flush();

        return new JsonResponse(
            $serializer->serialize($room, 'json', ['groups' => 'room:read']),
            201,
            [],
            true
        );
    }

    #[Route('/{id}', name: 'delete_room', methods: ['DELETE'])]
    public function deleteRoom(int $id, EntityManagerInterface $em): JsonResponse
    {
        $room = $em->getRepository(Room::class)->find($id);

        if (!$room) {
            return $this->json(['error' => 'Room not found'], 404);
        }

        $em->remove($room);
        $em->flush();

        return $this->json(['message' => 'Room deleted'], 200);
    }

    #[Route('', name: 'list_rooms', methods: ['GET'])]
    public function listRooms(EntityManagerInterface $em): JsonResponse
    {
        $rooms = $em->getRepository(Room::class)->findAll();

        return $this->json($rooms, 200, [], ['groups' => 'room:read']);
    }

    #[Route('/{id}', name: 'get_room', methods: ['GET'])]
    public function getRoom(int $id, EntityManagerInterface $em): JsonResponse
    {
        $room = $em->getRepository(Room::class)->find($id);

        if (!$room) {
            return $this->json(['error' => 'Room non trouvée'], 404);
        }

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
            $em->flush();
        }

        return $this->json(['message' => 'Quitté avec succès'], 200);
    }
}
