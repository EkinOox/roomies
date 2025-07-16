<?php

namespace App\DataFixtures;

// Importation de l'entité Game à insérer en base
use App\Entity\Game;
// Classe de base pour les fixtures de Doctrine
use Doctrine\Bundle\FixturesBundle\Fixture;
// Interface utilisée pour l'injection du gestionnaire d'entités
use Doctrine\Persistence\ObjectManager;

// Définition de la classe de fixture pour insérer des jeux dans la base de données
class GameFixtures extends Fixture
{
    // Méthode appelée automatiquement pour insérer les données
    public function load(ObjectManager $manager): void
    {
        // Tableau contenant les données des jeux à insérer
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

        // Boucle sur chaque jeu défini dans le tableau
        foreach ($games as $g) {
            // Création d'un nouvel objet Game
            $game = new Game();
            // Définition du nom du jeu
            $game->setName($g['name']);
            // Définition du chemin de l'image
            $game->setImage($g['image']);
            // Définition de la description
            $game->setDescription($g['description']);
            // On indique à Doctrine qu'on souhaite persister cet objet en base
            $manager->persist($game);
        }

        // Exécution de l'enregistrement en base de données
        $manager->flush();
    }
}

// Commande : php bin/console doctrine:fixtures:load --append