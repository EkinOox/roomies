<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Routing\Attribute\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class AuthController extends AbstractController
{

    #[Route('/login', methods: ['OPTIONS'])]
    #[Route('/register', methods: ['OPTIONS'])]
    public function options(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $email = $data['email'] ?? null;
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$username || !$password) {
            return $this->json(['message' => 'Email, nom d\'utilisateur et mot de passe requis'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si un utilisateur existe déjà avec cet email
        $existingEmail = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingEmail) {
            return $this->json(['message' => 'Email déjà utilisé'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie si un utilisateur existe déjà avec ce nom d'utilisateur
        $existingUsername = $em->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUsername) {
            return $this->json(['message' => 'Nom d\'utilisateur déjà utilisé'], Response::HTTP_BAD_REQUEST);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        $user->setPassword(
            $passwordHasher->hashPassword($user, $password)
        );
        $user->setAvatar('img/avatar/10.png');
        $user->setRoles(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $em->persist($user);
        $em->flush();

        return $this->json(['message' => 'Utilisateur cr��'], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login_custom', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Identifiants invalides'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $jwtManager->createFromPayload($user, [
            'id' => $user->getId(),
            'roles' => $user->getRoles(),
            'avatar' => $user->getAvatar(),
            'email' => $user->getEmail(),
        ]);

        return $this->json([
            'token' => $token,
        ]);
    }
}
