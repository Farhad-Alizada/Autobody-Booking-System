SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autobody_db`

-- Table structure for table `admin`
CREATE TABLE `admin` (
  `UserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `admin`
INSERT INTO `admin` (`UserID`) VALUES
(1);

-- Table structure for table `customer`
CREATE TABLE `customer` (
  `UserID` int(11) NOT NULL,
  `PreferredContact` varchar(20) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `customer`
INSERT INTO `customer` (`UserID`, `PreferredContact`, `Address`) VALUES
(5, 'Phone', '765 Random St, NW, Calgary, AB, Canada'),
(8, 'Email', '576 Random St, NW, Calgary, AB, Canada');

-- Table structure for table `customerdiscountcoupon`
CREATE TABLE `customerdiscountcoupon` (
  `CouponNumber` int(11) NOT NULL,
  `CustomerUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Stand-in structure for view `d=esting`
-- (See below for the actual view)
--
CREATE TABLE `d=esting` (
`CONSTRAINT_NAME` varchar(64)
,`COLUMN_NAME` varchar(64)
,`REFERENCED_TABLE_NAME` varchar(64)
,`REFERENCED_COLUMN_NAME` varchar(64)
);

-- Table structure for table `dealswith`
CREATE TABLE `dealswith` (
  `CustomerUserID` int(11) NOT NULL,
  `EmployeeUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `discountcoupon`
CREATE TABLE `discountcoupon` (
  `CouponNumber` int(11) NOT NULL,
  `DiscountAmount` decimal(10,2) DEFAULT NULL,
  `OfferingID` int(11) DEFAULT NULL,
  `AdminUserID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `discountcoupon`
INSERT INTO `discountcoupon` (`CouponNumber`, `DiscountAmount`, `OfferingID`, `AdminUserID`) VALUES
(1, 20.00, 1, 1),
(2, 100.00, 3, 1);

-- Table structure for table `employee`
CREATE TABLE `employee` (
  `UserID` int(11) NOT NULL,
  `JobTitle` varchar(50) DEFAULT NULL,
  `Specialization` varchar(150) DEFAULT NULL,
  `Address` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employee`
INSERT INTO `employee` (`UserID`, `JobTitle`, `Specialization`, `Address`) VALUES
(2, 'Engine Tuner', 'Performance Tune', '123 Random St, NW, Calgary, AB, Canada'),
(3, 'PPF Installer', 'PPF', '456 Random St, NW, Calgary, AB'),
(4, 'Wrap Installer', 'Vinyl Wrap', '789 Random St, NW, Calgary, AB, Canada'),
(6, 'Tint Installer', 'Window Tint', '345 Random St, NW, Calgary, AB, Canada'),
(7, 'Wrap Installer Backup', 'Vinyl Wrap', '234 Random St, NW, Calgary, AB, Canada');

-- Table structure for table `employeeavailability`
CREATE TABLE `employeeavailability` (
  `AvailabilityID` int(11) NOT NULL,
  `EmployeeUserID` int(11) DEFAULT NULL,
  `AvailabilityDate` date DEFAULT NULL,
  `Status` enum('Available','Out') NOT NULL DEFAULT 'Available',
  `StartTime` time DEFAULT NULL,
  `EndTime` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `employeeavailability`
INSERT INTO `employeeavailability` (`AvailabilityID`, `EmployeeUserID`, `AvailabilityDate`, `Status`, `StartTime`, `EndTime`) VALUES
(3, 2, '2025-05-02', 'Available', '10:00:00', '11:00:00'),
(4, 2, '2025-05-02', 'Available', '12:00:00', '13:00:00'),
(5, 2, '2025-05-02', 'Available', '13:00:00', '14:00:00'),
(6, 2, '2025-05-02', 'Available', '14:00:00', '15:00:00'),
(7, 2, '2025-05-02', 'Available', '15:00:00', '16:00:00'),
(8, 2, '2025-05-05', 'Available', '08:00:00', '09:00:00'),
(9, 2, '2025-05-05', 'Available', '09:00:00', '10:00:00'),
(10, 2, '2025-05-05', 'Available', '10:00:00', '11:00:00'),
(11, 2, '2025-05-05', 'Available', '12:00:00', '13:00:00'),
(12, 2, '2025-05-05', 'Available', '13:00:00', '14:00:00'),
(13, 2, '2025-05-05', 'Available', '14:00:00', '15:00:00'),
(14, 2, '2025-05-05', 'Available', '15:00:00', '16:00:00'),
(16, 3, '2025-05-02', 'Available', '09:00:00', '10:00:00'),
(17, 3, '2025-05-02', 'Available', '10:00:00', '11:00:00'),
(18, 3, '2025-05-02', 'Available', '11:00:00', '12:00:00'),
(19, 3, '2025-05-02', 'Available', '13:00:00', '14:00:00'),
(20, 3, '2025-05-02', 'Available', '14:00:00', '15:00:00'),
(21, 3, '2025-05-02', 'Available', '15:00:00', '16:00:00'),
(22, 3, '2025-05-05', 'Available', '08:00:00', '09:00:00'),
(23, 3, '2025-05-05', 'Available', '09:00:00', '10:00:00'),
(24, 3, '2025-05-05', 'Available', '10:00:00', '11:00:00'),
(25, 3, '2025-05-05', 'Available', '11:00:00', '12:00:00'),
(26, 3, '2025-05-05', 'Available', '13:00:00', '14:00:00'),
(27, 3, '2025-05-05', 'Available', '14:00:00', '15:00:00'),
(28, 3, '2025-05-05', 'Available', '15:00:00', '16:00:00'),
(30, 4, '2025-05-02', 'Available', '09:00:00', '10:00:00'),
(31, 4, '2025-05-02', 'Available', '10:00:00', '11:00:00'),
(32, 4, '2025-05-02', 'Available', '11:00:00', '12:00:00'),
(33, 4, '2025-05-02', 'Available', '12:00:00', '13:00:00'),
(34, 4, '2025-05-02', 'Available', '14:00:00', '15:00:00'),
(35, 4, '2025-05-02', 'Available', '15:00:00', '16:00:00'),
(36, 4, '2025-05-05', 'Available', '08:00:00', '09:00:00'),
(37, 4, '2025-05-05', 'Available', '09:00:00', '10:00:00'),
(38, 4, '2025-05-05', 'Available', '10:00:00', '11:00:00'),
(39, 4, '2025-05-05', 'Available', '11:00:00', '12:00:00'),
(40, 4, '2025-05-05', 'Available', '12:00:00', '13:00:00'),
(41, 4, '2025-05-05', 'Available', '14:00:00', '15:00:00'),
(42, 4, '2025-05-05', 'Available', '15:00:00', '16:00:00'),
(43, 6, '2025-05-02', 'Available', '08:00:00', '09:00:00'),
(44, 6, '2025-05-02', 'Available', '09:00:00', '10:00:00'),
(45, 6, '2025-05-02', 'Available', '10:00:00', '11:00:00'),
(46, 6, '2025-05-02', 'Available', '14:00:00', '15:00:00'),
(49, 6, '2025-05-05', 'Available', '08:00:00', '09:00:00'),
(50, 6, '2025-05-05', 'Available', '09:00:00', '10:00:00'),
(51, 6, '2025-05-05', 'Available', '10:00:00', '11:00:00'),
(52, 6, '2025-05-05', 'Available', '14:00:00', '15:00:00'),
(53, 6, '2025-05-05', 'Available', '15:00:00', '16:00:00'),
(54, 6, '2025-05-05', 'Available', '16:00:00', '17:00:00');

-- Table structure for table `feedback`
CREATE TABLE `feedback` (
  `FeedbackID` int(11) NOT NULL,
  `CustomerUserID` int(11) DEFAULT NULL,
  `FeedbackDate` datetime DEFAULT NULL,
  `FeedbackName` varchar(50) DEFAULT NULL,
  `Comments` text DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `feedback`
INSERT INTO `feedback` (`FeedbackID`, `CustomerUserID`, `FeedbackDate`, `FeedbackName`, `Comments`, `Rating`) VALUES
(1, 5, '2025-04-21 22:49:46', 'Ahmed', 'Great Services! I would highly recommend that car enthusiasts visit these guys.', 5),
(8, 8, '2025-04-21 23:18:03', 'Haris Awan', 'The vinyl wrap and window tint installation exceeded my expectations! The wrap gave my vehicle a fresh, custom look with flawless application, while the tint significantly improved privacy and kept the interior cooler. Both services were completed with precision and attention to detail, making my car look and feel top-notch. Highly recommend!', 5);


-- Table structure for table `schedule`
CREATE TABLE `schedule` (
  `ScheduleID` int(11) NOT NULL,
  `CustomerUserID` int(11) NOT NULL,
  `OfferingID` int(11) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `TotalPrice` decimal(10,2) DEFAULT NULL,
  `AdminUserID` int(11) DEFAULT NULL,
  `VehicleID` int(11) DEFAULT NULL,
  `Status` enum('Scheduled','In Progress','Completed') DEFAULT 'Scheduled',
  `CouponNumber` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `schedule`
INSERT INTO `schedule` (`ScheduleID`, `CustomerUserID`, `OfferingID`, `StartDate`, `EndDate`, `TotalPrice`, `AdminUserID`, `VehicleID`, `Status`, `CouponNumber`) VALUES
(4, 5, 1, '2025-05-02 16:00:00', '2025-05-02 17:00:00', 79.00, 1, 5, 'Scheduled', 1),
(2, 5, 2, '2025-05-02 08:00:00', '2025-05-02 09:00:00', 2399.00, 1, 3, 'Completed', NULL),
(1, 5, 3, '2025-05-02 09:00:00', '2025-05-02 10:00:00', 699.00, 1, 2, 'In Progress', NULL),
(3, 5, 4, '2025-05-02 08:00:00', '2025-05-02 09:00:00', 1899.00, 1, 4, 'Scheduled', NULL),
(5, 8, 1, '2025-05-02 15:00:00', '2025-05-02 16:00:00', 99.00, 1, 6, 'Scheduled', NULL);

-- Table structure for table `scheduleemployee`
CREATE TABLE `scheduleemployee` (
  `CustomerUserID` int(11) NOT NULL,
  `OfferingID` int(11) NOT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime NOT NULL,
  `EmployeeUserID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `scheduleemployee`
INSERT INTO `scheduleemployee` (`CustomerUserID`, `OfferingID`, `StartDate`, `EndDate`, `EmployeeUserID`) VALUES
(5, 1, '2025-05-02 16:00:00', '2025-05-02 17:00:00', 6),
(5, 2, '2025-05-02 08:00:00', '2025-05-02 09:00:00', 3),
(5, 3, '2025-05-02 09:00:00', '2025-05-02 10:00:00', 2),
(5, 4, '2025-05-02 08:00:00', '2025-05-02 09:00:00', 4),
(8, 1, '2025-05-02 15:00:00', '2025-05-02 16:00:00', 6);

-- Table structure for table `serviceoffering`
CREATE TABLE `serviceoffering` (
  `OfferingID` int(11) NOT NULL,
  `OfferingName` varchar(100) DEFAULT NULL,
  `ServiceDescription` text DEFAULT NULL,
  `ImagePath` varchar(255) DEFAULT NULL,
  `MinPrice` decimal(10,2) NOT NULL,
  `MaxPrice` decimal(10,2) NOT NULL,
  `Currency` char(3) DEFAULT 'USD',
  `Specialization` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `serviceoffering`
INSERT INTO `serviceoffering` (`OfferingID`, `OfferingName`, `ServiceDescription`, `ImagePath`, `MinPrice`, `MaxPrice`, `Currency`, `Specialization`) VALUES
(1, 'Window Tint', 'Our window tinting service provides enhanced privacy, UV protection, and heat reduction, giving your vehicle a sleek look while protecting the interior from sun damage.', 'uploads/services/svc_6800d1db35bcc5.66099740.png', 99.00, 275.00, 'USD', 'Window Tints'),
(2, 'PPF', 'Our Paint Protection Film (PPF) service offers a clear, durable shield that protects your vehicle\'s paint from scratches, rock chips, and environmental damage while maintaining its original appearance.', 'uploads/services/svc_6800d1af4b8823.62123162.png', 2399.00, 8999.00, 'USD', 'PPF'),
(3, 'Performance Tune', 'Our performance tuning service enhances your vehicle\'s power and efficiency by optimizing the ECU, delivering improved throttle response, increased horsepower, and better fuel economy.', 'uploads/services/svc_6800d43669ae58.83227407.png', 699.00, 2199.00, 'USD', 'Performance engine retune'),
(4, 'Vinyl Wrap', 'We provide high-quality vinyl wrap installations, offering a durable, customizable solution to transform the look of your vehicle with a wide range of colors, textures, and finishes.', 'uploads/services/svc_6800d46381ccb9.55061102.png', 1899.00, 3999.00, 'USD', 'vinyl wrap');

-- Table structure for table `users`
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

-- Dumping data for table `users`
INSERT INTO `users` (`UserID`, `Password`, `PhoneNumber`, `FirstName`, `LastName`, `Email`, `AccessLevel`, `DateCreated`) VALUES
(1, '$2y$10$lRupnHcTOL7k94EPRudKruQwrqr33cNayWTWHUdnhZI56NnGngH7.', '587-000-1111', 'Roy', 'Li', 'roy.li@admin.com', 'Admin', '2025-04-15 22:33:48'),
(2, '$2y$10$xlywFDZUVxPfEklBk/ewpuSCjSnCoQdk6rD2t3fg9Q2aj2hIX9gWO', '825-111-2222', 'Harris', 'Jan', 'harris.jan@wraplab.com', 'Employee', '2025-04-21 14:18:12'),
(3, '$2y$10$p7Y2n5vwCe/Qk9nOhilfKe2zeRf8XwSI16MGiZOcA8Ph/tNtUnf/q', '403-111-2222', 'Farhad', 'Alizada', 'farhad.alizada@wraplab.com', 'Employee', '2025-04-21 14:18:49'),
(4, '$2y$10$d9TWte1lvQq9kpA0meYhTO3xf8NeYLJ6ICMKerxSFvmMxR/S/L1wS', '587-111-2222', 'Bobby', 'Brar', 'bobby.brar@wraplab.com', 'Employee', '2025-04-21 14:19:16'),
(5, '$2y$10$WDl9Y4G7L27kZM0D1egEsuarM22qFVKa7fmt/ey06oWaacHr0W/Hm', '5979998888', 'Ahmed', 'Chaudhry', 'ahmedch45@gmail.com', 'Customer', '2025-04-21 14:34:40'),
(6, '$2y$10$3D9rvgSzp7oX7h6VM9GoOObkCjP6/wHhxZcj.HFhS2T9zvw6637LK', '647-111-2222', 'Ronakh', 'Shariff', 'ronakh.shariff@wraplab.com', 'Employee', '2025-04-21 15:06:19'),
(7, '$2y$10$LMW0MkMKea/LdyvRfn4ZbO0pDF2MZd1.QvVzz1AObKrKTBsu33pLq', '604-111-2222', 'Lionel', 'Messi', 'lionel.messi@wraplab.com', 'Employee', '2025-04-21 15:12:11'),
(8, '$2y$10$4R44kgFZ.3scUvOwvrm3mOqUbLqNBGMhqdsWb0G9ufXZU8Gutzf8i', '2850001111', 'Haris', 'Awan', 'harisawan@gmail.com', 'Customer', '2025-04-21 15:17:12');

-- Table structure for table `vehicle`
CREATE TABLE `vehicle` (
  `VehicleID` int(11) NOT NULL,
  `CustomerUserID` int(11) NOT NULL,
  `Make` varchar(50) NOT NULL,
  `Model` varchar(50) NOT NULL,
  `Year` int(11) NOT NULL,
  `VINNumber` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `vehicle`
INSERT INTO `vehicle` (`VehicleID`, `CustomerUserID`, `Make`, `Model`, `Year`, `VINNumber`) VALUES
(1, 5, 'Audi', 'A4', 2018, '4GB784383'),
(2, 5, 'Audi', 'A4', 2018, '4GB784383'),
(3, 5, 'Dodge', 'Challenger SRT Hellcat', 2023, '5FB589494'),
(4, 5, 'Honda', 'Accord', 2010, '7JH684848'),
(5, 5, 'Mercedes', 'E63', 2025, '8BF4567839'),
(6, 8, 'Lexus', 'IS350', 2020, NULL);

-- Structure for view `d=esting`
DROP TABLE IF EXISTS `d=esting`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `d=esting`  AS SELECT `rc`.`CONSTRAINT_NAME` AS `CONSTRAINT_NAME`, `kcu`.`COLUMN_NAME` AS `COLUMN_NAME`, `kcu`.`REFERENCED_TABLE_NAME` AS `REFERENCED_TABLE_NAME`, `kcu`.`REFERENCED_COLUMN_NAME` AS `REFERENCED_COLUMN_NAME` FROM (`information_schema`.`referential_constraints` `rc` join `information_schema`.`key_column_usage` `kcu` on(`kcu`.`CONSTRAINT_NAME` = `rc`.`CONSTRAINT_NAME` and `kcu`.`CONSTRAINT_SCHEMA` = `rc`.`CONSTRAINT_SCHEMA`)) WHERE `rc`.`CONSTRAINT_SCHEMA` = database() AND `rc`.`TABLE_NAME` = 'ScheduleEmployee' ;

-- Indexes for table `admin`
ALTER TABLE `admin`
  ADD PRIMARY KEY (`UserID`);

-- Indexes for table `customer`
ALTER TABLE `customer`
  ADD PRIMARY KEY (`UserID`);

-- Indexes for table `customerdiscountcoupon`
ALTER TABLE `customerdiscountcoupon`
  ADD PRIMARY KEY (`CouponNumber`,`CustomerUserID`),
  ADD KEY `CustomerUserID` (`CustomerUserID`);

-- Indexes for table `dealswith`
ALTER TABLE `dealswith`
  ADD PRIMARY KEY (`CustomerUserID`,`EmployeeUserID`),
  ADD KEY `EmployeeUserID` (`EmployeeUserID`);

-- Indexes for table `discountcoupon`
ALTER TABLE `discountcoupon`
  ADD PRIMARY KEY (`CouponNumber`),
  ADD KEY `OfferingID` (`OfferingID`),
  ADD KEY `AdminUserID` (`AdminUserID`);

-- Indexes for table `employee`
ALTER TABLE `employee`
  ADD PRIMARY KEY (`UserID`);

-- Indexes for table `employeeavailability`
ALTER TABLE `employeeavailability`
  ADD PRIMARY KEY (`AvailabilityID`),
  ADD KEY `EmployeeUserID` (`EmployeeUserID`);

-- Indexes for table `feedback`
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FeedbackID`),
  ADD KEY `CustomerUserID` (`CustomerUserID`);

-- Indexes for table `schedule`
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`CustomerUserID`,`OfferingID`,`StartDate`,`EndDate`),
  ADD UNIQUE KEY `ScheduleID` (`ScheduleID`),
  ADD UNIQUE KEY `ScheduleID_2` (`ScheduleID`),
  ADD KEY `OfferingID` (`OfferingID`),
  ADD KEY `AdminUserID` (`AdminUserID`),
  ADD KEY `VehicleID` (`VehicleID`),
  ADD KEY `fk_schedule_coupon` (`CouponNumber`);

-- Indexes for table `scheduleemployee`
ALTER TABLE `scheduleemployee`
  ADD PRIMARY KEY (`CustomerUserID`,`OfferingID`,`StartDate`,`EndDate`,`EmployeeUserID`),
  ADD KEY `EmployeeUserID` (`EmployeeUserID`);

-- Indexes for table `serviceoffering`
ALTER TABLE `serviceoffering`
  ADD PRIMARY KEY (`OfferingID`);

-- Indexes for table `users`
ALTER TABLE `users`
  ADD PRIMARY KEY (`UserID`),
  ADD UNIQUE KEY `Email` (`Email`);

-- Indexes for table `vehicle`
ALTER TABLE `vehicle`
  ADD PRIMARY KEY (`VehicleID`),
  ADD KEY `CustomerUserID` (`CustomerUserID`);

-- AUTO_INCREMENT for table `discountcoupon`
ALTER TABLE `discountcoupon`
  MODIFY `CouponNumber` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

-- AUTO_INCREMENT for table `employeeavailability`
ALTER TABLE `employeeavailability`
  MODIFY `AvailabilityID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

-- AUTO_INCREMENT for table `feedback`
ALTER TABLE `feedback`
  MODIFY `FeedbackID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

-- AUTO_INCREMENT for table `schedule`
ALTER TABLE `schedule`
  MODIFY `ScheduleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- AUTO_INCREMENT for table `serviceoffering`
ALTER TABLE `serviceoffering`
  MODIFY `OfferingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- AUTO_INCREMENT for table `users`
ALTER TABLE `users`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

-- AUTO_INCREMENT for table `vehicle`
ALTER TABLE `vehicle`
  MODIFY `VehicleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

-- Constraints for table `admin`
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`);

-- Constraints for table `customer`
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `users` (`UserID`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `customerdiscountcoupon`
ALTER TABLE `customerdiscountcoupon`
  ADD CONSTRAINT `customerdiscountcoupon_ibfk_1` FOREIGN KEY (`CouponNumber`) REFERENCES `discountcoupon` (`CouponNumber`),
  ADD CONSTRAINT `customerdiscountcoupon_ibfk_2` FOREIGN KEY (`CustomerUserID`) REFERENCES `customer` (`UserID`);

-- Constraints for table `discountcoupon`
ALTER TABLE `discountcoupon`
  ADD CONSTRAINT `discountcoupon_ibfk_1` FOREIGN KEY (`OfferingID`) REFERENCES `serviceoffering` (`OfferingID`),
  ADD CONSTRAINT `discountcoupon_ibfk_2` FOREIGN KEY (`AdminUserID`) REFERENCES `admin` (`UserID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;