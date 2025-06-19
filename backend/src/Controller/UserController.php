<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use App\Repository\GameRepository;

#[Route('/api/users', name: 'api_users_')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findAll(), 200, [], ['groups' => 'user:read']);
    }

    #[Route('/profile', name: 'api_me', methods: ['GET'])]
    public function Profile(Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'username' => $user->getUserIdentifier(), // ou getUsername() si redéfini
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    #[Route('/update', name: 'update', methods: ['POST'])]
    public function update(
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $user = $security->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Non authentifiÃ©'], 401);
        }

        $data = json_decode($request->getContent(), true);

        $username = $data['username'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;
        $avatarPath = $data['avatar'] ?? null;

        $hasChanged = false;
        $usernameChanged = false;

        if ($username && $username !== $user->getUsername()) {
            $user->setUsername($username);
            $hasChanged = true;
            $usernameChanged = true;
        }

        if ($email && $email !== $user->getEmail()) {
            $emailConstraint = new Email();
            $emailConstraint->message = 'Email invalide.';
            $errors = $validator->validate($email, $emailConstraint);
            if (count($errors) > 0) {
                return new JsonResponse(['message' => (string) $errors], 400);
            }
            $user->setEmail($email);
            $hasChanged = true;
        }

        if ($password) {
            $hashed = $passwordHasher->hashPassword($user, $password);
            $user->setPassword($hashed);
            $hasChanged = true;
        }

        if ($avatarPath && $avatarPath !== $user->getAvatar()) {
            $user->setAvatar($avatarPath);
            $hasChanged = true;
        }

        if ($hasChanged) {
            $em->persist($user);
            $em->flush();
        }

        // GÃ©nÃ©rer un nouveau token si le username a changÃ©
        $token = null;
        if ($usernameChanged) {
            $token = $jwtManager->create($user);
        }

        return $this->json([
            'user' => $user,
            'token' => $token,
        ], 200, [], ['groups' => 'user:read']);
    }
    #[Route('/favorites/{id}', name: 'add_favorite', methods: ['POST'])]
    public function addFavorite(
        Security $security,
        GameRepository $gameRepository,
        int $id,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $game = $gameRepository->find($id);
        if (!$game) {
            return $this->json(['message' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $user->addFavori($game);
        $em->flush();

        return $this->json(['message' => 'Jeu ajouté aux favoris'], Response::HTTP_OK);
    }

    #[Route('/favorites/{id}', name: 'remove_favorite', methods: ['DELETE'])]
    public function removeFavorite(
        Security $security,
        GameRepository $gameRepository,
        int $id,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $security->getUser();
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        $game = $gameRepository->find($id);
        if (!$game) {
            return $this->json(['message' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $user->removeFavori($game);
        $em->flush();

        return $this->json(['message' => 'Jeu retiré des favoris'], Response::HTTP_OK);
    }
}
