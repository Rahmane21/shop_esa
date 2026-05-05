-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 06 mai 2026 à 01:24
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `shop_esa`
--

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantite`) VALUES
(1, NULL, NULL, 20),
(2, NULL, NULL, 20),
(3, NULL, NULL, 20),
(40, 15, 29, 2);

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `nom`, `description`) VALUES
(1, 'Sports', 'Maillots,Ballons,Godasses,Gans,colan'),
(2, ' constructions', 'Ciments,Sable,Gravier,carreaux'),
(3, 'Electroniques', 'Telephones,ordinateur,tablette,accessoires tech'),
(4, 'Decoration', 'Fleur,Led,air fryer,linge de lit,ampoule multicolor'),
(5, 'Mode', 'T-shirts,robe,chaine,montres vetements'),
(6, 'Bijoux', 'Boucle d\'oreille\r\nMontre \r\nChaine\r\n'),
(7, 'Machines Amenagements', 'fer a repasser\r\naspirateur\r\ncuissiniere');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `statut` varchar(50) DEFAULT 'en attente',
  `adresse` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paiement` varchar(50) DEFAULT 'cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total`, `statut`, `adresse`, `created_at`, `paiement`) VALUES
(13, 15, 188800.00, 'livree', 'zongo, lome — Tél : 97160715', '2026-05-02 05:10:49', 'cash'),
(14, 16, 47200.00, 'livree', 'be, lome — Tél : 90202585', '2026-05-02 07:11:07', 'cash'),
(15, 10, 981170.00, 'annulee', 'kozah, kara — Tél : 91236548', '2026-05-02 07:19:16', 'cash'),
(16, 10, 64900.00, 'expediee', 'zongo, sokode — Tél : 97160715', '2026-05-02 14:14:50', 'cash'),
(17, 10, 4829740.00, 'livree', 'neuf 1, paris — Tél : 1-4582-684-795', '2026-05-02 20:27:34', 'cash'),
(18, 10, 3540.00, 'expediee', 'togblekope, agoe — Tél : 98564231', '2026-05-02 20:30:23', 'cash'),
(19, 17, 767000.00, 'annulee', 'togblekope, lome — Tél : 96036566', '2026-05-03 10:42:07', 'cash'),
(20, 10, 767000.00, 'livree', 'zongo, lome — Tél : 9000000', '2026-05-03 10:44:09', 'cash'),
(21, 18, 20060.00, 'en_attente', 'zongo, lome — Tél : 71339632', '2026-05-03 19:16:38', 'cash'),
(22, 19, 5900.00, 'expediee', 'Demakpoe, Lome — Tél : 90202585', '2026-05-03 19:36:50', 'cash'),
(23, 10, 17700.00, 'annulee', 'dlgf, koko — Tél : 9000000', '2026-05-03 19:38:31', 'cash'),
(24, 10, 5900.00, 'livree', 'hdk, fci — Tél : 9000000', '2026-05-03 19:39:44', 'cash'),
(25, 10, 17700.00, 'livree', 'dfr, hz — Tél : 9000000', '2026-05-03 20:03:32', 'cash'),
(26, 18, 29500.00, 'annulee', 'zongo, Lome — Tél : 79299223', '2026-05-03 20:32:02', 'cash'),
(27, 17, 5900.00, 'expediee', 'togle, Lome — Tél : 96036566', '2026-05-03 20:33:28', 'cash'),
(28, 10, 64900.00, 'en_attente', 'mbaquida, kara — Tél : 1-4582-684-795', '2026-05-03 20:38:35', 'cash'),
(29, 10, 29500.00, 'en_attente', 'zongo, Lome — Tél : 97160715', '2026-05-04 07:55:35', 'cash'),
(30, 10, 23600.00, 'en_attente', 'hzh, hzh — Tél : 9000000', '2026-05-05 22:47:07', 'cash'),
(31, 10, 23600.00, 'en_attente', 'zongo, Lome — Tél : 9000000', '2026-05-05 22:59:01', 'cash'),
(32, 10, 17700.00, 'annulee', 'fkzu, viu — Tél : 97160715', '2026-05-05 23:02:06', 'cash');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unit` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantite`, `prix_unit`) VALUES
(6, NULL, NULL, 20, 5000.00),
(7, NULL, NULL, 20, 10500.00),
(8, NULL, NULL, 12, 50000.00),
(9, NULL, NULL, 2, 20000.00),
(10, NULL, NULL, 5, 400000.00),
(11, 13, NULL, 4, 5000.00),
(12, 13, 11, 4, 15000.00),
(13, 13, NULL, 4, 20000.00),
(14, 14, NULL, 1, 5000.00),
(15, 14, 11, 1, 15000.00),
(16, 14, NULL, 1, 20000.00),
(17, 15, 6, 1, 750000.00),
(18, 15, 7, 1, 6500.00),
(19, 15, 17, 1, 75000.00),
(20, 16, NULL, 1, 5000.00),
(21, 16, 11, 1, 15000.00),
(22, 16, NULL, 1, 20000.00),
(23, 16, 23, 1, 15000.00),
(24, 17, 12, 1, 10000.00),
(25, 17, 21, 1, 4000000.00),
(26, 17, NULL, 1, 80000.00),
(27, 17, 3, 1, 3000.00),
(28, 18, 14, 1, 3000.00),
(29, 19, 30, 1, 650000.00),
(30, 20, 30, 1, 650000.00),
(31, 21, 7, 2, 6500.00),
(32, 21, 47, 2, 2000.00),
(33, 22, NULL, 1, 5000.00),
(34, 23, 11, 1, 15000.00),
(35, 24, NULL, 1, 5000.00),
(36, 25, 11, 1, 15000.00),
(37, 26, NULL, 5, 5000.00),
(38, 27, NULL, 1, 5000.00),
(39, 28, NULL, 11, 5000.00),
(40, 29, 38, 1, 25000.00),
(41, 30, 29, 1, 20000.00),
(42, 31, 29, 1, 20000.00),
(43, 32, 23, 1, 15000.00);

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `prix` decimal(10,2) NOT NULL,
  `stock` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `cat_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `nom`, `description`, `prix`, `stock`, `image`, `cat_id`) VALUES
(3, 'ecouteurs', 'Sans fil,autonomie 10h', 3000.00, 9, 'prod_69f6001cc2841.jpg', 3),
(6, 'sable', 'rouge,10roues,2voyage', 750000.00, 14, 'prod_69f5fff58e53c.jpg', 2),
(7, 'ciment', '3tonnes,5voyage,2roues', 6500.00, 27, 'prod_69f5ffe8b9019.jpg', 2),
(11, 'Ballon', 'Marque:Kipsta\r\nN°5,4,3\r\nCouleur:rouge,blanc,vert,jaune', 15000.00, 42, 'prod_69f5ffc7c3492.jpg', 1),
(12, 'Chemise', 'Marque:louis vuiton\r\ncouleur:Blanche\r\nTaille:xl/xxl/L', 10000.00, 49, 'prod_69f5ffb34beef.jpg', 5),
(13, 'Veste', 'Marque:azur\r\nCouleur:noir,cafe,cendre\r\ntaille:xxl/xl', 50000.00, 40, 'prod_69f5ff9ebd2ec.jpg', 5),
(14, 'Led', 'multicolor\r\ncouleur blanche', 3000.00, 49, 'prod_69f5ff6e3ea96.jpg', 4),
(15, 'FLeurs', 'lorier\r\nlotus\r\n', 2000.00, 10, 'prod_69f5ff917390c.jpg', 4),
(16, 'ordinateur', 'ecran : 14 \r\nled\r\nnoir', 400000.00, 30, 'prod_69f6010458fdc.jpg', 3),
(17, 'Gravier', 'rouge,noir\r\n10roues', 75000.00, 9, 'prod_69f600f7ad4a1.jpg', 2),
(20, 'Boucle d\'oreille', 'brillant \r\nDiamants', 2000000.00, 10, 'prod_69f603c320e01.jpg', 6),
(21, 'Montre', 'rolex\r\nor\r\nDiamant', 4000000.00, 9, 'prod_69f6043fcd813.jpg', 6),
(22, 'Colier', 'Acier\r\nor\r\nDiamants', 1500000.00, 20, 'prod_69f604f090b29.jpg', 6),
(23, 'Gant', 'multicouleur\r\nTaille xxl/xl/l', 15000.00, 48, 'prod_69f6058125864.jpg', 1),
(24, 'Micro ondes', 'chargable', 10000.00, 50, 'prod_69f6dbc2518a3.jpg', 7),
(25, 'Fer a Repasser', 'puissant\r\nchargable', 5000.00, 50, 'prod_69f6dc0fcd835.jpg', 7),
(26, 'Cuisiniere', 'puissants', 50000.00, 40, 'prod_69f6dc8d24b26.jpg', 7),
(27, 'Apirateurs', 'puissants', 50000.00, 40, 'prod_69f6dcbe03f4f.jpg', 7),
(28, 'Basket (SB nike)', 'rouge', 25000.00, 40, 'prod_69f6dd4e69df6.jpg', 5),
(29, 'bike foot', '', 20000.00, 48, 'prod_69f6ddea1aa2c.jpg', 1),
(30, 'Iphone17pro max', '256Gb\r\n100%', 650000.00, 48, 'prod_69f6de578db0a.jpg', 3),
(31, 'Iphone16', '256Gb\r\n100%', 450000.00, 50, 'prod_69f6de767eb63.jpg', 3),
(32, 'Iphone15 pro', '256Gb\r\n100%', 300000.00, 50, 'prod_69f6deb259948.jpg', 3),
(33, 'Basket(Jordane)', 'multi couleurs', 25000.00, 50, 'prod_69f6df26907c9.jpg', 5),
(34, 'Apple Watch', 'multi couleurs', 5000.00, 50, 'prod_69f6df93e8d16.jpg', 3),
(35, 'Atikpo', '', 50000.00, 25, 'prod_69f6dff4e4d50.jpg', 5),
(36, 'Rideau', 'multi couleur', 3000.00, 50, 'prod_69f705e803df4.jpg', 4),
(37, 'laptop HP', 'ecran 14 pouce 512gb RAM 16', 500000.00, 50, 'prod_69f6e17974cc8.jpg', 3),
(38, 'Air Nike', 'Multi couleur', 25000.00, 99, 'prod_69f6e2887ebe2.jpg', 1),
(39, 'Moulinex', '', 25000.00, 30, 'prod_69f707ae42426.jpg', 7),
(40, 'Machine a laver', '', 75000.00, 30, 'prod_69f707d5088b1.jpg', 7),
(41, 'Pandora', 'or', 200000.00, 25, 'prod_69f708f36b870.jpg', 6),
(42, 'Bague', 'or Diamant', 200000.00, 35, 'prod_69f7092547407.jpg', 6),
(43, 'Bracelet', 'or Diamant argent', 200000.00, 35, 'prod_69f7094ca5dcf.jpg', 6),
(44, 'Goyard', 'multi couleur', 20000.00, 100, 'prod_69f709f73e5af.jpg', 5),
(45, 'Collan Pro', 'multi couleur', 5000.00, 50, 'prod_69f70a6694354.jpg', 1),
(46, 'pavé', '', 650.00, 50000, 'prod_69f70c05c476d.jpg', 2),
(47, 'Pelle', '', 2000.00, 4998, 'prod_69f70c49999c4.jpg', 2),
(48, 'Betonniere', '', 200000.00, 50, 'prod_69f70c71820fd.jpg', 2),
(49, 'Table diner', '', 30000.00, 50, 'prod_69f70dec42480.jpg', 4),
(50, 'Canape', '', 30000.00, 50, 'prod_69f70e0a7f98f.jpg', 4),
(51, 'Art', '', 15000.00, 50, 'prod_69f70e3572a33.jpg', 4),
(53, 'Maillot', '', 5000.00, 100, 'prod_69f7b2ade9159.jpg', 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `email`, `password`, `role`, `created_at`) VALUES
(10, 'Rahmane', 'Rahmane@gmail.com', '$2y$10$oUlkS8sKlUStt/pxZMOUp.E/B1V08uEkkMXAVsvBplz7q5wFv9KxO', 'admin', '2026-05-01 14:49:52'),
(11, 'Hairya', 'Hairya@gmail.com', '$2y$10$5E257KOtVAmV4Xcb.6SJe.xYCLUtErA1SZOUFPDEdkV06C3nJsFJS', 'admin', '2026-05-01 14:49:52'),
(12, 'Maxime', 'Maxime1@gmail.com', '$2y$10$bn5W6.COaQ901iK/Gy6yUu0fvPd1rLGM7UNDVY8LQKXO3TGCjDut2', 'client', '2026-05-01 14:52:50'),
(13, 'amira', 'Rae@gmail.com', '$2y$10$C9KkvGmlE2ebDUhuCKrK5OKvu9YXO4KFPC0w6BGvSpFq.BhjZuYpa', 'client', '2026-05-01 16:50:11'),
(14, 'kokou', 'Ra@gmail.com', '$2y$10$sL2Niaiw9gPDUTZgWZWXJ.bLroABN1ArKijA5LuLbE0Yhrb7Ldfa2', 'client', '2026-05-01 17:19:36'),
(15, 'rahmane', 'Rahmane1@gmail.com', '$2y$10$QDSsvRhirWtic9bFtJqpg.gm7Xr9SoUqMhJLgSwt8xB7a7WuCaO02', 'client', '2026-05-01 21:57:31'),
(16, 'abd', 'abd@gmail.com', '$2y$10$QdGWXE39Q4UhNgbOQ89D4ONguD.wN8pAmBIqhSSh0E2mMGUbk6bzu', 'client', '2026-05-02 07:09:35'),
(17, 'kokou', 'kokoupipino@gmail.com', '$2y$10$cP.CrqYegop8ht7c.UwhMuZIPf2oZsyESsapDel7n5jq8qG3AbOBm', 'client', '2026-05-03 10:14:37'),
(18, 'Ouro Abdou', 'ouro@gmail.com', '$2y$10$YV/0YWcVSQ2oZg3h8vpYYuY47RePwa.DJIKU6ZllDVxFKRPtfoPsu', 'client', '2026-05-03 19:11:07'),
(19, 'koudous', 'koudous@gmail.com', '$2y$10$8hL3nbn8PFqpKHc2dWcQ.O5lc6Xr.kDVSpYDcpoKO4zN7v2ITDicC', 'client', '2026-05-03 19:35:04'),
(20, 'Maxime', 'Maxime14@gmail.com', '$2y$10$fk30XvSarPhRVul0.XLEy.xW1pUT5iGK3sS9KpcnIRfEDspIVN0uW', 'admin', '2026-05-05 07:23:34'),
(21, 'lk', 'koudos@gmail.com', '$2y$10$2EvWT.7MYqWQDkQ/YcgPkOKxSGk4PtWY.eGmmWITnPRIJdkI1qTpu', 'client', '2026-05-05 22:01:51');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_cart_users` (`user_id`),
  ADD KEY `fk_cart_products` (`product_id`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_users` (`user_id`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_orders` (`order_id`),
  ADD KEY `fk_order_items_products` (`product_id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_products_categories` (`cat_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `fk_cart_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_orders` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_products` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_categories` FOREIGN KEY (`cat_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
