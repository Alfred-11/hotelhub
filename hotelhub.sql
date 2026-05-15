-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 26, 2025 at 03:29 PM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotelhub`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `hotel_id` int NOT NULL,
  `room_type` varchar(100) NOT NULL,
  `checkin_date` date NOT NULL,
  `checkout_date` date NOT NULL,
  `total_nights` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `booking_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `payment_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `user_id` (`user_id`),
  KEY `hotel_id` (`hotel_id`),
  KEY `payment_id` (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotels`
--

DROP TABLE IF EXISTS `hotels`;
CREATE TABLE IF NOT EXISTS `hotels` (
  `hotel_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `description` text,
  `address` text,
  `image_main` varchar(255) DEFAULT NULL,
  `image_gallery1` varchar(255) DEFAULT NULL,
  `image_gallery2` varchar(255) DEFAULT NULL,
  `image_gallery3` varchar(255) DEFAULT NULL,
  `checkin_time` varchar(20) DEFAULT NULL,
  `checkout_time` varchar(20) DEFAULT NULL,
  `cancellation_policy` text,
  `pets_policy` text,
  `payment_policy` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `rating` int DEFAULT '0',
  `price` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`hotel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hotel_facilities`
--

DROP TABLE IF EXISTS `hotel_facilities`;
CREATE TABLE IF NOT EXISTS `hotel_facilities` (
  `facility_id` int NOT NULL AUTO_INCREMENT,
  `hotel_id` int DEFAULT NULL,
  `facility_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`facility_id`),
  KEY `hotel_id` (`hotel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` varchar(100) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `booking_id` int DEFAULT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `offer_id` varchar(100) DEFAULT NULL,
  `status` enum('success','failed','pending') NOT NULL DEFAULT 'pending',
  `method` varchar(50) DEFAULT NULL,
  `payment_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `razorpay_signature` text,
  PRIMARY KEY (`payment_id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int NOT NULL AUTO_INCREMENT,
  `hotel_id` int DEFAULT NULL,
  `room_type` varchar(100) DEFAULT NULL,
  `price_per_night` decimal(10,2) DEFAULT NULL,
  `capacity` varchar(50) DEFAULT NULL,
  `beds` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`room_id`),
  KEY `hotel_id` (`hotel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `reset_token_created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `google_id` (`google_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
