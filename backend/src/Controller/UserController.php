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

/**
 * Contrôleur de gestion des utilisateurs - Profil, favoris, mise à jour
 * Toutes les routes sont préfixées par /api/users et protégées par authentification JWT
 */
#[Route('/api/users', name: 'api_users_')]
final class UserController extends AbstractController
{
    /**
     * Liste tous les utilisateurs (pour administration ou recherche)
     * Attention : à sécuriser selon les besoins métier
     */
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findAll(), 200, [], ['groups' => 'user:read']);
    }

    /**
     * Récupère le profil de l'utilisateur connecté
     * Utilisé pour afficher les informations personnelles dans l'interface
     */
    #[Route('/profile', name: 'api_me', methods: ['GET'])]
    public function Profile(Security $security): JsonResponse
    {
        // Récupère l'utilisateur connecté depuis le token JWT
        $user = $security->getUser();

        if (!$user instanceof User) {
            return $this->json(['error' => 'Utilisateur non connecté'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'avatar' => $user->getAvatar(),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/update', name: 'update', methods: ['POST'])]
    public function update(
        Request $request,
        Security $security,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager // Service pour créer des tokens JWT
    ): JsonResponse {
        // Récupère l'utilisateur connecté
        $user = $security->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Décode les nouvelles données envoyées
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

        // Générer un nouveau token à chaque fois qu'il y a une modification
        // pour que le frontend ait les données à jour (avatar, email, etc.)
        $token = null;
        if ($hasChanged) {
            $token = $jwtManager->createFromPayload($user, [
                'id' => $user->getId(),
                'roles' => $user->getRoles(),
                'avatar' => $user->getAvatar(),
                'email' => $user->getEmail(),
            ]);
        }

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'avatar' => $user->getAvatar(),
                'roles' => $user->getRoles(),
            ],
            'token' => $token,
        ], 200);
    }

    /**
     * Liste les jeux favoris de l'utilisateur connecté
     * Utilisé pour afficher la section "Mes Favoris" du profil
     */
    #[Route('/favorites', name: 'favorite_list', methods: ['GET'])]
    public function myFavorites(Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            return $this->json(['message' => 'Non authentifié'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Récupère les jeux favoris de l'utilisateur
        $favorites = $user->getFavoris();

        // Retourne la liste des favoris
        return $this->json($favorites, 200, [], ['groups' => 'game:read']);
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

        if (!$user->getFavoris()->contains($game)) {
            $user->addFavori($game);
            $em->flush();
        }

        return $this->json($game, 200, [], ['groups' => 'game:read']);
    }

    // Annotation de route : définit une route HTTP DELETE sur /favorites/{id}
    // Elle appelle cette méthode pour retirer un jeu des favoris d'un utilisateur connecté
    #[Route('/favorites/{id}', name: 'remove_favorite', methods: ['DELETE'])]
    public function removeFavorite(
        Security $security,                   // Service pour accéder à l'utilisateur connecté
        GameRepository $gameRepository,      // Repository pour accéder aux jeux dans la base
        int $id,                              // Identifiant du jeu à retirer des favoris (passé dans l'URL)
        EntityManagerInterface $em           // Gestionnaire d'entité Doctrine pour effectuer des opérations en base
    ): JsonResponse {

        // Récupération de l'utilisateur actuellement connecté
        $user = $security->getUser();

        // Vérification que l'utilisateur est bien authentifié et de type User
        if (!$user instanceof User) {
            return $this->json(['message' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        // Recherche du jeu correspondant à l'identifiant passé en paramètre
        $game = $gameRepository->find($id);

        // Si aucun jeu n'est trouvé, on retourne une erreur 404
        if (!$game) {
            return $this->json(['message' => 'Jeu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        // Vérifie si le jeu est bien dans la liste des favoris de l'utilisateur
        if ($user->getFavoris()->contains($game)) {
            // Si oui, on le retire des favoris
            $user->removeFavori($game);
            // On sauvegarde les modifications en base de données
            $em->flush();
        }

        // On retourne une réponse JSON indiquant que le jeu a bien été retiré des favoris
        return $this->json(['message' => 'Jeu retiré des favoris'], Response::HTTP_OK);
    }
}
