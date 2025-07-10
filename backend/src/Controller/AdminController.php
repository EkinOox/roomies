<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Room;
use App\Repository\UserRepository;
use App\Repository\RoomRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    // === GESTION DES UTILISATEURS ===

    #[Route('/users', name: 'admin_users_list', methods: ['GET'])]
    public function getUsers(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();

        $userData = [];
        foreach ($users as $user) {
            $userData[] = [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'avatar' => $user->getAvatar(),
                'createdAt' => $user->getCreatedAt()?->format('c'),
                'lastActive' => $user->getLastActive()?->format('c')
            ];
        }

        return $this->json($userData);
    }

    #[Route('/users', name: 'admin_users_create', methods: ['POST'])]
    public function createUser(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setUsername($data['username'] ?? '');
        $user->setEmail($data['email'] ?? '');
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        $user->setAvatar($data['avatar'] ?? '/img/avatar/1.png');
        $user->setCreatedAt(new \DateTimeImmutable());

        if (!empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        // Validation
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $violations = [];
            foreach ($errors as $error) {
                $violations[] = [
                    'propertyPath' => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ];
            }
            return $this->json(['violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'avatar' => $user->getAvatar(),
            'createdAt' => $user->getCreatedAt()?->format('c'),
            'lastActive' => null
        ], Response::HTTP_CREATED);
    }

    #[Route('/users/{id}', name: 'admin_users_update', methods: ['PUT'])]
    public function updateUser(int $id, Request $request, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['roles'])) {
            $user->setRoles($data['roles']);
        }
        if (isset($data['avatar'])) {
            $user->setAvatar($data['avatar']);
        }

        // Validation
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $violations = [];
            foreach ($errors as $error) {
                $violations[] = [
                    'propertyPath' => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ];
            }
            return $this->json(['violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'avatar' => $user->getAvatar(),
            'createdAt' => $user->getCreatedAt()?->format('c'),
            'lastActive' => $user->getLastActive()?->format('c')
        ]);
    }

    #[Route('/users/{id}', name: 'admin_users_delete', methods: ['DELETE'])]
    public function deleteUser(int $id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        if (!$user) {
            return $this->json(['message' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier qu'on ne supprime pas le dernier admin
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $adminCount = $userRepository->countByRole('ROLE_ADMIN');
            if ($adminCount <= 1) {
                return $this->json([
                    'message' => 'Impossible de supprimer le dernier administrateur'
                ], Response::HTTP_CONFLICT);
            }
        }

        try {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return $this->json(['message' => 'Utilisateur supprimé'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Impossible de supprimer cet utilisateur (données liées)'
            ], Response::HTTP_CONFLICT);
        }
    }

    // === GESTION DES ROOMS ===

    #[Route('/rooms', name: 'admin_rooms_list', methods: ['GET'])]
    public function getRooms(RoomRepository $roomRepository): JsonResponse
    {
        $rooms = $roomRepository->findAllWithDetails();

        $roomData = [];
        foreach ($rooms as $room) {
            $roomData[] = [
                'id' => $room->getId(),
                'name' => $room->getName(),
                'description' => $room->getDescription(),
                'maxPlayers' => $room->getMaxPlayers(),
                'currentPlayers' => count($room->getPlayers()),
                'status' => $room->getStatus(),
                'createdAt' => $room->getCreatedAt()?->format('c'),
                'game' => [
                    'id' => $room->getGame()->getId(),
                    'name' => $room->getGame()->getName(),
                    'image' => $room->getGame()->getImage()
                ],
                'owner' => [
                    'username' => $room->getOwner()->getUsername(),
                    'avatar' => $room->getOwner()->getAvatar()
                ]
            ];
        }

        return $this->json($roomData);
    }

    #[Route('/rooms', name: 'admin_rooms_create', methods: ['POST'])]
    public function createRoom(Request $request, GameRepository $gameRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $game = $gameRepository->find($data['gameId'] ?? 0);
        if (!$game) {
            return $this->json(['message' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $room = new Room();
        $room->setName($data['name'] ?? '');
        $room->setDescription($data['description'] ?? '');
        $room->setMaxPlayers($data['maxPlayers'] ?? 2);
        $room->setGame($game);
        $room->setGameType($game->getName()); // Utiliser le nom du jeu comme type
        $room->setOwner($this->getUser()); // L'admin qui crée
        $room->setStatus('waiting');
        $room->setCreatedAt(new \DateTimeImmutable());
        
        // Génération automatique du slug
        $slug = $this->generateSlug($data['name'] ?? '');
        $room->setSlug($slug);

        // Validation
        $errors = $this->validator->validate($room);
        if (count($errors) > 0) {
            $violations = [];
            foreach ($errors as $error) {
                $violations[] = [
                    'propertyPath' => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ];
            }
            return $this->json(['violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->persist($room);
        $this->entityManager->flush();

        return $this->json([
            'id' => $room->getId(),
            'name' => $room->getName(),
            'description' => $room->getDescription(),
            'maxPlayers' => $room->getMaxPlayers(),
            'currentPlayers' => 0,
            'status' => $room->getStatus(),
            'createdAt' => $room->getCreatedAt()?->format('c'),
            'game' => [
                'id' => $room->getGame()->getId(),
                'name' => $room->getGame()->getName(),
                'image' => $room->getGame()->getImage()
            ],
            'owner' => [
                'username' => $room->getOwner()->getUsername(),
                'avatar' => $room->getOwner()->getAvatar()
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/rooms/{id}', name: 'admin_rooms_update', methods: ['PUT'])]
    public function updateRoom(int $id, Request $request, RoomRepository $roomRepository, GameRepository $gameRepository): JsonResponse
    {
        $room = $roomRepository->find($id);
        if (!$room) {
            return $this->json(['message' => 'Room non trouvée'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $room->setName($data['name']);
            // Régénérer le slug si le nom change
            $slug = $this->generateSlug($data['name'], $room);
            $room->setSlug($slug);
        }
        if (isset($data['description'])) {
            $room->setDescription($data['description']);
        }
        if (isset($data['maxPlayers'])) {
            $room->setMaxPlayers($data['maxPlayers']);
        }
        if (isset($data['gameId'])) {
            $game = $gameRepository->find($data['gameId']);
            if (!$game) {
                return $this->json(['message' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
            }
            $room->setGame($game);
            $room->setGameType($game->getName()); // Mettre à jour le type de jeu
        }

        // Validation
        $errors = $this->validator->validate($room);
        if (count($errors) > 0) {
            $violations = [];
            foreach ($errors as $error) {
                $violations[] = [
                    'propertyPath' => $error->getPropertyPath(),
                    'message' => $error->getMessage()
                ];
            }
            return $this->json(['violations' => $violations], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->entityManager->flush();

        return $this->json([
            'id' => $room->getId(),
            'name' => $room->getName(),
            'description' => $room->getDescription(),
            'maxPlayers' => $room->getMaxPlayers(),
            'currentPlayers' => count($room->getPlayers()),
            'status' => $room->getStatus(),
            'createdAt' => $room->getCreatedAt()?->format('c'),
            'game' => [
                'id' => $room->getGame()->getId(),
                'name' => $room->getGame()->getName(),
                'image' => $room->getGame()->getImage()
            ],
            'owner' => [
                'username' => $room->getOwner()->getUsername(),
                'avatar' => $room->getOwner()->getAvatar()
            ]
        ]);
    }

    #[Route('/rooms/{id}', name: 'admin_rooms_delete', methods: ['DELETE'])]
    public function deleteRoom(int $id, RoomRepository $roomRepository): JsonResponse
    {
        $room = $roomRepository->find($id);
        if (!$room) {
            return $this->json(['message' => 'Room non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier qu'il n'y a pas de partie en cours
        if ($room->getStatus() === 'active') {
            return $this->json([
                'message' => 'Impossible de supprimer une room avec une partie en cours'
            ], Response::HTTP_CONFLICT);
        }

        try {
            $this->entityManager->remove($room);
            $this->entityManager->flush();
            return $this->json(['message' => 'Room supprimée'], Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json([
                'message' => 'Impossible de supprimer cette room'
            ], Response::HTTP_CONFLICT);
        }
    }
    
    /**
     * Génère un slug unique à partir d'un nom
     */
    private function generateSlug(string $name, ?Room $excludeRoom = null): string
    {
        // Convertir en minuscules et remplacer les caractères spéciaux
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // S'assurer que le slug n'est pas vide
        if (empty($slug)) {
            $slug = 'room';
        }
        
        // Vérifier l'unicité et ajouter un suffixe si nécessaire
        $originalSlug = $slug;
        $counter = 1;
        
        $roomRepository = $this->entityManager->getRepository(Room::class);
        do {
            $existingRoom = $roomRepository->findOneBy(['slug' => $slug]);
            
            // Si pas de room trouvée ou si c'est la room qu'on exclut, le slug est valide
            if (!$existingRoom || ($excludeRoom && $existingRoom->getId() === $excludeRoom->getId())) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        } while (true);
        
        return $slug;
    }
}
