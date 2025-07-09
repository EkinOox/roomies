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

/**
 * Contrôleur d'authentification - Gère l'inscription et la connexion des utilisateurs
 */
final class AuthController extends AbstractController
{
    /**
     * Gère les requêtes CORS preflight (OPTIONS) pour les routes login et register
     * Nécessaire pour les appels AJAX cross-origin depuis le frontend Vue.js
     */
    #[Route('/login', methods: ['OPTIONS'])]
    #[Route('/register', methods: ['OPTIONS'])]
    public function options(): JsonResponse
    {
        // Retourne une réponse vide avec le statut 204 (No Content)
        // pour indiquer que la requête preflight CORS est autorisée
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Endpoint d'inscription - Crée un nouveau compte utilisateur
     */
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher, // Service pour hasher les mots de passe
        EntityManagerInterface $em // Service pour interagir avec la base de données
    ): JsonResponse {
        // Décode le JSON reçu du frontend en tableau PHP
        $data = json_decode($request->getContent(), true);

        // Récupère les données ou null si elles n'existent pas
        $email = $data['email'] ?? null;
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        // Validation : vérifie que tous les champs requis sont présents
        if (!$email || !$username || !$password) {
            return $this->json(['message' => 'Email, nom d\'utilisateur et mot de passe requis'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification unicité email : empêche la création de comptes avec des emails existants
        $existingEmail = $em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingEmail) {
            return $this->json(['message' => 'Email déjà utilisé'], Response::HTTP_BAD_REQUEST);
        }

        // Vérification unicité username : empêche la création de comptes avec des noms d'utilisateur existants
        $existingUsername = $em->getRepository(User::class)->findOneBy(['username' => $username]);
        if ($existingUsername) {
            return $this->json(['message' => 'Nom d\'utilisateur déjà utilisé'], Response::HTTP_BAD_REQUEST);
        }

        // Création de l'entité utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setUsername($username);
        // Hash du mot de passe pour la sécurité (jamais stocker en clair)
        $user->setPassword(
            $passwordHasher->hashPassword($user, $password)
        );
        // Avatar par défaut assigné à tous les nouveaux utilisateurs
        $user->setAvatar('img/avatar/10.png');
        // Rôle par défaut pour les permissions
        $user->setRoles(['ROLE_USER']);
        // Date de création automatique
        $user->setCreatedAt(new \DateTimeImmutable());

        // Sauvegarde en base de données
        $em->persist($user); // Prépare l'entité pour la sauvegarde
        $em->flush(); // Exécute la requête SQL INSERT

        return $this->json(['message' => 'Utilisateur créé'], Response::HTTP_CREATED);
    }

    /**
     * Endpoint de connexion - Authentifie un utilisateur et génère un token JWT
     */
    #[Route('/login', name: 'login_custom', methods: ['POST'])]
    public function login(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwtManager // Service pour créer des tokens JWT
    ): JsonResponse {
        // Décode le JSON reçu du frontend
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        // Recherche l'utilisateur par email dans la base de données
        $user = $em->getRepository(User::class)->findOneBy(['email' => $email]);

        // Vérifie si l'utilisateur existe ET si le mot de passe est correct
        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['message' => 'Identifiants invalides'], Response::HTTP_UNAUTHORIZED);
        }

        // Génère un token JWT personnalisé avec des données supplémentaires
        $token = $jwtManager->createFromPayload($user, [
            'id' => $user->getId(),           // ID de l'utilisateur pour l'identification
            'roles' => $user->getRoles(),     // Rôles pour l'autorisation
            'avatar' => $user->getAvatar(),   // Avatar pour l'affichage dans l'interface
            'email' => $user->getEmail(),     // Email pour la validation côté client
        ]);

        // Retourne le token au frontend pour l'authentification
        return $this->json([
            'token' => $token,
        ]);
    }
}
