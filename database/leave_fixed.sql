-- phpMyAdmin SQL Dump (Modified)
-- Fixed version for XAMPP import
-- Changes:
-- 1. Fixed 0000-00-00 -> NULL (with DEFAULT NULL)
-- 2. Fixed Primary Key of position_detail to Composite Key
-- 3. Standardized Leave_Type_ID as INT across tables
-- 4. Added Foreign Keys

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `leave` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for table `dayoff`
CREATE TABLE `dayoff` (
  `Dayoff_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Dayoff_Name` varchar(50) NOT NULL,
  `Dayoff_start_date` date NOT NULL,
  `Dayoff_end_date` date NOT NULL,
  PRIMARY KEY (`Dayoff_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `dayoff` (`Dayoff_ID`, `Dayoff_Name`, `Dayoff_start_date`, `Dayoff_end_date`) VALUES
(1, 'วันแม่', '2025-08-11', '2025-08-12'),
(2, 'อยากหยุด', '2025-08-26', '2025-08-27'),
(3, 'อยากหยุด2', '2025-10-04', '2025-10-05');

-- --------------------------------------------------------
-- Table structure for table `employee`
CREATE TABLE `employee` (
  `Emp_id` varchar(5) NOT NULL,
  `Prefix_ID` varchar(2) NOT NULL,
  `Position_ID` int(11) NOT NULL,
  `Emp_Name` varchar(30) NOT NULL,
  `Emp_LastName` varchar(30) NOT NULL,
  `Email` varchar(30) NOT NULL,
  `Address` varchar(100) NOT NULL,
  `Created_at` date NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `Birthdate` date DEFAULT NULL,
  `ID_Card_Number` varchar(13) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Emp_pic` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Emp_id`),
  KEY `Position_ID` (`Position_ID`),
  KEY `Prefix_ID` (`Prefix_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `employee` VALUES
('em001', '1', 1, 'ก๊อง', 'สมศักท์', 'nine@gmail.com', '20250 ซอยนาจอมเทียน 13', '2024-06-03', 'ชาย', '2007-06-07', '1789012345678', '$2y$10$z2YQzgHib5dC5giTG4B4puolMH/2tNfWfOWRFgrgJ5PnN5GJ90hgK', 'lywC5Q3G.jpg'),
('em002', '1', 5, 'เจษฎา', 'มีเกียรติ', 'jed@gmail.com', '20250 ซอยนาจอมเที่ยน 11', '2024-06-12', 'หญิง', '2004-06-11', '1345678901234', '$2y$10$Ew.Y7y.hCR/aCIg2lEaM9uV0zEqp.24sR7K4.yDDUu0C6yWk5w0/a', NULL),
('em003', '2', 2, 'มาร์ค', 'มาเกช', 'mark@gmail.com', '20250 ซอยนาจอมเที่ยน 15', '2024-05-03', 'ชาย', '2002-04-09', '1123456789012', '$2y$10$Ew.Y7y.hCR/aCIg2lEaM9uV0zEqp.24sR7K4.yDDUu0C6yWk5w0/a', NULL),
('em004', '1', 3, 'วอเลนติโน่', 'ลอชซี่', 'voren@gmail.com', '20250 ซอยนาจอมเที่ยน 9', '2024-01-03', 'ชาย', '2002-12-01', '1901234567890', '$2y$10$Ew.Y7y.hCR/aCIg2lEaM9uV0zEqp.24sR7K4.yDDUu0C6yWk5w0/a', NULL),
('em005', '1', 4, 'มิกเกล', 'ดุฮาน', 'mick@gmail.com', '20250 ซอยนาจอมเทียน 12', '2024-01-12', 'ชาย', '2002-04-01', '1567890123456', '$2y$10$z2YQzgHib5dC5giTG4B4puolMH/2tNfWfOWRFgrgJ5PnN5GJ90hgK', NULL),
('em006', '3', 2, 'a', 'b', 'employee006@company.com', '', '2025-08-27', '', NULL, '', '$2y$10$HIO9mi5wHAsaGmM8LsLub.f9ELrHYL6N5L/ZFIEBkhEnWwhn0mg.K', 'W3jVYvxQ.jpg'),
('em007', '2', 3, 'ก๊อง', 'b', 'employee007@company.com', '', '2025-08-27', '', NULL, '', '$2y$10$d6FbRd7ev2zciOAdGr5a4OZpYzgKH/GvPl2E8nAwWXRrWxf0Zpswq', 'q342CrBB.jpg');

-- --------------------------------------------------------
-- Table structure for table `emp_leave`
CREATE TABLE `emp_leave` (
  `Leave_ID` varchar(10) NOT NULL,
  `Leave_Type_ID` int(11) NOT NULL,
  `Leave_Time_ID` varchar(2) NOT NULL,
  `Emp_ID` varchar(5) NOT NULL,
  `Leave_Status_ID` varchar(2) NOT NULL,
  `Dayoff_ID` int(11) DEFAULT NULL,
  `Reason` varchar(50) DEFAULT NULL,
  `Start_leave_date` date NOT NULL,
  `End_Leave_date` date NOT NULL,
  `Approved_date` date DEFAULT NULL,
  `Request_date` date NOT NULL,
  `Attach_medCertificate` varchar(10) DEFAULT NULL,
  `Document_File` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`Leave_ID`),
  KEY `Leave_Type_ID` (`Leave_Type_ID`),
  KEY `Leave_Time_ID` (`Leave_Time_ID`),
  KEY `Emp_ID` (`Emp_ID`),
  KEY `Leave_Status_ID` (`Leave_Status_ID`),
  KEY `Dayoff_ID` (`Dayoff_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `emp_leave` VALUES
('L175626568', 2, '03', 'em001', '3', NULL, 'test', '2025-08-31', '2025-08-31', NULL, '2025-08-27', NULL, 'ctUGWJTp.pdf'),
('L175626593', 1, '03', 'em001', '3', NULL, 'อยากนอน', '2025-08-28', '2025-08-29', NULL, '2025-08-27', NULL, 'gUhfwkSM.pdf');

-- --------------------------------------------------------
-- Table structure for table `leave_status`
CREATE TABLE `leave_status` (
  `Leave_Status_ID` varchar(2) NOT NULL,
  `Leave_Type_Name` varchar(20) NOT NULL,
  PRIMARY KEY (`Leave_Status_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `leave_status` VALUES
('1', 'อนุมัติ'),
('2', 'ไม่อนุมัติ'),
('3', 'รออนุมัติ');

-- --------------------------------------------------------
-- Table structure for table `leave_time_type`
CREATE TABLE `leave_time_type` (
  `Leave_time_ID` varchar(2) NOT NULL,
  `Leave_Type_Name` varchar(20) NOT NULL,
  PRIMARY KEY (`Leave_time_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `leave_time_type` VALUES
('01', '08:00-13:00'),
('02', '13:00-18:00'),
('03', '08:00-18:00');

-- --------------------------------------------------------
-- Table structure for table `leave_type`
CREATE TABLE `leave_type` (
  `Leave_Type_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Leave_Type_Name` varchar(50) NOT NULL,
  PRIMARY KEY (`Leave_Type_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `leave_type` VALUES
(1, 'ลาป่วย'),
(2, 'ลากิจ'),
(3, 'ลาบวช'),
(4, 'ลาคลอดบุตร'),
(5, 'ลาไปช่วยภริยาที่คลอด');

-- --------------------------------------------------------
-- Table structure for table `notifications`
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(5) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'approved, rejected, new_request, system, warning',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=unread, 1=read',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notifications` VALUES
(1, 'em001', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลาป่วย วันที่ 15-17 มิ.ย. 2024 ได้รับการอนุมัติแล้ว', 1, '2025-08-11 12:54:37'),
(2, 'em001', 'new_request', 'มีคำขอใหม่รอการอนุมัติ', 'สมชาย ใจดี ขอลาป่วย วันที่ 20 มิ.ย. 2024', 1, '2025-08-11 12:54:37'),
(3, 'em001', 'system', 'ระบบสำรองข้อมูลเสร็จสิ้น', 'การสำรองข้อมูลอัตโนมัติเสร็จสิ้นเรียบร้อยแล้ว', 1, '2025-08-11 12:54:37'),
(4, 'em001', 'warning', 'แจ้งเตือนวันลาใกล้หมด', 'วันลากิจของคุณเหลือเพียง 2 วัน', 1, '2025-08-11 12:54:37');

-- --------------------------------------------------------
-- Table structure for table `position`
CREATE TABLE `position` (
  `Position_ID` int(11) NOT NULL AUTO_INCREMENT,
  `Position_Name` varchar(50) NOT NULL,
  PRIMARY KEY (`Position_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `position` VALUES
(1, 'พนักงานขาย'),
(2, 'พนักงานหน้าร้าน'),
(3, 'พนักงานครัว'),
(4, 'ผู้จัดการร้าน'),
(5, 'พนักงานเสิร์ฟ');

-- --------------------------------------------------------
-- Table structure for table `position_detail`
CREATE TABLE `position_detail` (
  `Position_ID` int(11) NOT NULL,
  `Emp_ID` varchar(5) NOT NULL,
  PRIMARY KEY (`Position_ID`, `Emp_ID`),
  KEY `Emp_ID` (`Emp_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `position_detail` VALUES
(1, 'em001'),
(5, 'em002'),
(2, 'em003'),
(3, 'em004'),
(4, 'em005');

-- --------------------------------------------------------
-- Table structure for table `prefix`
CREATE TABLE `prefix` (
  `Prefix_ID` varchar(2) NOT NULL,
  `Prefix_Name` varchar(20) NOT NULL,
  PRIMARY KEY (`Prefix_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `prefix` VALUES
('1', 'นาง'),
('2', 'นาย'),
('3', 'นางสาว');

-- --------------------------------------------------------
-- Foreign Keys
ALTER TABLE `employee`
  ADD CONSTRAINT `fk_employee_prefix` FOREIGN KEY (`Prefix_ID`) REFERENCES `prefix`(`Prefix_ID`),
  ADD CONSTRAINT `fk_employee_position` FOREIGN KEY (`Position_ID`) REFERENCES `position`(`Position_ID`);

ALTER TABLE `emp_leave`
  ADD CONSTRAINT `fk_leave_type` FOREIGN KEY (`Leave_Type_ID`) REFERENCES `leave_type`(`Leave_Type_ID`),
  ADD CONSTRAINT `fk_leave_time` FOREIGN KEY (`Leave_Time_ID`) REFERENCES `leave_time_type`(`Leave_time_ID`),
  ADD CONSTRAINT `fk_leave_emp` FOREIGN KEY (`Emp_ID`) REFERENCES `employee`(`Emp_id`),
  ADD CONSTRAINT `fk_leave_status` FOREIGN KEY (`Leave_Status_ID`) REFERENCES `leave_status`(`Leave_Status_ID`),
  ADD CONSTRAINT `fk_leave_dayoff` FOREIGN KEY (`Dayoff_ID`) REFERENCES `dayoff`(`Dayoff_ID`);

ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notif_emp` FOREIGN KEY (`emp_id`) REFERENCES `employee`(`Emp_id`);

ALTER TABLE `position_detail`
  ADD CONSTRAINT `fk_posdetail_position` FOREIGN KEY (`Position_ID`) REFERENCES `position`(`Position_ID`),
  ADD CONSTRAINT `fk_posdetail_emp` FOREIGN KEY (`Emp_ID`) REFERENCES `employee`(`Emp_id`);

COMMIT;
