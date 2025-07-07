<?php

namespace App\DataFixtures;

use App\Entity\Game;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class GameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $games = [
            [
                'name' => '2048',
                'image' => '/img/games/2048.png',
                'description' => 'Un jeu de puzzle où vous combinez des tuiles pour atteindre 2048.',
            ],
            [
                'name' => 'morpion',
                'image' => '/img/games/morpion.png',
                'description' => 'Le célèbre jeu de Tic-Tac-Toe pour deux joueurs.',
            ],
            [
                'name' => 'echecs',
                'image' => '/img/games/echecs.png',
                'description' => 'Jeu de stratégie classique opposant deux joueurs sur un damier.',
            ],
        ];

        foreach ($games as $g) {
            $game = new Game();
            $game->setName($g['name']);
            $game->setImage($g['image']);
            $game->setDescription($g['description']);
            $manager->persist($game);
        }

        $manager->flush();
    }
}


// Commande : php bin/console doctrine:fixtures:load --append