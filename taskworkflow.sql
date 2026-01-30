-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 27, 2025 at 04:49 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taskworkflow`
--

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `employeeID` varchar(50) NOT NULL,
  `employeeEmail` varchar(255) NOT NULL,
  `teamID` int(11) DEFAULT NULL,
  `roles` varchar(50) NOT NULL,
  `employeeFullName` varchar(255) NOT NULL,
  `employeeIC` varchar(100) NOT NULL,
  `employeeNoPhone` varchar(20) DEFAULT NULL,
  `employeeDOB` date DEFAULT NULL,
  `employeePicture` varchar(255) DEFAULT NULL,
  `employeeAddress` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`employeeID`, `employeeEmail`, `teamID`, `roles`, `employeeFullName`, `employeeIC`, `employeeNoPhone`, `employeeDOB`, `employeePicture`, `employeeAddress`) VALUES
('2025118319', '2025118319@staffGFM.my', 3, 'Leader', 'Hakim Ismail', '970303-08-1112', '01123456782', '2005-12-23', 'HakimIsmail1750266078.jpg', 'A-12-3, Flat Sentul, KL Kuala Lumpur'),
('2025137655', '2025137655@staffGFM.my', 4, 'Team Member', 'Haziq Kamal', '910909-07-5566', '01123456788', '1985-05-22', 'HaziqKamal1750266460.jpg', 'Lot 66, Jalan Gombak, Kuantan Pahang'),
('2025154794', '2025154794@staffGFM.my', 2, 'Team Member', 'Naufal Hakimi', '050506-10-0515', '011-16467016', '1997-11-21', 'NaufalHakimi1750296825.jpg', '98, Jalan Bukit Gambir, Gelugor, 11700 Georgetown Selangor'),
('2025194894', '2025194894@staffGFM.my', 2, 'Leader', 'Aiman Ramli', '981202-14-4321', '01123456781', '2002-05-15', 'AimanRamli1750257441.jpg', 'No 5, Taman Bahagia, Ipoh Perak'),
('2025219860', '2025219860@staffGFM.my', 4, 'Leader', 'Farhan Othman', '960404-05-8765', '01123456783', '1998-11-19', 'FarhanOthman1750266127.jpg', 'No 8, Jalan Mawar, Johor Bahru Johor'),
('2025285147', '2025285147@staffGFM.my', 1, 'Team Member', 'Syafiq Rahman', '940606-11-4455', '01123456785', '1991-07-11', 'SyafiqRahman1750266235.jpg', 'No 45, Jalan Pantai, Kuala Terengganu Terengganu '),
('2025368917', '2025368917@staffGFM.my', 1, 'Leader', 'Azri Ibrahim', '900202-13-0099', '01123456799', '1981-12-31', 'AzriIbrahim1750268694.jpg', 'No 6, Kg Air Panas, Port Dickson Negeri Sembilan'),
('2025402673', '2025402673@staffGFM.my', 4, 'Team Member', 'Shah Yahya', '980404-05-2345', '01123456801', '2002-09-04', 'ShahYahya1750268790.jpg', 'No 45, Jalan Stadium, Dungun Terengganu'),
('2025419674', '2025419674@staffGFM.my', 3, 'Team Member', 'Hafizi Nordin', '950606-08-3344 ', '01123456794', '1997-01-05', 'HafiziNordin1750268304.jpg', 'No 77, Taman Impian, Klang Selangor'),
('2025451342', '2025451342@staffGFM.my', 1, 'Team Member', 'Nabil Kamarul', '990202-06-8888', '01123456790', '1987-07-03', 'NabilKamarul1750266554.jpg', 'No 9, Jalan Sena, Kangar Perlis'),
('2025526544', '2025526544@staffGFM.my', 2, 'Team Member', 'Firdaus Bakri', '960505-02-4567', '01123456793 ', '1997-09-11', 'FirdausBakri1750268255.jpg', 'No 99, Jalan Kenyalang, Kuching Sarawak'),
('2025637286', '2025637286@staffGFM.my', 2, 'Team Member', 'Zulhilmi Azmi', '930707-12-1122 ', '01123456786', '1998-10-13', 'ZulhilmiAzmi1750266338.jpg', 'No 17, Taman Bukit, Melaka Melaka'),
('2025647668', '2025647668@staffGFM.my', 4, 'Team Member', 'Nasrul Rahim', '930808-10-2222', '01123456796', '1994-01-08', 'NasrulRahim1750268409.jpg', 'No 5, Jalan Laut, WP Labuan Labuan'),
('2025666757', '2025666757@staffGFM.my', 3, 'Team Member', 'Luqman  Yusof', '940707-01-7788', '01123456795 ', '1999-05-21', 'LuqmanYusof1750268350.jpg', 'Q-3-2, Presint 15, Putrajaya Putrajaya '),
('2025686834', '2025686834@staffGFM.my', 1, 'Team Member', 'Rashid Sulaiman', '970404-03-9876', '01123456792', '1990-02-08', 'RashidSulaiman1750268203.jpg', 'No 12, Lorong Damai, Kota Kinabalu Sabah'),
('2025786096', '2025786096@staffGFM.my', 4, 'Team Member', 'Khalid Rosdi', '990303-14-4433', '01123456800', '1987-09-04', 'KhalidRosdi1750268742.jpg', 'Lot 234, Kg Baru, Bachok Kelantan');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `taskID` int(11) NOT NULL,
  `taskName` varchar(255) NOT NULL,
  `location1` varchar(100) NOT NULL,
  `taskDesc` text,
  `teamID` int(11) NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `dueDate` date DEFAULT NULL,
  `status` enum('Not Started','In Progress','Completed') NOT NULL DEFAULT 'Not Started'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`taskID`, `taskName`, `location1`, `taskDesc`, `teamID`, `createdAt`, `updatedAt`, `dueDate`, `status`) VALUES
(1, 'Toilet Plumbing Repair', 'Blok Beta 2', 'Fix leaking pipes and faulty flush systems in the male and female toilets.', 2, '2025-06-18 17:47:46', '2025-06-18 18:06:02', '2025-06-22', 'Completed'),
(2, 'Light Bulb Replacement', 'Blok Gamma A', 'Replace all burnt-out ceiling bulbs in corridors and lecture rooms.', 3, '2025-06-18 17:48:17', '0000-00-00 00:00:00', '2025-06-23', 'In Progress'),
(3, 'Fan Maintenance', 'Cafeteria Alpha', 'Service ceiling fans to ensure proper airflow for students during peak hours.', 1, '2025-06-18 17:48:42', '2025-06-18 18:01:43', '2025-06-27', 'Completed'),
(4, 'Roof Leakage Inspection', 'Blok Alpha 2', 'Check roof and ceiling panels for signs of leakage after recent rainfall.', 1, '2025-06-18 17:49:10', '2025-06-18 18:01:45', '2025-06-28', 'Completed'),
(5, 'Door Lock Replacement', 'GreenHouse', 'Replace faulty door lock for Head of Departmentâ€™s office.', 4, '2025-06-18 17:49:36', '0000-00-00 00:00:00', '2025-06-25', 'Not Started'),
(6, 'Paint Touch-Up', 'Blok Beta 1', 'Repaint chipped wall surfaces in lecture halls on the first floor.', 2, '2025-06-18 17:50:09', '2025-06-18 18:06:05', '2025-06-27', 'Completed'),
(7, 'Air Conditioner Installation', 'Zeta Auditorium', 'Install new air conditioner unit in staff discussion room.', 5, '2025-06-18 17:51:05', '0000-00-00 00:00:00', '2025-07-16', 'In Progress'),
(8, 'Lift Maintenance', 'PTAR', 'Routine servicing and lubrication for student lift system.', 4, '2025-06-18 17:51:37', '2025-06-18 18:15:07', '2025-07-02', 'Completed'),
(9, 'Gardening', 'Blok Alpha Surroundings', 'Trim bushes, mow lawn, and clean walking paths.', 1, '2025-06-18 17:52:14', '0000-00-00 00:00:00', '2025-07-05', 'In Progress'),
(10, 'Wall Crack Repair', 'Blok Beta 3', 'Patch and repaint minor cracks reported in lecture room walls', 2, '2025-06-18 17:52:34', '0000-00-00 00:00:00', '2025-07-10', 'In Progress'),
(11, 'Glass Window Repair', 'BK 1 Zeta', 'Repair high windows facing the main entrance and common areas.', 5, '2025-06-18 17:53:28', '0000-00-00 00:00:00', '2025-07-08', 'In Progress'),
(12, 'Sink Unclogging', 'Cafeteria Wash Area', 'Clear clogged sinks and check plumbing under the counters', 4, '2025-06-18 17:55:05', '2025-06-18 18:14:42', '2025-07-04', 'Completed'),
(13, 'Wi-Fi Router Upgrade', 'Blok Alpha 3', 'Replace old routers with new high-speed units.', 1, '2025-06-18 17:55:33', '0000-00-00 00:00:00', '2025-07-09', 'Not Started'),
(14, 'Projector Maintenance', 'Multimedia Room', 'Clean projector lenses and test HDMI/USB connections', 4, '2025-06-18 17:56:06', '0000-00-00 00:00:00', '2025-07-14', 'In Progress'),
(15, 'Tile Repair', 'Pusat Islam', 'Fix cracked floor tiles to prevent accidents.', 3, '2025-06-18 18:16:42', '0000-00-00 00:00:00', '2025-07-10', 'Not Started'),
(16, 'Classroom Speaker Wiring', 'DK 150A', 'Fix loose speaker wires and test sound output.', 5, '2025-06-18 18:17:12', '0000-00-00 00:00:00', '2025-07-02', 'Not Started'),
(17, 'Sink Unclogging', 'Blok Beta 9, Level 3', 'Clear clogged sinks and check plumbing under the counters.', 2, '2025-06-18 18:18:49', '0000-00-00 00:00:00', '2025-07-01', 'Not Started'),
(19, 'Light Bulb Replacement', 'Blok Gamma B', 'Replace all burnt-out ceiling bulbs in corridors and lecture rooms.', 1, '2025-06-19 01:29:58', '0000-00-00 00:00:00', '2025-06-27', 'Not Started'),
(20, 'makan', 'cafe alpja', 'makan ayam', 3, '2025-06-20 08:32:03', '0000-00-00 00:00:00', '2025-06-26', 'Not Started');

-- --------------------------------------------------------

--
-- Table structure for table `task_proofs`
--

CREATE TABLE `task_proofs` (
  `proofID` int(11) NOT NULL,
  `taskID` int(11) NOT NULL,
  `fileName` varchar(255) NOT NULL,
  `uploadedBy` varchar(50) NOT NULL,
  `uploadedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `task_proofs`
