-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 20, 2025 at 05:26 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autobodybooking`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `UserID` int(11) NOT NULL,
  `WebsiteUpdateDate` date DEFAULT NULL,
  `AdminNotes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`UserID`, `WebsiteUpdateDate`, `AdminNotes`) VALUES
(1, '2025-03-18', 'Main Admin account');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `UserID` int(11) NOT NULL,
  `PreferredContact` varchar(20) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`UserID`, `PreferredContact`, `Address`) VALUES
(3, 'Email', '123 Main Street'),
(10, 'Email', '12345 st nw'),
(11, 'Phone', '123'),
(15, 'Email', '123');

-- --------------------------------------------------------

--
-- Table structure for table `customerdiscountcoupon`
--

CREATE TABLE `customerdiscountcoupon` (
  `CouponNumber` int(11) NOT NULL,
  `CustomerUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `d=esting`
-- (See below for the actual view)
--
CREATE TABLE `d=esting` (
`CONSTRAINT_NAME` varchar(64)
,`COLUMN_NAME` varchar(64)
,`REFERENCED_TABLE_NAME` varchar(64)
,`REFERENCED_COLUMN_NAME` varchar(64)
);

-- --------------------------------------------------------

--
-- Table structure for table `dealswith`
--

CREATE TABLE `dealswith` (
  `CustomerUserID` int(11) NOT NULL,
  `EmployeeUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dealswith`
--

INSERT INTO `dealswith` (`CustomerUserID`, `EmployeeUserID`) VALUES
(3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `discountcoupon`
--

CREATE TABLE `discountcoupon` (
  `CouponNumber` int(11) NOT NULL,
  `DiscountAmount` decimal(10,2) DEFAULT NULL,
  `OfferingID` int(11) DEFAULT NULL,
  `AdminUserID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discountcoupon`
--

INSERT INTO `discountcoupon` (`CouponNumber`, `DiscountAmount`, `OfferingID`, `AdminUserID`) VALUES
(1, 10.00, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `UserID` int(11) NOT NULL,
  `JobTitle` varchar(50) DEFAULT NULL,
  `Specialization` varchar(150) DEFAULT NULL,
  `Address` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`UserID`, `JobTitle`, `Specialization`, `Address`) VALUES
(2, 'Tinter', 'Window Tints', '254 Some Street NW Calgary, AB, Canada'),
(12, 'Performance', 'engine retune', '123 somewhere drive, Alaska, USA'),
(13, 'PPF instal', 'PPF', '123 which Street Alabama'),
(14, 'vinyl wrap', 'vinyl wrap', 'aStreet kenya Africa'),
(16, 'tint', 'Window Tint', 'Zimbabwe drive, Africa');

-- --------------------------------------------------------

--
-- Table structure for table `employeeavailability`
--

CREATE TABLE `employeeavailability` (
  `AvailabilityID` int(11) NOT NULL,
  `EmployeeUserID` int(11) DEFAULT NULL,
  `AvailabilityDate` date DEFAULT NULL,
  `Status` enum('Available','Out') NOT NULL DEFAULT 'Available',
  `StartTime` time DEFAULT NULL,
  `EndTime` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employeeavailability`
--

INSERT INTO `employeeavailability` (`AvailabilityID`, `EmployeeUserID`, `AvailabilityDate`, `Status`, `StartTime`, `EndTime`) VALUES
(21, 2, '2025-04-19', 'Available', '09:00:00', '10:00:00'),
(22, 2, '2025-04-19', 'Available', '10:00:00', '11:00:00'),
(23, 2, '2025-04-19', 'Available', '11:00:00', '12:00:00'),
(30, 12, '2025-04-20', 'Available', '16:00:00', '17:00:00'),
(31, 12, '2025-04-20', 'Available', '17:00:00', '18:00:00'),
(35, 12, '2025-05-02', 'Available', '08:00:00', '09:00:00'),
(36, 12, '2025-05-02', 'Available', '09:00:00', '10:00:00'),
(37, 12, '2025-05-02', 'Available', '10:00:00', '11:00:00'),
(44, 12, '2025-04-25', 'Available', '16:00:00', '17:00:00'),
(45, 16, '2025-04-24', 'Available', '08:00:00', '09:00:00'),
(46, 16, '2025-04-24', 'Available', '12:00:00', '13:00:00'),
(47, 16, '2025-04-24', 'Available', '16:00:00', '17:00:00'),
(49, 14, '2025-04-21', 'Available', '12:00:00', '13:00:00'),
(50, 14, '2025-04-21', 'Available', '16:00:00', '17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `CustomerUserID` int(11) DEFAULT NULL,
  `FeedbackDate` datetime DEFAULT NULL,
  `FeedbackName` varchar(50) DEFAULT NULL,
  `Comments` text DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`FeedbackID`, `CustomerUserID`, `FeedbackDate`, `FeedbackName`, `Comments`, `Rating`) VALUES
(2, 3, '2025-04-17 02:52:33', NULL, '(Hello) heloo', 5),
(3, 11, '2025-04-19 03:40:40', 'jony', 'very nice,', 5),
(4, 15, '2025-04-20 01:24:20', 'jb', 'I love u', 5);

-- --------------------------------------------------------

--
-- Table structure for table `Schedule`
--

CREATE TABLE `Schedule` (
  `ScheduleID` int(11) NOT NULL,
  `CustomerUserID` int(11) NOT NULL,
  `OfferingID` int(11) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `TotalPrice` decimal(10,2) DEFAULT NULL,
  `AdminUserID` int(11) DEFAULT NULL,
  `VehicleID` int(11) DEFAULT NULL,
  `Status` enum('Scheduled','In Progress','Completed') DEFAULT 'Scheduled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Schedule`
--

INSERT INTO `Schedule` (`ScheduleID`, `CustomerUserID`, `OfferingID`, `StartDate`, `EndDate`, `TotalPrice`, `AdminUserID`, `VehicleID`, `Status`) VALUES
(1, 3, 1, '2025-03-25 10:00:00', '2025-03-25 11:00:00', 20.00, 1, NULL, 'Completed'),
(2, 11, 1, '2025-04-19 16:03:00', '2025-04-19 17:03:00', 0.00, 1, NULL, 'Scheduled'),
(3, 11, 1, '2025-04-22 12:00:00', '2025-04-22 13:00:00', 0.00, 1, NULL, 'Completed'),
(8, 11, 6, '2025-04-19 08:00:00', '2025-04-19 09:00:00', 2399.00, 1, NULL, 'In Progress'),
(7, 11, 7, '2025-04-19 10:00:00', '2025-04-19 11:00:00', 699.00, 1, NULL, 'Completed'),
(6, 11, 7, '2025-04-21 08:00:00', '2025-04-21 09:00:00', 699.00, 1, NULL, 'Completed'),
(5, 11, 7, '2025-04-22 16:00:00', '2025-04-22 17:00:00', 0.00, 1, NULL, 'Completed'),
(4, 11, 8, '2025-04-22 17:00:00', '2025-04-22 18:00:00', 0.00, 1, NULL, 'Completed'),
(12, 15, 6, '2025-04-20 08:00:00', '2025-04-20 09:00:00', 2399.00, 1, NULL, 'Completed'),
(13, 15, 6, '2025-04-21 09:00:00', '2025-04-21 10:00:00', 2399.00, 1, NULL, 'Scheduled'),
(14, 15, 6, '2025-04-21 10:00:00', '2025-04-21 11:00:00', 2399.00, 1, 1, 'Scheduled'),
(15, 15, 6, '2025-04-25 12:00:00', '2025-04-25 13:00:00', 2399.00, 1, 2, 'Completed'),
(10, 15, 7, '2025-04-19 08:00:00', '2025-04-19 09:00:00', 699.00, 1, NULL, 'Scheduled'),
(11, 15, 7, '2025-04-19 09:00:00', '2025-04-19 10:00:00', 699.00, 1, NULL, 'In Progress'),
(9, 15, 7, '2025-04-20 11:00:00', '2025-04-20 12:00:00', 699.00, 1, NULL, 'Completed'),
(16, 15, 8, '2025-04-21 08:00:00', '2025-04-21 09:00:00', 1899.00, 1, 3, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `scheduleemployee`
--

CREATE TABLE `scheduleemployee` (
  `CustomerUserID` int(11) NOT NULL,
  `OfferingID` int(11) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `EmployeeUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scheduleemployee`
--

INSERT INTO `scheduleemployee` (`CustomerUserID`, `OfferingID`, `StartDate`, `EndDate`, `EmployeeUserID`) VALUES
(3, 1, '2025-03-25 10:00:00', '2025-03-25 11:00:00', 2),
(11, 1, '2025-04-19 16:03:00', '2025-04-19 17:03:00', 1),
(11, 1, '2025-04-22 12:00:00', '2025-04-22 13:00:00', 2),
(11, 6, '2025-04-19 08:00:00', '2025-04-19 09:00:00', 12),
(11, 7, '2025-04-19 10:00:00', '2025-04-19 11:00:00', 12),
(11, 7, '2025-04-21 08:00:00', '2025-04-21 09:00:00', 2),
(11, 7, '2025-04-22 16:00:00', '2025-04-22 17:00:00', 2),
(11, 8, '2025-04-22 17:00:00', '2025-04-22 18:00:00', 2),
(15, 6, '2025-04-20 08:00:00', '2025-04-20 09:00:00', 14),
(15, 6, '2025-04-21 09:00:00', '2025-04-21 10:00:00', 2),
(15, 6, '2025-04-21 10:00:00', '2025-04-21 11:00:00', 2),
(15, 6, '2025-04-25 12:00:00', '2025-04-25 13:00:00', 12),
(15, 7, '2025-04-19 08:00:00', '2025-04-19 09:00:00', 2),
(15, 7, '2025-04-19 09:00:00', '2025-04-19 10:00:00', 12),
(15, 7, '2025-04-20 11:00:00', '2025-04-20 12:00:00', 14),
(15, 8, '2025-04-21 08:00:00', '2025-04-21 09:00:00', 14);

-- --------------------------------------------------------

--
-- Table structure for table `serviceoffering`
--

CREATE TABLE `serviceoffering` (
  `OfferingID` int(11) NOT NULL,
  `OfferingName` varchar(100) DEFAULT NULL,
  `ServiceDescription` text DEFAULT NULL,
  `ImagePath` varchar(255) DEFAULT NULL,
  `MinPrice` decimal(10,2) NOT NULL,
  `MaxPrice` decimal(10,2) NOT NULL,
  `Currency` char(3) DEFAULT 'USD'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `serviceoffering`
--

INSERT INTO `serviceoffering` (`OfferingID`, `OfferingName`, `ServiceDescription`, `ImagePath`, `MinPrice`, `MaxPrice`, `Currency`) VALUES
(1, 'Window Tint', 'Window Tint', 'uploads/services/svc_6800d1db35bcc5.66099740.png', 99.00, 275.00, 'USD'),
(6, 'PPF', 'PPF with price range', 'uploads/services/svc_6800d1af4b8823.62123162.png', 2399.00, 8999.00, 'USD'),
(7, 'Performance Tune', 'ECU Tunes', 'uploads/services/svc_6800d43669ae58.83227407.png', 699.00, 2199.00, 'USD'),
(8, 'Vinyl Wrap', 'Vinyl Wrap', 'uploads/services/svc_6800d46381ccb9.55061102.png', 1899.00, 3999.00, 'USD');

-- --------------------------------------------------------

--
-- Stand-in structure for view `test`
-- (See below for the actual view)
--
CREATE TABLE `test` (
`CouponNumber` int(11)
,`DiscountAmount` decimal(10,2)
,`OfferingID` int(11)
,`AdminUserID` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `UserID` int(11) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `FirstName` varchar(30) DEFAULT NULL,
  `LastName` varchar(30) DEFAULT NULL,
  `Email` varchar(50) NOT NULL,
  `AccessLevel` enum('Admin','Employee','Customer') NOT NULL,
  `DateCreated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`UserID`, `Password`, `PhoneNumber`, `FirstName`, `LastName`, `Email`, `AccessLevel`, `DateCreated`) VALUES
(1, '$2y$10$0f/4h4QvaQtD.WtXGItn0eB9j6lhwLJr1H9bejenh00NOF/5e3eTu', '587-000-1111', 'Ahmed', 'Chaudhry', 'ahmedch45@admin.com', 'Admin', '2025-04-15 22:33:48'),
(2, '$2y$10$U9v7FR5J4MagkN8snAFGbehZ16jbbWPGQWja3Pywh7KwddRzcUlGO', '587-111-000', 'Richard', 'Tan', 'richard.tan1@wraplab.com', 'Employee', '2025-04-15 22:33:48'),
(3, 'customerpass', '647-555-1985', 'Charlie', 'Angus', 'charlie@example.com', 'Customer', '2025-04-15 22:33:48'),
(9, '$2y$10$DMjvjucC.cbfBEp61MCyHuCLO1m7x2LjKL0PJKT3xbF1wu3llfecq', '8257121947', 'Farhad', 'Alizada', 'farhad@gmail.com', 'Customer', '2025-04-18 01:42:58'),
(10, '$2y$10$2LXzcPyjqQFhSOqbSh39rugIQFh1kKMcw57XUqufCKPcRX4GApN1K', '1234567890', 'bobby', 'brar', 'bobby@gmail.com', 'Customer', '2025-04-18 02:17:36'),
(11, '$2y$10$xjU5RnT0pLr0Gqn6N7e50eZKP7RVK35FZw.uuUw5ZLE5/iV3WU1JS', '5877034154', 'F', 'a', 'fa@gmail.com', 'Customer', '2025-04-18 18:34:03'),
(12, '$2y$10$.V2kGUFnxIv2RE76fI/1POT6Dtg396s0eFC7OjfFyENhviFwUN2Py', NULL, 'John', 'cena', 'ohn.cena@wraplab.com', 'Employee', '2025-04-19 14:08:59'),
(13, 'changeme123', NULL, 'Joe', 'mama', 'oe.mama@wraplab.com', 'Employee', '2025-04-19 14:11:21'),
(14, '$2y$10$zb9eLxBEYV/qU6YIPU7YJ.KCBR6I7LKY3iz592GhgiN3NVfUyarTu', NULL, 'bill', 'burr', 'bill.burr@wraplab.com', 'Employee', '2025-04-19 15:13:17'),
(15, '$2y$10$W.Gcsri2FAjmRlXEIQthNedV.bROazJyvShF428odW.0TN1HX4TS.', '1234567890', 'j', 'b', 'jb@gmail.com', 'Customer', '2025-04-19 15:15:51'),
(16, '$2y$10$/7hoZHR2ivzY2OyR5j5vJOddc7bCtVOixlciIZohGXZfKI1ybdmdK', NULL, 'Jose', 'm', 'ose.m@wraplab.com', 'Employee', '2025-04-19 17:11:31');

-- --------------------------------------------------------

--
-- Table structure for table `Vehicle`
--

CREATE TABLE `Vehicle` (
  `VehicleID` int(11) NOT NULL,
  `CustomerUserID` int(11) NOT NULL,
  `Make` varchar(50) NOT NULL,
  `Model` varchar(50) NOT NULL,
  `Year` int(11) NOT NULL,
  `VINNumber` varchar(50) DEFAULT NULL,
  `Color` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Vehicle`
--

INSERT INTO `Vehicle` (`VehicleID`, `CustomerUserID`, `Make`, `Model`, `Year`, `VINNumber`, `Color`) VALUES
(1, 15, 'toyota', 'corrola', 1602, NULL, NULL),
(2, 15, 'lamborghini', 'tractor', 1788, NULL, NULL),
(3, 15, 'h', 'j', 1234, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure for view `d=esting`
--
DROP TABLE IF EXISTS `d=esting`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `d=esting`  AS SELECT `rc`.`CONSTRAINT_NAME` AS `CONSTRAINT_NAME`, `kcu`.`COLUMN_NAME` AS `COLUMN_NAME`, `kcu`.`REFERENCED_TABLE_NAME` AS `REFERENCED_TABLE_NAME`, `kcu`.`REFERENCED_COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` FROM (`information_schema`.`referential_constraints` `rc` join `information_schema`.`key_column_usage` `kcu` on(`kcu`.`CONSTRAINT_NAME` = `rc`.`CONSTRAINT_NAME` and `kcu`.`CONSTRAINT_SCHEMA` = `rc`.`CONSTRAINT_SCHEMA`)) WHERE `rc`.`CONSTRAINT_SCHEMA` = database() AND `rc`.`TABLE_NAME` = 'ScheduleEmployee' ;

-- --------------------------------------------------------

--
-- Structure for view `test`
--
DROP TABLE IF EXISTS `test`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `test`  AS SELECT `discountcoupon`.`CouponNumber` AS `CouponNumber`, `discountcoupon`.`DiscountAmount` AS `DiscountAmount`, `discountcoupon`.`OfferingID` AS `OfferingID`, `discountcoupon`.`AdminUserID` AS `AdminUserID` FROM `discountcoupon` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`UserID`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`UserID`);

--
-- Indexes for table `customerdiscountcoupon`
--
ALTER TABLE `customerdiscountcoupon`
  ADD PRIMARY KEY (`CouponNumber`,`CustomerUserID`),
  ADD KEY `CustomerUserID` (`CustomerUserID`);

--
-- Indexes for table `dealswith`
--
ALTER TABLE `dealswith`
  ADD PRIMARY KEY (`CustomerUserID`,`EmployeeUserID`),
  ADD KEY `EmployeeUserID` (`EmployeeUserID`);

--
-- Indexes for table `discountcoupon`
--
ALTER TABLE `discountcoupon`
  ADD PRIMARY KEY (`CouponNumber`),
  ADD KEY `OfferingID` (`OfferingID`),
  ADD KEY `AdminUserID` (`AdminUserID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`UserID`);

--
-- Indexes for table `employeeavailability`
--
ALTER TABLE `employeeavailability`
  ADD PRIMARY KEY (`AvailabilityID`),
  ADD KEY `EmployeeUserID` (`EmployeeUserID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `CustomerUserID` (`CustomerUserID`);

--
-- Indexes for table `Schedule`
--
ALTER TABLE `Schedule`
  ADD PRIMARY KEY (`CustomerUserID`,`OfferingID`,`StartDate`,`EndDate`),
  ADD UNIQUE KEY `ScheduleID` (`ScheduleID`),
  ADD KEY `OfferingID` (`OfferingID`),
  ADD KEY `AdminUserID` (`AdminUserID`),
  ADD KEY `VehicleID` (`VehicleID`);

--
-- Indexes for table `scheduleemployee`
--
ALTER TABLE `scheduleemployee`
  ADD PRIMARY KEY (`CustomerUserID`,`OfferingID`,`StartDate`,`EndDate`,`EmployeeUserID`),
  ADD KEY `EmployeeUserID` (`EmployeeUserID`);

--
-- Indexes for table `serviceoffering`
--
ALTER TABLE `serviceoffering`
  ADD PRIMARY KEY (`OfferingID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `Vehicle`
--
ALTER TABLE `Vehicle`
  ADD PRIMARY KEY (`VehicleID`),
  ADD KEY `CustomerUserID` (`CustomerUserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `discountcoupon`
--
ALTER TABLE `discountcoupon`
  MODIFY `CouponNumber` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employeeavailability`
--
ALTER TABLE `employeeavailability`
  MODIFY `AvailabilityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `Schedule`
--
ALTER TABLE `Schedule`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `serviceoffering`
--
ALTER TABLE `serviceoffering`
  MODIFY `OfferingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `Vehicle`
--
ALTER TABLE `Vehicle`
  MODIFY `VehicleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `customerdiscountcoupon`
--
ALTER TABLE `customerdiscountcoupon`
  ADD CONSTRAINT `customerdiscountcoupon_ibfk_1` FOREIGN KEY (`CouponNumber`) REFERENCES `discountcoupon` (`CouponNumber`),
  ADD CONSTRAINT `customerdiscountcoupon_ibfk_2` FOREIGN KEY (`CustomerUserID`) REFERENCES `customer` (`UserID`);

--
-- Constraints for table `dealswith`
--
ALTER TABLE `dealswith`
  ADD CONSTRAINT `dealswith_ibfk_1` FOREIGN KEY (`CustomerUserID`) REFERENCES `customer` (`UserID`),
  ADD CONSTRAINT `dealswith_ibfk_2` FOREIGN KEY (`EmployeeUserID`) REFERENCES `employee` (`UserID`);

--
-- Constraints for table `discountcoupon`
--
ALTER TABLE `discountcoupon`
  ADD CONSTRAINT `discountcoupon_ibfk_1` FOREIGN KEY (`OfferingID`) REFERENCES `serviceoffering` (`OfferingID`),
  ADD CONSTRAINT `discountcoupon_ibfk_2` FOREIGN KEY (`AdminUserID`) REFERENCES `admin` (`UserID`);

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

--
-- Constraints for table `employeeavailability`
--
ALTER TABLE `employeeavailability`
  ADD CONSTRAINT `employeeavailability_ibfk_1` FOREIGN KEY (`EmployeeUserID`) REFERENCES `employee` (`UserID`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`CustomerUserID`) REFERENCES `customer` (`UserID`);

--
-- Constraints for table `Schedule`
--
ALTER TABLE `Schedule`
  ADD CONSTRAINT `fk_schedule_vehicle` FOREIGN KEY (`VehicleID`) REFERENCES `Vehicle` (`VehicleID`) ON DELETE SET NULL,
  ADD CONSTRAINT `schedule_ibfk_1` FOREIGN KEY (`CustomerUserID`) REFERENCES `customer` (`UserID`),
  ADD CONSTRAINT `schedule_ibfk_2` FOREIGN KEY (`OfferingID`) REFERENCES `serviceoffering` (`OfferingID`),
  ADD CONSTRAINT `schedule_ibfk_3` FOREIGN KEY (`AdminUserID`) REFERENCES `admin` (`UserID`);

--
-- Constraints for table `Vehicle`
--
ALTER TABLE `Vehicle`
  ADD CONSTRAINT `fk_vehicle_customer` FOREIGN KEY (`CustomerUserID`) REFERENCES `customer` (`UserID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
