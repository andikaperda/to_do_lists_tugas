-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2024 at 09:45 AM
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
-- Database: `to_do_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `to_do_lists`
--

CREATE TABLE `to_do_lists` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `day` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `deadline` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_email` varchar(100) DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `to_do_lists`
--

INSERT INTO `to_do_lists` (`id`, `title`, `day`, `date`, `deadline`, `created_at`, `user_email`, `is_completed`) VALUES
(2, 'web pro', 'Sunday', '2024-11-03', '00:00:00', '2024-10-23 05:58:49', 'Jammbul@gmail.com', 0),
(3, 'SAVZAS', 'Wednesday', '3272-11-02', '00:00:00', '2024-10-23 06:22:50', 'Jammbul@gmail.com', 0),
(4, 'database', 'Friday', '2024-10-11', '23:59:00', '2024-10-23 06:24:14', 'Jammbul@gmail.com', 0),
(5, 'Webprog', 'Thursday', '0000-00-00', '11:30:00', '2024-10-23 06:47:07', 'Jammbul@gmail.com', 0),
(7, 'wkwkwk', 'Wednesday', '3192-09-02', '03:28:00', '2024-10-23 20:28:33', 'm.tarekh2004@gmail.com', 1),
(8, 'fasfs', 'Monday', '2025-01-01', '15:02:00', '2024-10-23 20:28:49', 'm.tarekh2004@gmail.com', 0),
(9, 'cwsfc', 'Thursday', '8930-03-09', '09:03:00', '2024-10-23 20:29:00', 'm.tarekh2004@gmail.com', 0),
(10, 'csdgvd', 'Wednesday', '3412-02-12', '12:34:00', '2024-10-23 20:31:20', 'm.tarekh2004@gmail.com', 0),
(11, 'WAD', 'Sunday', '0045-03-12', '12:45:00', '2024-10-23 20:46:19', 'm.tarekh2004@gmail.com', 0),
(12, 'wb pro', 'Wednesday', '2024-10-23', '00:00:00', '2024-10-24 03:12:41', 'm.tarekh2004@gmail.com', 0),
(13, 'jnasdjinj', 'Thursday', '2910-10-02', '00:00:00', '2024-10-24 05:11:55', 'm.tarekh2004@gmail.com', 0),
(14, 'anjirlah', 'Thursday', '2024-10-24', '16:32:00', '2024-10-24 09:31:14', 'm.tarekh2004@gmail.com', 0),
(15, 'Web Programming', 'Monday', '2024-10-21', '23:59:00', '2024-10-25 03:53:13', 'yogawyas@gmail.com', 1),
(16, 'UTS WEB PROGRAMING', 'Friday', '2024-10-25', '23:59:00', '2024-10-25 03:58:32', 'm.tarekh2004@gmail.com', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password`, `created_at`, `profile_image`) VALUES
(1, 'Muhamad Tarekh', 'm.tarekh2004@gmail.com', '$2y$10$eO/QOhamQgAFdNMfeY3EN.SGsnWiwwkjLj99xd6q/GEpJK3PZ41mG', '2024-10-23 05:41:48', 'uploads/WhatsApp Image 2024-09-05 at 11.23.36_63827971.jpg'),
(2, 'Jammbul', 'Jammbul@gmail.com', '$2y$10$fjPJIs5R23gHanTN9VUYgO0geX6eZVU/BtxuKgEYgcw29Nd7/7WAq', '2024-10-23 05:42:42', NULL),
(3, 'Yoga Wyas', 'yogawyas@gmail.com', '$2y$10$1Cxf5BkkKooYDTHlhc4rZeLytWtENKoad.ewOX0Gcwb/4y1pbVUKa', '2024-10-25 03:52:19', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `to_do_lists`
--
ALTER TABLE `to_do_lists`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `to_do_lists`
--
ALTER TABLE `to_do_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
