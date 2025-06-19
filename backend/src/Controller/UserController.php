<?php
namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/users', name: 'api_users_')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function list(UserRepository $userRepository): JsonResponse
    {
        return $this->json($userRepository->findAll(), 200, [], ['groups' => 'user:read']);
    }

    #[Route('/profile', name: 'profile', methods: ['GET'])]
    public function me(Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Non authentifié'], 401);
        }

        return $this->json($user, 200, [], ['groups' => 'user:read']);
    }

    #[Route('/add-friend/{id}', name: 'add_friend', methods: ['POST'])]
    public function addFriend(User $friend, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        if ($user === $friend) {
            return new JsonResponse(['message' => 'Impossible de s’ajouter soi-même en ami'], 400);
        }

        $user->addFriend($friend);
        $em->flush();

        return new JsonResponse(['message' => 'Ami ajouté avec succès']);
    }

    #[Route('/remove-friend/{id}', name: 'remove_friend', methods: ['POST'])]
    public function removeFriend(User $friend, EntityManagerInterface $em, Security $security): JsonResponse
    {
        $user = $security->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['message' => 'Unauthorized'], 401);
        }

        $user->removeFriend($friend);
        $em->flush();

        return new JsonResponse(['message' => 'Ami supprimé avec succès']);
    }
}