--

INSERT INTO `task_proofs` (`proofID`, `taskID`, `fileName`, `uploadedBy`, `uploadedAt`) VALUES
(1, 4, 'proof_4_1750269691.png', '2025368917', '2025-06-18 18:01:31'),
(2, 3, 'proof_3_1750269699.png', '2025368917', '2025-06-18 18:01:39'),
(4, 1, 'proof_1_1750269952.png', '2025194894', '2025-06-18 18:05:52'),
(5, 6, 'proof_6_1750269959.png', '2025194894', '2025-06-18 18:05:59'),
(6, 12, 'proof_12_1750270479.png', '2025219860', '2025-06-18 18:14:39'),
(7, 8, 'proof_8_1750270515.png', '2025219860', '2025-06-18 18:15:15');

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `teamID` int(11) NOT NULL,
  `teamName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `team`
--

INSERT INTO `team` (`teamID`, `teamName`) VALUES
(1, 'Team A'),
(2, 'Team B'),
(3, 'Team C'),
(4, 'Team D'),
(5, 'Team E');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `category` enum('employee','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `password`, `category`) VALUES
('2025118319', '$2y$10$dXyCnpdr0n67XJajrG536uB/2hMz8SvI2JBGNKZnPzVWh9KWVmpd.', 'employee'),
('2025137655', '$2y$10$D05XfExFGU.fdKF9NkWXM.7U70Ef/EPAiap9Tp.S.DFV6Ozsw0eeK', 'employee'),
('2025154794', '$2y$10$Wy3D7tBzyzOrKlpFA0X91ekzGqPMfdNiDFx3k9hUMM1c7oOurNIcO', 'employee'),
('2025194894', '$2y$10$TSNrH5P49UTuJ.BWMbTN2e8LXCz8SO5zge7jP68Y71Bb7ub/DSh6W', 'employee'),
('2025219860', '$2y$10$3wumM5opawFD0ZDdjTlJUe/ER3JG5WbY3K48HQqjCBwICLAzLSsju', 'employee'),
('2025285147', '$2y$10$BKiQF/pEJDlwVzSpJ2D26O2Vy/wUPd7ctnbuauxoW.DyjqTY0hrTi', 'employee'),
('2025368917', '$2y$10$mt8eNSpKWWYrR6eMPlY6fuonyG5jbKYDin2WbrtVkGJmIONgwFfGu', 'employee'),
('2025402673', '$2y$10$xFqrHjXNJnjp5OpBgSa4iuMQUeN5LdbpybSKJRl20IS14TXzoqO3u', 'employee'),
('2025419674', '$2y$10$fPLwACHvMLT6R6gY16v9o./8VMk/xalysunZkrwNovk11XEFHOb.a', 'employee'),
('2025451342', '$2y$10$Lps89ov3eFl8JIECRsTtTedarbtihuflHaxplDIR9wubZBBml0wM2', 'employee'),
('2025526544', '$2y$10$pHUEkh4jzuVXuWk4jX4WiuY2uRX5sH.iZjxRcfvGXBMm8BRIBDT5C', 'employee'),
('2025637286', '$2y$10$5UEaUdluC9rr5.DVKhdc1eNKELEuWWvVllpyzrR.UDt.pIHx2x/kG', 'employee'),
('2025647668', '$2y$10$pzPrPUaiS4X6nu4ukoVN.O1QkDgtErf2.Te/8XH8/0TxO6YyW3zU2', 'employee'),
('2025666757', '$2y$10$8Qx2Nw80y22BeAA4B4RjiuvEBsAyI2E.UIt52fTkQe/eu5jNdy2aq', 'employee'),
('2025686834', '$2y$10$EwNQzOVClwh.JZPi6jZL2eSWp0dPInw53Xx43AKAb0Q2CUQ3gm4yO', 'employee'),
('2025786096', '$2y$10$Gj8zHKrmmMdGk92nvfrv9OmwwgMMqk.UaUTUvtM3qZeJ60PCbHIJa', 'employee'),
('admin01', '$2y$10$X4uJrwPNZOKMSxCPvmu02ega66k/FUTrfOSMvTNcvNXDgMUBDeNxi', 'admin'),
('admin02', '$2y$10$UsgKqGTVytRBZ/oRHl2UC.vaPXCuvFOQQlKFxfJmTDiTODoIS9yDO', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employeeID`),
  ADD KEY `teamID` (`teamID`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`taskID`),
  ADD KEY `teamID` (`teamID`);

--
-- Indexes for table `task_proofs`
--
ALTER TABLE `task_proofs`
  ADD PRIMARY KEY (`proofID`),
  ADD KEY `idx_taskID` (`taskID`);

--
-- Indexes for table `team`
--
ALTER TABLE `team`
  ADD PRIMARY KEY (`teamID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `taskID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `task_proofs`
--
ALTER TABLE `task_proofs`
  MODIFY `proofID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `team`
--
ALTER TABLE `team`
  MODIFY `teamID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`teamID`) REFERENCES `team` (`teamID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`teamID`) REFERENCES `team` (`teamID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `task_proofs`
--
ALTER TABLE `task_proofs`
  ADD CONSTRAINT `fk_proofs_task` FOREIGN KEY (`taskID`) REFERENCES `tasks` (`taskID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
