<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api/games', name: 'api_games_')]
class GameController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(GameRepository $gameRepository): JsonResponse
    {
        $games = $gameRepository->findAll();
        return $this->json($games, 200, [], ['groups' => 'game:read']);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Game $game): JsonResponse
    {
        return $this->json($game, 200, [], ['groups' => 'game:read']);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $game = new Game();
        $game->setName($data['name'] ?? '');
        $game->setDescription($data['description'] ?? null);
        $game->setImage($data['image'] ?? null);

        $em->persist($game);
        $em->flush();

        return $this->json($game, 201, [], ['groups' => 'game:read']);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(Game $game, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) $game->setName($data['name']);
        if (isset($data['description'])) $game->setDescription($data['description']);
        if (isset($data['image'])) $game->setImage($data['image']);

        $em->flush();

        return $this->json($game, 200, [], ['groups' => 'game:read']);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(Game $game, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($game);
        $em->flush();

        return $this->json(['message' => 'Jeu supprimé.'], 200);
    }
}
