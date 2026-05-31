-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 26, 2026 at 10:37 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehicle_mgmt`
--

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `status` enum('available','on_trip','off') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `full_name`, `phone`, `license_number`, `status`, `created_at`) VALUES
(1, 'Nguyễn Văn An', '0901234561', 'B2-001234', 'on_trip', '2026-05-23 08:53:19'),
(2, 'Trần Văn Bình', '0901234562', 'B2-005678', 'available', '2026-05-23 08:53:19'),
(3, 'Lê Văn Cường', '0901234563', 'B2-009012', 'available', '2026-05-23 08:53:19'),
(4, 'Phạm Văn Dũng', '0901234564', 'D-003456', 'available', '2026-05-23 08:53:19'),
(5, 'Hoàng Văn Em', '0901234565', 'B2-007890', 'on_trip', '2026-05-23 08:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `requester_name` varchar(100) NOT NULL,
  `requester_dept` varchar(100) DEFAULT NULL,
  `companions` varchar(500) DEFAULT NULL,
  `destination` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `departure_time` datetime NOT NULL,
  `expected_return` datetime DEFAULT NULL,
  `actual_return` datetime DEFAULT NULL,
  `status` enum('scheduled','departed','returned','cancelled') DEFAULT 'scheduled',
  `gate_out_note` varchar(255) DEFAULT NULL,
  `gate_in_note` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `vehicle_id`, `driver_id`, `requester_name`, `requester_dept`, `companions`, `destination`, `purpose`, `departure_time`, `expected_return`, `actual_return`, `status`, `gate_out_note`, `gate_in_note`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'JASON HUANG 2', 'IT', NULL, 'BIÊN HÒA 2', 'GO LIVE', '2026-05-26 07:22:00', '2026-05-26 15:24:00', '2026-05-26 04:36:11', 'returned', '', 'MANG ĐỒ RA BÊN NGOÀI', 5, '2026-05-26 02:18:37', '2026-05-26 02:36:11'),
(2, 2, 5, 'JASON HUANG 3', 'IT', NULL, 'BIÊN HÒA 2', 'đi cong viec', '2026-05-26 08:39:00', '2026-05-26 12:39:00', '2026-05-26 04:40:39', 'returned', '', 'MANG ĐỒ RA BÊN NGOÀI', 5, '2026-05-26 02:36:59', '2026-05-26 02:40:39'),
(3, 1, 1, 'JASON HUANG 4', '', NULL, 'BIÊN HÒA 2', 'cong tac', '2026-05-26 09:48:00', '2026-05-26 18:50:00', NULL, 'departed', '', '', 5, '2026-05-26 02:43:58', '2026-05-26 07:45:17'),
(4, 2, 5, 'sơn', 'cp', NULL, 'hố nai', 'công tác', '2026-05-26 08:22:00', '2026-05-26 11:20:00', '2026-05-26 06:19:33', 'returned', 'â', 'đá', 5, '2026-05-26 04:19:05', '2026-05-26 04:19:33'),
(5, 2, 3, 'Test 9', 'it', 'người 1, người 2 , người 3', 'Hố Nai', 'Kiểm tra hàng hóa', '2026-05-26 14:50:00', '2026-05-26 16:03:00', '2026-05-26 10:32:40', 'returned', '', '', 5, '2026-05-26 07:51:27', '2026-05-26 08:32:40'),
(6, 1, 5, 'Nhân viên', 'CP', 'lê văn 1, lê văn 2', 'trảng dài', 'đi làm', '2026-05-26 07:30:00', '2026-05-26 14:10:00', NULL, 'departed', 'Mang theo giấy tờ', '', 5, '2026-05-26 08:24:04', '2026-05-26 08:24:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','staff') DEFAULT 'staff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`) VALUES
(5, 'admin', '$2y$10$6eAZzDkOtW2O.56xqxzKTOjaU8oF.nviOQggoaxq2YRElKs8/Hxau', 'Quan tri viên', 'admin', '2026-05-23 09:08:45'),
(6, 'nhansu', '$2y$10$aofPJNpoXRkr8QgqVI4gIerxQMnHhnTGSNgVOl88qfzoxer/I16ca', 'Nhân Su', 'staff', '2026-05-23 09:08:45');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `vehicle_name` varchar(100) NOT NULL,
  `vehicle_type` varchar(50) DEFAULT 'Sedan',
  `status` enum('available','in_use','maintenance') DEFAULT 'available',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `plate_number`, `vehicle_name`, `vehicle_type`, `status`, `notes`, `created_at`) VALUES
(1, '51A-123.45', 'Toyota Fortuner Trắng', 'SUV', 'in_use', NULL, '2026-05-23 08:53:19'),
(2, '51G-678.90', 'Toyota Innova Bạc', 'MPV', 'available', NULL, '2026-05-23 08:53:19'),
(3, '51H-321.00', 'Ford Transit Đen', 'Van', 'available', NULL, '2026-05-23 08:53:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `drivers`
--
ALTER TABLE `drivers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`),
  ADD CONSTRAINT `trips_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `drivers` (`id`),
  ADD CONSTRAINT `trips_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
