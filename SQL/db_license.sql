-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Feb 06, 2022 at 02:58 PM
-- Server version: 5.7.34
-- PHP Version: 7.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ultimate_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `db_license`
--

CREATE TABLE `db_license` (
  `id` int(11) NOT NULL,
  `store_name` text NOT NULL,
  `expired_at` datetime NOT NULL,
  `license_duration` int(11) DEFAULT NULL,
  `description` text,
  `status` int(11) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `db_license`
--

INSERT INTO `db_license` (`id`, `store_name`, `expired_at`, `license_duration`, `description`, `status`) VALUES
(9, 'Babus Store', '2023-04-06 08:04:13', 14, 'License start form 2022-02-06 to 2023-04-06 08:04:13', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `db_license`
--
ALTER TABLE `db_license`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `db_license`
--
ALTER TABLE `db_license`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
