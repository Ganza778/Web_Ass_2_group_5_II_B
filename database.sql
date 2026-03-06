-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 06, 2026 at 09:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tourstack`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `homestay_id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL,
  `nights` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `homestays`
--

CREATE TABLE `homestays` (
  `id` int(11) NOT NULL,
  `host_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `location` varchar(200) NOT NULL,
  `cultural_tag` enum('Imigongo','volcanic stone','traditional','none') DEFAULT 'none',
  `image` varchar(255) DEFAULT 'placeholder.jpg',
  `verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `homestays`
--

INSERT INTO `homestays` (`id`, `host_id`, `title`, `description`, `price`, `location`, `cultural_tag`, `image`, `verified`, `created_at`) VALUES
(7, 1, 'Volcanic Stone Cottage', 'Cozy cottage near volcanoes', 45.00, 'Musanze', 'volcanic stone', 'volcanic-cottage.jpg', 1, '2026-03-06 08:51:37'),
(8, 1, 'Imigongo Art Stay', 'Traditional art experience', 55.00, 'Ruhengeri', 'Imigongo', 'imigongo-art.jpg', 1, '2026-03-06 08:51:37'),
(9, 1, 'Traditional Farm Stay', 'Experience rural life', 35.00, 'Cyuve', 'traditional', 'traditional-farm.jpg', 0, '2026-03-06 08:51:37'),
(10, 1, 'La Dream Stay', 'Beautiful luxury stay with mountain views', 75.00, 'Musanze', 'none', 'la-dream.jpg', 1, '2026-03-06 08:51:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('host','guest','admin') DEFAULT 'guest',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'John Host', 'host@test.com', '$2y$10$2jGjxDj0EYhn4qgTqBxblOWLD2TpWTeWVYHCVlg2HzqBODroxlkY.', 'host', '2026-03-06 04:52:19'),
(2, 'Jane Guest', 'guest@test.com', '$2y$10$MO.29.nwKimGysX.au/Wd.jXc4IAl0pDPIx0AhIiF.r1cdpGWbB.G', 'guest', '2026-03-06 04:52:19'),
(3, 'Admin User', 'admin@test.com', '$2y$10$Dcr.naSFQ6j7RxgFzfoDVOY.7y.k936jcQ9vQ9am1IWM7i.y8nCs2', 'admin', '2026-03-06 04:52:19'),
(4, 'Ines', 'ines@ines.ac.rw', '$2y$10$hSuHTU8QygL4vNLcVtQ3wuWjkBUjtKoNEaMRIhtpMjKbYKr/tjVlq', 'host', '2026-03-06 05:10:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `homestay_id` (`homestay_id`),
  ADD KEY `guest_id` (`guest_id`);

--
-- Indexes for table `homestays`
--
ALTER TABLE `homestays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `host_id` (`host_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `homestays`
--
ALTER TABLE `homestays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`homestay_id`) REFERENCES `homestays` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`guest_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `homestays`
--
ALTER TABLE `homestays`
  ADD CONSTRAINT `homestays_ibfk_1` FOREIGN KEY (`host_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
