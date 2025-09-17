-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 04:34 AM
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
-- Database: `leave`
--

-- --------------------------------------------------------

--
-- Table structure for table `dayoff`
--

CREATE TABLE `dayoff` (
  `Dayoff_ID` int(11) NOT NULL,
  `Dayoff_Name` varchar(50) NOT NULL,
  `Dayoff_start_date` date NOT NULL,
  `Dayoff_end_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dayoff`
--

INSERT INTO `dayoff` (`Dayoff_ID`, `Dayoff_Name`, `Dayoff_start_date`, `Dayoff_end_date`) VALUES
(1, 'วันแม่', '2025-08-11', '2025-08-12'),
(2, 'อยากหยุด', '2025-08-26', '2025-08-27'),
(3, 'อยากหยุด2', '2025-10-04', '2025-10-05');

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `Emp_id` varchar(5) NOT NULL,
  `Prefix_ID` varchar(2) NOT NULL,
  `Position_ID` int(2) NOT NULL,
  `Emp_Name` varchar(50) NOT NULL,
  `Emp_LastName` varchar(50) NOT NULL,
  `Email` varchar(30) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `Created_at` date NOT NULL,
  `Gender` varchar(10) NOT NULL,
  `Birthdate` date NOT NULL,
  `ID_Card_Number` varchar(13) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Emp_pic` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`Emp_id`, `Prefix_ID`, `Position_ID`, `Emp_Name`, `Emp_LastName`, `Email`, `Address`, `Created_at`, `Gender`, `Birthdate`, `ID_Card_Number`, `Password`, `Emp_pic`) VALUES
('em001', '1', 1, 'ก๊อง', 'สมศักท์', 'nine@gmail.com', '20250 ซอยนาจอมเทียน 13', '2024-06-03', 'ชาย', '2007-06-07', '1789012345678', '$2y$10$z2YQzgHib5dC5giTG4B4puolMH/2tNfWfOWRFgrgJ5PnN5GJ90hgK', 'lywC5Q3G.jpg'),
('em002', '1', 5, 'เจษฎา', 'มีเกียรติ', 'jed@gmail.com', '20250 ซอยนาจอมเที่ยน 11', '2024-06-12', 'หญิง', '2004-06-11', '1345678901234', '$2y$10$E8fo7yiT.x0UnnzPwe99P.XHKxXntgzATClJ4.2NCWqyCAnBgAv5K', NULL),
('em003', '2', 2, 'มาร์ค', 'มาเกช', 'mark@gmail.com', '20250 ซอยนาจอมเที่ยน 15', '2024-05-03', 'ชาย', '2002-04-09', '1123456789012', '$2y$10$/8eSoBCEKbWPWdIzu3ZOieTHiAxc5vFzYv2t3YV52Pky79mk0qhMW', NULL),
('em004', '1', 3, 'วอเลนติโน่', 'ลอชซี่', 'voren@gmail.com', '20250 ซอยนาจอมเที่ยน 9', '2024-01-03', 'ชาย', '2002-12-01', '1901234567890', '$2y$10$Ew.Y7y.hCR/aCIg2lEaM9uV0zEqp.24sR7K4.yDDUu0C6yWk5w0/a', NULL),
('em005', '1', 4, 'มิกเกล', 'ดุฮาน', 'mick@gmail.com', '20250 ซอยนาจอมเทียน 12', '2024-01-12', 'ชาย', '2002-04-01', '1567890123456', '$2y$10$z2YQzgHib5dC5giTG4B4puolMH/2tNfWfOWRFgrgJ5PnN5GJ90hgK', NULL),
('em006', '3', 2, 'a', 'b', 'employee006@company.com', '', '2025-08-27', '', '0000-00-00', '', '$2y$10$HIO9mi5wHAsaGmM8LsLub.f9ELrHYL6N5L/ZFIEBkhEnWwhn0mg.K', 'W3jVYvxQ.jpg'),
('em007', '2', 3, 'ซัน', 'b', 'employee007@company.com', '', '2025-08-27', '', '0000-00-00', '', '$2y$10$d6FbRd7ev2zciOAdGr5a4OZpYzgKH/GvPl2E8nAwWXRrWxf0Zpswq', 'PtyxgGpI.jpg'),
('em008', '2', 3, 'พงศกร', 'ธนะสัมบันธ์', 'employee008@company.com', 'บ้าน', '2025-09-03', 'ชาย', '2007-01-07', '1659902322666', '$2y$10$m4zcfXSvkiW2BY0dGSrkgeiFTYjxKeFjB0JBB3a3bXEzmCxRv7Da6', 'ZyV3D9Ga.jpg'),
('em011', '2', 5, 'กร', 'นน', 'em011@gmail.com', '123', '2025-09-15', 'ชาย', '2547-10-05', '1234567891011', '$2y$10$bxdX7FAMUl24.7qcQvIp/OGXTYduqEzvGqxnaJI3Dmg3dckPHtxLu', 'vQ71sFNk.jpg'),
('EM013', '2', 2, 'Sun', 'Ny', 'em013@gmail.com', '123', '2025-09-17', 'ชาย', '2000-01-01', '1234568901212', '$2y$10$zPubK8Ct97tRQqAoaNBV1u.ZJrhMGms99a8hzq2UzVh6Xtaw9PkI.', 'JEz8sXMY.png');

-- --------------------------------------------------------

--
-- Table structure for table `emp_leave`
--

CREATE TABLE `emp_leave` (
  `Leave_ID` varchar(10) NOT NULL,
  `Leave_Type_ID` int(2) NOT NULL,
  `Leave_Time_ID` int(2) NOT NULL,
  `Emp_ID` varchar(5) NOT NULL,
  `Leave_Status_ID` int(2) NOT NULL,
  `Dayoff_ID` int(11) NOT NULL,
  `Reason` varchar(50) DEFAULT NULL,
  `Start_leave_date` date NOT NULL,
  `End_Leave_date` date NOT NULL,
  `Approved_date` date NOT NULL,
  `Request_date` date NOT NULL,
  `Document_File` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `emp_leave`
--

INSERT INTO `emp_leave` (`Leave_ID`, `Leave_Type_ID`, `Leave_Time_ID`, `Emp_ID`, `Leave_Status_ID`, `Dayoff_ID`, `Reason`, `Start_leave_date`, `End_Leave_date`, `Approved_date`, `Request_date`, `Document_File`) VALUES
('L175626568', 2, 3, 'em001', 1, 0, 'test', '2025-08-31', '2025-08-31', '2025-09-02', '2025-08-27', 'ctUGWJTp.pdf'),
('L175678402', 2, 3, 'em003', 1, 0, 'Nah', '2025-09-03', '2025-09-03', '2025-09-02', '2025-09-02', 'D39zbMIa.png'),
('L175678405', 1, 3, 'em002', 1, 0, 'Bar', '2025-09-04', '2025-09-05', '2025-09-02', '2025-09-02', '59U7Vz3c.jpg'),
('L175678439', 1, 3, 'em001', 1, 0, 'aaa', '2025-09-05', '2025-09-05', '2025-09-02', '2025-09-02', '3UYLk1Ip.png'),
('L175678442', 2, 3, 'em002', 1, 0, 'asa', '2025-09-14', '2025-09-15', '2025-09-02', '2025-09-02', 'oJc19KiL.png'),
('L175680307', 1, 3, 'em002', 2, 0, '123', '2025-09-03', '2025-09-03', '2025-09-02', '2025-09-02', 'hnVNMpX1.jpg'),
('L175680388', 3, 3, 'em003', 1, 0, 'asdf', '2025-09-03', '2025-09-04', '2025-09-02', '2025-09-02', 'OGmat5Yo.jpg'),
('L175680443', 3, 3, 'em003', 2, 0, 'KK', '2025-09-10', '2025-09-17', '2025-09-02', '2025-09-02', 'ik6VDUXv.jpg'),
('L175685385', 1, 3, 'em006', 2, 0, 'ททท', '2025-09-06', '2025-09-07', '2025-09-03', '2025-09-03', 'xkbyh5is.jpg'),
('L175685677', 1, 3, 'em006', 1, 0, 'aaa', '2025-09-04', '2025-09-04', '2025-09-03', '2025-09-03', 'stuJwONh.png'),
('L175686893', 2, 3, 'em001', 1, 0, 'm', '2025-09-04', '2025-09-05', '2025-09-03', '2025-09-03', 'frKEqpkb.png'),
('L175686931', 3, 3, 'em008', 1, 0, 'บิด', '2025-09-07', '2025-09-14', '2025-09-03', '2025-09-03', 'Z2mjeWtB.png');

-- --------------------------------------------------------

--
-- Table structure for table `leave_status`
--

CREATE TABLE `leave_status` (
  `Leave_Status_ID` int(2) NOT NULL,
  `Leave_Type_Name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_status`
--

INSERT INTO `leave_status` (`Leave_Status_ID`, `Leave_Type_Name`) VALUES
(1, 'อนุมัติ'),
(2, 'ไม่อนุมัติ'),
(3, 'รออนุมัติ');

-- --------------------------------------------------------

--
-- Table structure for table `leave_time_type`
--

CREATE TABLE `leave_time_type` (
  `Leave_time_ID` int(2) NOT NULL,
  `Leave_Type_ID` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_time_type`
--

INSERT INTO `leave_time_type` (`Leave_time_ID`, `Leave_Type_ID`) VALUES
(1, '08:00-13:00'),
(2, '13:00-18:00'),
(3, '08:00-18:00');

-- --------------------------------------------------------

--
-- Table structure for table `leave_type`
--

CREATE TABLE `leave_type` (
  `Leave_Type_ID` int(2) NOT NULL,
  `Leave_Type_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_type`
--

INSERT INTO `leave_type` (`Leave_Type_ID`, `Leave_Type_Name`) VALUES
(1, 'ลาป่วย'),
(2, 'ลากิจ'),
(3, 'ลาบวช'),
(4, 'ลาคลอดบุตร'),
(5, 'ลาไปช่วยภริยาที่คลอด');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `emp_id` varchar(5) NOT NULL,
  `type` varchar(50) NOT NULL COMMENT 'approved, rejected, new_request, system, warning',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=unread, 1=read',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `emp_id`, `type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 'em001', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลาป่วย วันที่ 15-17 มิ.ย. 2024 ได้รับการอนุมัติแล้ว', 1, '2025-08-11 12:54:37'),
(2, 'em001', 'new_request', 'มีคำขอใหม่รอการอนุมัติ', 'สมชาย ใจดี ขอลาป่วย วันที่ 20 มิ.ย. 2024', 1, '2025-08-11 12:54:37'),
(3, 'em001', 'system', 'ระบบสำรองข้อมูลเสร็จสิ้น', 'การสำรองข้อมูลอัตโนมัติเสร็จสิ้นเรียบร้อยแล้ว', 1, '2025-08-11 12:54:37'),
(4, 'em001', 'warning', 'แจ้งเตือนวันลาใกล้หมด', 'วันลากิจของคุณเหลือเพียง 2 วัน', 1, '2025-08-11 12:54:37'),
(5, 'em001', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลากิจ ของคุณ (31 Aug 2025 - 31 Aug 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-02 03:31:58'),
(6, 'em001', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลาป่วย ของคุณ (28 Aug 2025 - 29 Aug 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-02 03:31:59'),
(7, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: ก๊อง (em001) ขอลา: ลาป่วย วันที่ 2025-09-05 ถึง 2025-09-05', 1, '2025-09-02 03:39:56'),
(8, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: เจษฎา (em002) ขอลา: ลากิจ วันที่ 2025-09-14 ถึง 2025-09-15', 1, '2025-09-02 03:40:25'),
(9, 'em002', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลากิจ ของคุณ (14 Sep 2025 - 15 Sep 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-02 03:51:34'),
(10, 'em003', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลากิจ ของคุณ (03 Sep 2025 - 03 Sep 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-02 03:51:35'),
(11, 'em001', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลาป่วย ของคุณ (05 Sep 2025 - 05 Sep 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-02 03:51:35'),
(12, 'em002', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลาป่วย ของคุณ (04 Sep 2025 - 05 Sep 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-02 03:51:35'),
(13, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: มาร์ค (em003) ขอลา: ลาบวช วันที่ 2025-09-10 ถึง 2025-09-17', 1, '2025-09-02 09:13:59'),
(14, 'em003', 'rejected', 'คำขอลาถูกปฏิเสธ', 'คำขอลา ลาบวช ของคุณ (10 Sep 2025 - 17 Sep 2025) ไม่ได้รับการอนุมัติ', 1, '2025-09-02 09:14:22'),
(15, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: a (em006) ขอลา: ลาป่วย วันที่ 2025-09-06 ถึง 2025-09-07', 1, '2025-09-02 22:57:31'),
(16, 'em006', 'rejected', 'คำขอลาถูกปฏิเสธ', 'คำขอลา ลาป่วย ของคุณ (06 Sep 2025 - 07 Sep 2025) ไม่ได้รับการอนุมัติ', 1, '2025-09-02 23:42:26'),
(17, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: a (em006) ขอลา: ลาป่วย วันที่ 2025-09-04 ถึง 2025-09-04', 1, '2025-09-02 23:46:12'),
(18, 'em006', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลาป่วย ของคุณ (04 Sep 2025 - 04 Sep 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-02 23:46:34'),
(19, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: ก๊อง (em001) ขอลา: ลากิจ วันที่ 2025-09-04 ถึง 2025-09-05', 1, '2025-09-03 03:08:58'),
(20, 'em001', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลากิจ ของคุณ (04 Sep 2025 - 05 Sep 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-03 03:10:12'),
(21, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: พงศกร (em008) ขอลา: ลาบวช วันที่ 2025-09-07 ถึง 2025-09-14', 1, '2025-09-03 03:15:14'),
(22, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: มาร์ค (em003) ขอลา: ลาป่วย วันที่ 2025-09-04 ถึง 2025-09-04', 1, '2025-09-03 03:16:55'),
(23, 'em008', 'approved', 'คำขอลาได้รับการอนุมัติ', 'คำขอลา ลาบวช ของคุณ (07 Sep 2025 - 14 Sep 2025) ได้รับการอนุมัติแล้ว', 1, '2025-09-03 03:18:26'),
(24, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: ก๊อง (em001) ขอลา: ลาป่วย วันที่ 2025-09-08 ถึง 2025-09-09', 1, '2025-09-07 11:38:23'),
(25, 'em001', 'rejected', 'คำขอลาถูกปฏิเสธ', 'คำขอลา ลาป่วย ของคุณ (08 Sep 2025 - 09 Sep 2025) ไม่ได้รับการอนุมัติ', 1, '2025-09-07 11:39:37'),
(26, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: พงศกร (em008) ขอลา: ลาบวช วันที่ 2025-09-09 ถึง 2025-09-16', 1, '2025-09-08 08:36:23'),
(27, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: พงศกร (em008) ขอลา: ลาป่วย วันที่ 2025-09-17 ถึง 2025-09-18', 1, '2025-09-08 08:37:06'),
(28, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: ก๊อง (em001) ขอลา: ลากิจ วันที่ 2025-09-18 ถึง 2025-09-22', 1, '2025-09-17 01:44:06'),
(29, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: ก๊อง (em001) ขอลา: ลากิจ วันที่ 2025-09-18 ถึง 2025-09-22', 1, '2025-09-17 01:50:23'),
(30, 'em005', 'new_request', 'มีคำขอลาใหม่รออนุมัติ', 'พนักงาน: ก๊อง (em001) ขอลา: ลากิจ วันที่ 2025-09-24 ถึง 2025-09-25', 1, '2025-09-17 01:50:40');

-- --------------------------------------------------------

--
-- Table structure for table `position`
--

CREATE TABLE `position` (
  `Position_ID` int(2) NOT NULL,
  `Position_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `position`
--

INSERT INTO `position` (`Position_ID`, `Position_Name`) VALUES
(1, 'พนักงานขาย'),
(2, 'พนักงานหน้าร้าน'),
(3, 'พนักงานครัว'),
(4, 'ผู้จัดการร้าน'),
(5, 'พนักงานเสิร์ฟ');

-- --------------------------------------------------------

--
-- Table structure for table `prefix`
--

CREATE TABLE `prefix` (
  `Prefix_ID` int(2) NOT NULL,
  `Prefix_Name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prefix`
--

INSERT INTO `prefix` (`Prefix_ID`, `Prefix_Name`) VALUES
(1, 'นาง'),
(2, 'นาย'),
(3, 'นางสาว');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dayoff`
--
ALTER TABLE `dayoff`
  ADD PRIMARY KEY (`Dayoff_ID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`Emp_id`),
  ADD KEY `Prefix_ID` (`Prefix_ID`),
  ADD KEY `Position_ID` (`Position_ID`);

--
-- Indexes for table `emp_leave`
--
ALTER TABLE `emp_leave`
  ADD PRIMARY KEY (`Leave_ID`),
  ADD KEY `Emp_ID` (`Emp_ID`),
  ADD KEY `Leave_Type_ID` (`Leave_Type_ID`),
  ADD KEY `Leave_Time_ID` (`Leave_Time_ID`),
  ADD KEY `Leave_Status_ID` (`Leave_Status_ID`),
  ADD KEY `Dayoff_ID` (`Dayoff_ID`);

--
-- Indexes for table `leave_status`
--
ALTER TABLE `leave_status`
  ADD PRIMARY KEY (`Leave_Status_ID`);

--
-- Indexes for table `leave_time_type`
--
ALTER TABLE `leave_time_type`
  ADD PRIMARY KEY (`Leave_time_ID`);

--
-- Indexes for table `leave_type`
--
ALTER TABLE `leave_type`
  ADD PRIMARY KEY (`Leave_Type_ID`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`Position_ID`);

--
-- Indexes for table `prefix`
--
ALTER TABLE `prefix`
  ADD PRIMARY KEY (`Prefix_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dayoff`
--
ALTER TABLE `dayoff`
  MODIFY `Dayoff_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_status`
--
ALTER TABLE `leave_status`
  MODIFY `Leave_Status_ID` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_time_type`
--
ALTER TABLE `leave_time_type`
  MODIFY `Leave_time_ID` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_type`
--
ALTER TABLE `leave_type`
  MODIFY `Leave_Type_ID` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `Position_ID` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prefix`
--
ALTER TABLE `prefix`
  MODIFY `Prefix_ID` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
