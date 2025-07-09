<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Contréleur pour la gestion des jeux - CRUD complet
 * Toutes les routes sont préfixées par /api/games
 */
#[Route('/api/games', name: 'api_games_')]
class GameController extends AbstractController
{
    /**
     * Liste tous les jeux disponibles
     * Accessible é tous les utilisateurs authentifiés
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(GameRepository $gameRepository): JsonResponse
    {
        // Récupére tous les jeux depuis la base de données
        $games = $gameRepository->findAll();
        
        // Retourne les jeux en JSON avec le groupe de sérialisation 'game:read'
        // pour contréler quels champs sont exposés é l'API
        return $this->json($games, 200, [], ['groups' => 'game:read']);
    }

    /**
     * Affiche un jeu spécifique par son ID
     * Symfony injecte automatiquement l'entité Game gréce é l'ID dans l'URL
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Game $game): JsonResponse
    {
        // Retourne le jeu en JSON avec les données publiques
        return $this->json($game, 200, [], ['groups' => 'game:read']);
    }

    /**
     * Crée un nouveau jeu
     * Accessible uniquement aux administrateurs (é sécuriser)
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décode le JSON reéu du frontend
        $data = json_decode($request->getContent(), true);

        // Crée une nouvelle instance de jeu
        $game = new Game();
        
        // Définit les propriétés avec les données reéues ou des valeurs par défaut
        $game->setName($data['name'] ?? '');
        $game->setDescription($data['description'] ?? null);
        $game->setImage($data['image'] ?? null);

        // Sauvegarde en base de données
        $em->persist($game); // Prépare l'entité pour la sauvegarde
        $em->flush(); // Exécute la requéte INSERT

        // Retourne le jeu créé avec le statut 201 (Created)
        return $this->json($game, 201, [], ['groups' => 'game:read']);
    }

    /**
     * Met é jour un jeu existant
     * Symfony injecte automatiquement l'entité Game é partir de l'ID
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Game $game, Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Décode les nouvelles données
        $data = json_decode($request->getContent(), true);

        // Met é jour seulement les champs fournis (update partiel)
        if (isset($data['name'])) $game->setName($data['name']);
        if (isset($data['description'])) $game->setDescription($data['description']);
        if (isset($data['image'])) $game->setImage($data['image']);

        // Sauvegarde les modifications (pas besoin de persist car l'entité existe déjé)
        $em->flush();

        // Retourne le jeu mis é jour
        return $this->json($game, 200, [], ['groups' => 'game:read']);
    }

    /**
     * Supprime un jeu
     * Attention : suppression définitive, é sécuriser pour les admins uniquement
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Game $game, EntityManagerInterface $em): JsonResponse
    {
        // Supprime l'entité de la base de données
        $em->remove($game);
        $em->flush(); // Exécute la requéte DELETE

        // Confirme la suppression avec un message
        return $this->json(['message' => 'Jeu supprimé.'], 200);
    }
}
