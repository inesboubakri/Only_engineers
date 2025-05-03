-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 03 mai 2025 à 08:12
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `onlyengs`
--

-- --------------------------------------------------------

--
-- Structure de la table `networking_connections`
--

CREATE TABLE `networking_connections` (
  `id` int(11) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','rejected') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `networking_follows`
--

CREATE TABLE `networking_follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `networking_follows`
--

INSERT INTO `networking_follows` (`id`, `follower_id`, `following_id`, `created_at`) VALUES
(8, 2, 1, '2025-05-03 05:07:06'),
(10, 1, 2, '2025-05-03 06:01:17');

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `type` enum('follow','connection_request','connection_accepted','connection_rejected') NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `position` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `birthday` date NOT NULL,
  `about` text NOT NULL,
  `seeking` text NOT NULL,
  `experiences` text DEFAULT NULL,
  `educations` text DEFAULT NULL,
  `organizations` text DEFAULT NULL,
  `honors` text DEFAULT NULL,
  `courses` text DEFAULT NULL,
  `projects` text DEFAULT NULL,
  `languages` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `is_admin` tinyint(4) DEFAULT 0,
  `is_banned` tinyint(4) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `profile_picture`, `position`, `country`, `city`, `birthday`, `about`, `seeking`, `experiences`, `educations`, `organizations`, `honors`, `courses`, `projects`, `languages`, `skills`, `is_admin`, `is_banned`, `reset_token`, `reset_token_expires`, `created_at`, `updated_at`) VALUES
(1, 'fatma khelifi', 'fatmakhelifi312@gmail.com', '$2y$10$IpMqifUX1acmFDP1vlzziels1Axb6W9xoS/1NcxxXpxvcv5eKdFfK', 'profile_1746239648_7864.png', 'Cybersecurity Engineer', 'Germany', 'Frankfurt', '2007-05-02', 'About You\r\nTell us about yourself and your professional background. ', 'event', '[{\"title\":\"Network Engineer \",\"company\":\"Huwaeii\",\"start_date\":\"2025-05-27\",\"end_date\":\"\",\"current\":1,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"school\":\"Esprit School Of Engineering \",\"degree\":\"Master of Arts\",\"field\":\"Computer Science \",\"start_date\":\"2025-05-06\",\"end_date\":\"\",\"current\":1,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"name\":\"IEE\",\"position\":\"Vice President \",\"start_date\":\"2025-05-13\",\"end_date\":\"\",\"current\":1,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"name\":\"Dean\'s List\",\"issuer\":\"MIT\",\"date\":\"2025-05-20\",\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"title\":\"Machine learning Course\",\"provider\":\"Coursera\",\"start_date\":\"2025-05-19\",\"end_date\":\"2025-05-25\",\"current\":0,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"title\":\"Secure Campus Network Design\",\"provider\":\"MIT\",\"start_date\":\"2025-05-19\",\"end_date\":\"\",\"current\":1,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"name\":\"English \",\"proficiency\":\"Intermediate\"}]', '[{\"name\":\"Problem Solving & Critical Thinking\",\"level\":\"Advanced\"}]', 0, 0, NULL, NULL, '2025-05-03 02:34:08', '2025-05-03 02:34:08'),
(2, 'Yessmine Chouchane', 'Yessmine.Chouchane@esprit.tn', '$2y$10$erDBo.HUrIk6s4htbHYEJ.QxM.aC82o5qFUfrqnjHEI7garz/PH5a', 'profile_1746244779_2359.jpeg', 'nekhdem', 'Tunisia', 'Gabès', '2007-05-02', 'About You\r\nTell us about yourself and your professional background.', 'event', '[{\"title\":\"Network Engineer \",\"company\":\"Huwaeii\",\"start_date\":\"2025-05-21\",\"end_date\":\"2025-05-26\",\"current\":0,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"school\":\"Esprit School Of Engineering \",\"degree\":\"Bachelor of Engineering\",\"field\":\"Computer Science \",\"start_date\":\"2025-05-05\",\"end_date\":\"\",\"current\":1,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"name\":\"IEE\",\"position\":\"Vice President \",\"start_date\":\"2025-05-12\",\"end_date\":\"2025-05-25\",\"current\":0,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"name\":\"Dean\'s List\",\"issuer\":\"MIT\",\"date\":\"2025-05-15\",\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"title\":\"Machine learning Course\",\"provider\":\"Coursera\",\"start_date\":\"2025-05-21\",\"end_date\":\"2025-05-25\",\"current\":0,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"title\":\"Secure Campus Network Design\",\"provider\":\"MIT\",\"start_date\":\"2025-05-19\",\"end_date\":\"\",\"current\":1,\"description\":\"About You\\r\\nTell us about yourself and your professional background.\"}]', '[{\"name\":\"English \",\"proficiency\":\"Basic\"}]', '[{\"name\":\"jdjsdjdh\",\"level\":\"Advanced\"}]', 0, 0, NULL, NULL, '2025-05-03 03:59:39', '2025-05-03 03:59:39');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `networking_connections`
--
ALTER TABLE `networking_connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_connection` (`requester_id`,`receiver_id`),
  ADD KEY `fk_receiver_user_id` (`receiver_id`);

--
-- Index pour la table `networking_follows`
--
ALTER TABLE `networking_follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_follow` (`follower_id`,`following_id`),
  ADD KEY `fk_following_user_id` (`following_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notification_user_id` (`user_id`),
  ADD KEY `fk_notification_sender_id` (`sender_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `networking_connections`
--
ALTER TABLE `networking_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `networking_follows`
--
ALTER TABLE `networking_follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `networking_connections`
--
ALTER TABLE `networking_connections`
  ADD CONSTRAINT `fk_receiver_user_id` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_requester_user_id` FOREIGN KEY (`requester_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `networking_follows`
--
ALTER TABLE `networking_follows`
  ADD CONSTRAINT `fk_follower_user_id` FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_following_user_id` FOREIGN KEY (`following_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notification_sender_id` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_notification_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
