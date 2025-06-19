-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : mysql
-- Généré le : jeu. 19 juin 2025 à 17:53
-- Version du serveur : 8.0.42
-- Version de PHP : 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `roomies`
--

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250521082600', '2025-06-16 09:29:15', 125),
('DoctrineMigrations\\Version20250618154835', '2025-06-18 15:49:03', 377),
('DoctrineMigrations\\Version20250618161147', '2025-06-18 16:12:16', 236),
('DoctrineMigrations\\Version20250618164642', '2025-06-18 16:46:52', 82),
('DoctrineMigrations\\Version20250619131436', '2025-06-19 13:14:38', 346),
('DoctrineMigrations\\Version20250619151116', '2025-06-19 15:11:41', 139),
('DoctrineMigrations\\Version20250619172748', '2025-06-19 17:27:54', 83);

-- --------------------------------------------------------

--
-- Structure de la table `friendship`
--

CREATE TABLE `friendship` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `friend_id` int NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `friendship`
--

INSERT INTO `friendship` (`id`, `user_id`, `friend_id`, `status`) VALUES
(1, 4, 2, 'accepted');

-- --------------------------------------------------------

--
-- Structure de la table `game`
--

CREATE TABLE `game` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `game`
--

INSERT INTO `game` (`id`, `name`, `image`, `description`) VALUES
(8, '2048', 'img/games/2048.png', 'Un jeu de puzzle où vous combinez des tuiles pour atteindre 2048.'),
(9, 'Morpion', 'img/games/morpion.png', 'Le célèbre jeu de Tic-Tac-Toe pour deux joueurs.'),
(10, 'echecs', 'img/games/echecs.png', 'Jeu de stratégie classique opposant deux joueurs sur un damier.');

-- --------------------------------------------------------

--
-- Structure de la table `room`
--

CREATE TABLE `room` (
  `id` int NOT NULL,
  `owner_id` int DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `game_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `game_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `room_user`
--

CREATE TABLE `room_user` (
  `room_id` int NOT NULL,
  `user_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `username`, `password`, `created_at`, `avatar`, `roles`) VALUES
(2, 'admin@admin.com', 'admintest', '$2y$13$LH6fjs6d97rcpUVEMcAEYeahnw8HRG/rGKbmaBSANAwYhtcyU6JUm', '2025-06-18 16:13:20', '/img/avatar/5.png', '[\"ROLE_USER\", \"ROLE_ADMIN\"]'),
(4, 'test@test.com', 'EkinOox', '$2y$13$SPqU3Zd7jSoCQ/Ctcxr.cuKwI8pVmeJrj3FBERyBenwlhmOwjDUgi', '2025-06-19 06:50:21', '/img/avatar/1.png', '[\"ROLE_USER\"]');

-- --------------------------------------------------------

--
-- Structure de la table `user_game`
--

CREATE TABLE `user_game` (
  `user_id` int NOT NULL,
  `game_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_game`
--

INSERT INTO `user_game` (`user_id`, `game_id`) VALUES
(2, 8),
(2, 9);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `friendship`
--
ALTER TABLE `friendship`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_7234A45FA76ED395` (`user_id`),
  ADD KEY `IDX_7234A45F6A5458E8` (`friend_id`);

--
-- Index pour la table `game`
--
ALTER TABLE `game`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_729F519B989D9B62` (`slug`),
  ADD KEY `IDX_729F519B7E3C61F9` (`owner_id`),
  ADD KEY `IDX_729F519BE48FD905` (`game_id`);

--
-- Index pour la table `room_user`
--
ALTER TABLE `room_user`
  ADD PRIMARY KEY (`room_id`,`user_id`),
  ADD KEY `IDX_EE973C2D54177093` (`room_id`),
  ADD KEY `IDX_EE973C2DA76ED395` (`user_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
  ADD UNIQUE KEY `UNIQ_8D93D649F85E0677` (`username`);

--
-- Index pour la table `user_game`
--
ALTER TABLE `user_game`
  ADD PRIMARY KEY (`user_id`,`game_id`),
  ADD KEY `IDX_59AA7D45A76ED395` (`user_id`),
  ADD KEY `IDX_59AA7D45E48FD905` (`game_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `friendship`
--
ALTER TABLE `friendship`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `game`
--
ALTER TABLE `game`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `room`
--
ALTER TABLE `room`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `friendship`
--
ALTER TABLE `friendship`
  ADD CONSTRAINT `FK_7234A45F6A5458E8` FOREIGN KEY (`friend_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_7234A45FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `room`
--
ALTER TABLE `room`
  ADD CONSTRAINT `FK_729F519B7E3C61F9` FOREIGN KEY (`owner_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_729F519BE48FD905` FOREIGN KEY (`game_id`) REFERENCES `game` (`id`);

--
-- Contraintes pour la table `room_user`
--
ALTER TABLE `room_user`
  ADD CONSTRAINT `FK_EE973C2D54177093` FOREIGN KEY (`room_id`) REFERENCES `room` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_EE973C2DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_game`
--
ALTER TABLE `user_game`
  ADD CONSTRAINT `FK_59AA7D45A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_59AA7D45E48FD905` FOREIGN KEY (`game_id`) REFERENCES `game` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
