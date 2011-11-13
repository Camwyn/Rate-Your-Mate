-- phpMyAdmin SQL Dump
-- version 3.4.3.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 10, 2011 at 12:51 PM
-- Server version: 5.1.52
-- PHP Version: 5.3.2

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `wp4`
--

-- --------------------------------------------------------

--
-- Table structure for table `ActiveUsers`
--

DROP TABLE IF EXISTS `ActiveUsers`;
CREATE TABLE IF NOT EXISTS `ActiveUsers` (
  `username` varchar(30) NOT NULL,
  `UID` varchar(36) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`username`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Behaviors`
--

DROP TABLE IF EXISTS `Behaviors`;
CREATE TABLE IF NOT EXISTS `Behaviors` (
  `BID` varchar(36) NOT NULL,
  `CID` varchar(36) NOT NULL,
  `notes` text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `title` varchar(45) DEFAULT NULL,
  `changedby` varchar(36) NOT NULL,
  PRIMARY KEY (`BID`),
  KEY `fk_B_Users_UID` (`changedby`),
  KEY `fk_B_Contracts_CID` (`CID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Behaviors`
--

INSERT INTO `Behaviors` (`BID`, `CID`, `notes`, `timestamp`, `title`, `changedby`) VALUES
('006ee888-0a88-11e1-afe7-000c29964cd2', 'a36ab400-0ba9-11e1-afe7-000c29964cd2', 'Do you show up to class, group meetings, and upload documents/files by deadlines? Can you explain your decisions/actions?', '2011-11-10 17:48:58', 'Preparedness/Accountability', 'a692064b3294c09624d055a92ca0c038'),
('ed2cd67c-0a87-11e1-afe7-000c29964cd2', 'a36ab400-0ba9-11e1-afe7-000c29964cd2', 'Do you treat other team members with respect, respond when needed, ask questions when confused, and keep team members up to date on your progress (or lack thereof)?', '2011-11-10 17:48:58', 'Responsiveness/Communication', 'a692064b3294c09624d055a92ca0c038'),
('ed2d0b74-0a87-11e1-afe7-000c29964cd2', 'a36ab400-0ba9-11e1-afe7-000c29964cd2', 'Have you set personal goals and are you making progress toward completing them? Do you try to help other team members? Are you committed to our overall project goals and team progress?', '2011-11-10 17:48:58', 'Productivity/Active Involvement', 'a692064b3294c09624d055a92ca0c038');

-- --------------------------------------------------------

--
-- Table structure for table `Classes`
--

DROP TABLE IF EXISTS `Classes`;
CREATE TABLE IF NOT EXISTS `Classes` (
  `CLID` varchar(36) NOT NULL,
  `cname` varchar(45) NOT NULL,
  `instructor` varchar(36) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`CLID`),
  KEY `fk_Cl_Users_instructor` (`instructor`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Classes`
--

INSERT INTO `Classes` (`CLID`, `cname`, `instructor`, `timestamp`) VALUES
('79d44de0-f371-11e0-863b-003048965058', 'test class 1', '2cb5a7f1cd204e8499cc67c3e44d8194', '0000-00-00 00:00:00'),
('79d451e6-f371-11e0-863b-003048965058', 'test class 2', '2cb5a7f1cd204e8499cc67c3e44d8194', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `Contracts`
--

DROP TABLE IF EXISTS `Contracts`;
CREATE TABLE IF NOT EXISTS `Contracts` (
  `CID` varchar(36) NOT NULL,
  `GID` varchar(36) NOT NULL,
  `goals` text,
  `comments` text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `changedby` varchar(36) NOT NULL,
  PRIMARY KEY (`CID`),
  KEY `fk_Con_Users_changedby` (`changedby`),
  KEY `fk_Con_Groups_GID` (`GID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Contracts`
--

INSERT INTO `Contracts` (`CID`, `GID`, `goals`, `comments`, `timestamp`, `changedby`) VALUES
('a36ab400-0ba9-11e1-afe7-000c29964cd2', '5DF2E500-6AE6-4C2E-BA84-769A6FB039E7', 'To make the best damn RYM application possible in the time allotted.', 'Need more coffee...', '2011-11-10 15:09:42', 'a692064b3294c09624d055a92ca0c038');

-- --------------------------------------------------------

--
-- Table structure for table `Contract_Flags`
--

DROP TABLE IF EXISTS `Contract_Flags`;
CREATE TABLE IF NOT EXISTS `Contract_Flags` (
  `CID` varchar(36) NOT NULL,
  `UID` varchar(36) NOT NULL,
  `Flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'False is saved but not finalized. True is finalized for both instructor and/or student.',
  PRIMARY KEY (`CID`,`UID`),
  KEY `fk_CF_Users_UID` (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Enrollment`
--

DROP TABLE IF EXISTS `Enrollment`;
CREATE TABLE IF NOT EXISTS `Enrollment` (
  `class` varchar(36) NOT NULL,
  `user` varchar(36) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `PRIME` (`class`,`user`),
  KEY `fk_E_Users_UID` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Enrollment`
--

INSERT INTO `Enrollment` (`class`, `user`, `timestamp`) VALUES
('79d44de0-f371-11e0-863b-003048965058', '7df4c42922ae87d628eaae544b0d9b48', '0000-00-00 00:00:00'),
('79d44de0-f371-11e0-863b-003048965058', 'a692064b3294c09624d055a92ca0c038', '0000-00-00 00:00:00'),
('79d44de0-f371-11e0-863b-003048965058', 'aa6e4e22-f2e2-11e0-863b-003048965058', '0000-00-00 00:00:00'),
('79d44de0-f371-11e0-863b-003048965058', 'b8485cea-f2e2-11e0-863b-003048965058', '0000-00-00 00:00:00'),
('79d44de0-f371-11e0-863b-003048965058', 'b848601e-f2e2-11e0-863b-003048965058', '0000-00-00 00:00:00'),
('79d44de0-f371-11e0-863b-003048965058', 'ef1d7288-f37c-11e0-863b-003048965058', '0000-00-00 00:00:00'),
('79d451e6-f371-11e0-863b-003048965058', '7df4c42922ae87d628eaae544b0d9b48', '0000-00-00 00:00:00'),
('79d451e6-f371-11e0-863b-003048965058', 'a692064b3294c09624d055a92ca0c038', '0000-00-00 00:00:00'),
('79d451e6-f371-11e0-863b-003048965058', 'ef1d7288-f37c-11e0-863b-003048965058', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `Evals`
--

DROP TABLE IF EXISTS `Evals`;
CREATE TABLE IF NOT EXISTS `Evals` (
  `EID` varchar(36) NOT NULL,
  `PID` varchar(36) NOT NULL,
  `odate` datetime NOT NULL,
  `cdate` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EID`),
  KEY `fk_E_Projects_PID` (`PID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Evals`
--

INSERT INTO `Evals` (`EID`, `PID`, `odate`, `cdate`, `timestamp`) VALUES
('76DFB505-7D4E-4C1F-A9BE-6649E9CC8DDC', '756D1205-A1F8-4AFC-9239-BB9DF748A88F', '2011-11-06 00:00:00', '2011-11-12 00:00:00', '2011-11-09 04:01:00'),
('B8E70F16-1DDE-4149-8E9C-A88EF01CC41C', '756D1205-A1F8-4AFC-9239-BB9DF748A88F', '2011-11-13 00:00:00', '2011-11-19 00:00:00', '2011-11-09 04:01:00');

-- --------------------------------------------------------

--
-- Table structure for table `Grades`
--

DROP TABLE IF EXISTS `Grades`;
CREATE TABLE IF NOT EXISTS `Grades` (
  `UID` varchar(36) NOT NULL,
  `EID` varchar(36) NOT NULL,
  `role` enum('subject','judge') NOT NULL,
  `grade` double NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `PRIME` (`UID`,`EID`,`role`),
  KEY `fk_G_Users_UID` (`UID`),
  KEY `fk_G_Evals_EID` (`EID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Groups`
--

DROP TABLE IF EXISTS `Groups`;
CREATE TABLE IF NOT EXISTS `Groups` (
  `GID` varchar(36) NOT NULL,
  `UID` varchar(36) NOT NULL,
  `PID` varchar(36) NOT NULL,
  `goals` text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `PRIME` (`GID`,`UID`,`PID`),
  KEY `fk_GP_Users_UID` (`UID`),
  KEY `fk_GP_Projects_PID` (`PID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Groups`
--

INSERT INTO `Groups` (`GID`, `UID`, `PID`, `goals`, `timestamp`) VALUES
('5DF2E500-6AE6-4C2E-BA84-769A6FB039E7', 'ef1d7288-f37c-11e0-863b-003048965058', '756D1205-A1F8-4AFC-9239-BB9DF748A88F', '', '2011-11-09 03:53:44'),
('5DF2E500-6AE6-4C2E-BA84-769A6FB039E7', 'aa6e4e22-f2e2-11e0-863b-003048965058', '756D1205-A1F8-4AFC-9239-BB9DF748A88F', '', '2011-11-09 03:53:44'),
('5DF2E500-6AE6-4C2E-BA84-769A6FB039E7', 'a692064b3294c09624d055a92ca0c038', '756D1205-A1F8-4AFC-9239-BB9DF748A88F', '', '2011-11-09 03:53:44'),
('5DF2E500-6AE6-4C2E-BA84-769A6FB039E7', 'b8485cea-f2e2-11e0-863b-003048965058', '756D1205-A1F8-4AFC-9239-BB9DF748A88F', '', '2011-11-09 03:53:44'),
('5DF2E500-6AE6-4C2E-BA84-769A6FB039E7', 'b848601e-f2e2-11e0-863b-003048965058', '756D1205-A1F8-4AFC-9239-BB9DF748A88F', '', '2011-11-09 03:53:44'),
('5DF2E500-6AE6-4C2E-BA84-769A6FB039E7', '7df4c42922ae87d628eaae544b0d9b48', '756D1205-A1F8-4AFC-9239-BB9DF748A88F', '', '2011-11-09 03:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `Overrides`
--

DROP TABLE IF EXISTS `Overrides`;
CREATE TABLE IF NOT EXISTS `Overrides` (
  `UID` varchar(36) NOT NULL,
  `EID` varchar(36) NOT NULL,
  `odate` datetime NOT NULL,
  `cdate` datetime NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `PRIME` (`UID`,`EID`),
  KEY `fk_O_Users_UID` (`UID`),
  KEY `fk_O_Evals_EID` (`EID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Projects`
--

DROP TABLE IF EXISTS `Projects`;
CREATE TABLE IF NOT EXISTS `Projects` (
  `PID` varchar(36) NOT NULL,
  `pname` varchar(45) NOT NULL,
  `odate` datetime NOT NULL,
  `cdate` datetime NOT NULL,
  `instructor` varchar(36) NOT NULL,
  `late` tinyint(1) NOT NULL DEFAULT '1',
  `groups` int(11) DEFAULT NULL,
  `evals` int(11) DEFAULT NULL,
  `maxpoints` tinyint(4) DEFAULT NULL,
  `contract` enum('student','instructor') NOT NULL DEFAULT 'student',
  `grades` enum('subject','judge','both','none') NOT NULL DEFAULT 'both',
  `class` varchar(36) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`PID`),
  KEY `fk_P_Users_UID` (`instructor`),
  KEY `fk_P_Classes_CLID` (`class`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Projects`
--

INSERT INTO `Projects` (`PID`, `pname`, `odate`, `cdate`, `instructor`, `late`, `groups`, `evals`, `maxpoints`, `contract`, `grades`, `class`, `timestamp`) VALUES
('756D1205-A1F8-4AFC-9239-BB9DF748A88F', 'ajax test project', '0000-00-00 00:00:00', '0000-00-00 00:00:00', '2cb5a7f1cd204e8499cc67c3e44d8194', 0, 2, 2, 0, 'student', 'both', '79d44de0-f371-11e0-863b-003048965058', '2011-11-09 03:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `Reviews`
--

DROP TABLE IF EXISTS `Reviews`;
CREATE TABLE IF NOT EXISTS `Reviews` (
  `RID` varchar(36) NOT NULL,
  `EID` varchar(36) NOT NULL,
  `subject` varchar(36) NOT NULL,
  `judge` varchar(36) NOT NULL,
  `BID` varchar(36) NOT NULL,
  `scomm` text,
  `icomm` text,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EID`,`BID`,`subject`,`judge`),
  KEY `fk_R_Users_subject` (`subject`),
  KEY `fk_R_Users_judge` (`judge`),
  KEY `fk_R_Behaviors_BID` (`BID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Review_Flags`
--

DROP TABLE IF EXISTS `Review_Flags`;
CREATE TABLE IF NOT EXISTS `Review_Flags` (
  `RID` varchar(36) NOT NULL,
  `UID` varchar(36) NOT NULL,
  `Flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'False is saved but not finalized. True is finalized for both instructor and/or student.',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`RID`,`UID`),
  KEY `fk_RF_Users_UID` (`UID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Review_Flags`
--

INSERT INTO `Review_Flags` (`RID`, `UID`, `Flag`, `timestamp`) VALUES
('76DFB505-7D4E-4C1F-A9BE-6649E9CC8DDC', 'ef1d7288-f37c-11e0-863b-003048965058', 1, '2011-11-10 15:26:58');

-- --------------------------------------------------------

--
-- Table structure for table `Scores`
--

DROP TABLE IF EXISTS `Scores`;
CREATE TABLE IF NOT EXISTS `Scores` (
  `EID` varchar(36) NOT NULL,
  `judge` varchar(36) NOT NULL,
  `subject` varchar(36) NOT NULL,
  `score` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`EID`,`judge`,`subject`),
  KEY `fk_S_Users_subject` (`subject`),
  KEY `fk_S_Users_judge` (`judge`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
CREATE TABLE IF NOT EXISTS `Users` (
  `UID` varchar(36) NOT NULL,
  `SESID` varchar(36) NOT NULL,
  `fname` varchar(45) DEFAULT NULL,
  `lname` varchar(45) DEFAULT NULL,
  `username` varchar(32) NOT NULL,
  `ulevel` tinyint(4) NOT NULL,
  `email` varchar(45) NOT NULL,
  `password` varchar(128) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_log` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`UID`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`UID`, `SESID`, `fname`, `lname`, `username`, `ulevel`, `email`, `password`, `timestamp`, `last_log`) VALUES
('2cb5a7f1cd204e8499cc67c3e44d8194', '3fd9ab3e7f0f05c37dc6667640671cdd', 'Christian', 'Roberson', 'caroberson', 9, 'caroberson@plymouth.edu', '1e3116afaedc2769027364f7d32b3b917ef2e58e65887336b7bc564d0367e2099d9c59385b1a22b4b02af21d2029a109506c298f21ad9c9c44cee133a93dcd14', '2011-11-09 16:45:03', '2011-11-09 16:27:45'),
('6033e24c-f2ef-11e0-863b-003048965058', '', 'Zach', 'Tirrell', 'zbtirell', 5, 'zbtirell@gmail.com', '', '2011-10-30 14:39:11', '0000-00-00 00:00:00'),
('7df4c42922ae87d628eaae544b0d9b48', '57d219a8d6f14dcfb460a7bedb1f0087', 'Jason', 'Schackai', 'jschackai', 1, 'jschackai@gmail.com', '3d08754f97e5ce582ee2596c864f8ff33f0c785665b21a4e56e008a3e07091d368dc4ac24a83e28c609552d37edfd64c8080cddcbeda76a654cd83f6ec137cb3', '2011-11-08 18:45:53', '2011-11-07 16:51:45'),
('a692064b3294c09624d055a92ca0c038', '229e26d47e1bfd418364cc45d92c6fb3', 'Stephen', 'Page', 'sjpage', 1, 'stephenjpage@gmail.com', '1e3116afaedc2769027364f7d32b3b917ef2e58e65887336b7bc564d0367e2099d9c59385b1a22b4b02af21d2029a109506c298f21ad9c9c44cee133a93dcd14', '2011-11-10 09:25:19', '2011-11-10 08:53:41'),
('aa6e4e22-f2e2-11e0-863b-003048965058', '7c570feefb3378729f6b2f86a3e9beae', 'Jonathan', 'Linden', 'jon8linden', 1, 'jon8linden@gmail.com', '71169c14c6e5febad09dac3f8db1ace7ba59a48ed85a03b67c6409905c6eb8328fe5dc3152af83a954bda1e25fc8e4664f758ec4666aeaa0ddbb5c4f9768c6f8', '2011-11-09 16:42:47', '2011-11-09 16:40:41'),
('b8485cea-f2e2-11e0-863b-003048965058', 'b73dcdc9ca40884773931dba22bb1734', 'James', 'Prehemo', 'prehemoj', 1, 'prehemoj@gmail.com', '8f697b68a7cfa679634077f589d1ba910f82899711029081649bb4263785b9fdf459272b9c9cbf6b059c7353e375873082738f04b1e3ef17043fc3a34c9364a8', '2011-11-07 16:16:46', '2011-10-30 14:39:11'),
('b848601e-f2e2-11e0-863b-003048965058', '', 'Nathan', 'Urbanowski', 'nurban512', 1, 'nurban512@comcast.net', '59cd89c3e3fe2def650a9c13ce95c2d63044530b7acbffbdc51d84bf9b08ea3d76e315463ab3c0e2c09fbddf8867f5b6d0f4d99f5443a2f6a226465c06ac5f66', '2011-11-02 13:27:20', '0000-00-00 00:00:00'),
('ef1d7288-f37c-11e0-863b-003048965058', '3383b4db00d8d7b3f0e6da5b795b08f5', 'Richard', 'Frederick', 'rickalis777', 1, 'rickalis777@gmail.com', '6524fef8a28832ea10a8c5cfdec505386fd50472c16c20056b394c821ba50764df1a2a39e10c41fb4152871086e4ea9812c922971e26c07039a65eb167602700', '2011-11-09 15:20:49', '2011-11-09 14:29:56');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ActivUsers`
--
ALTER TABLE `ActiveUsers`
  ADD CONSTRAINT `fk_AU_Users_UID` FOREIGN KEY (`UID`) REFERENCES `Users` (`UID`);


--
-- Constraints for table `Behaviors`
--
ALTER TABLE `Behaviors`
  ADD CONSTRAINT `fk_B_Contracts_CID` FOREIGN KEY (`CID`) REFERENCES `Contracts` (`CID`),
  ADD CONSTRAINT `fk_B_Users_UID` FOREIGN KEY (`changedby`) REFERENCES `Users` (`UID`);

--
-- Constraints for table `Classes`
--
ALTER TABLE `Classes`
  ADD CONSTRAINT `fk_CL_Users_instructor` FOREIGN KEY (`instructor`) REFERENCES `Users` (`UID`);

--
-- Constraints for table `Contracts`
--
ALTER TABLE `Contracts`
  ADD CONSTRAINT `fk_CN_Groups_GID` FOREIGN KEY (`GID`) REFERENCES `Groups` (`GID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_CN_Users_changedby` FOREIGN KEY (`changedby`) REFERENCES `Users` (`UID`);

--
-- Constraints for table `Enrollment`
--
ALTER TABLE `Enrollment`
  ADD CONSTRAINT `fk_EN_Classes_CLID` FOREIGN KEY (`class`) REFERENCES `Classes` (`CLID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_EN_Users_UID` FOREIGN KEY (`user`) REFERENCES `Users` (`UID`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `Evals`
--
ALTER TABLE `Evals`
  ADD CONSTRAINT `fk_EV_Projects_PID` FOREIGN KEY (`PID`) REFERENCES `Projects` (`PID`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `Grades`
--
ALTER TABLE `Grades`
  ADD CONSTRAINT `fk_G_Evals_EID` FOREIGN KEY (`EID`) REFERENCES `Evals` (`EID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_G_Users_UID` FOREIGN KEY (`UID`) REFERENCES `Users` (`UID`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `Groups`
--
ALTER TABLE `Groups`
  ADD CONSTRAINT `fk_GP_Projects_PID` FOREIGN KEY (`PID`) REFERENCES `Projects` (`PID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_GP_Users_UID` FOREIGN KEY (`UID`) REFERENCES `Users` (`UID`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `Overrides`
--
ALTER TABLE `Overrides`
  ADD CONSTRAINT `fk_O_Evals_EID` FOREIGN KEY (`EID`) REFERENCES `Evals` (`EID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_O_Users_UID` FOREIGN KEY (`UID`) REFERENCES `Users` (`UID`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `Projects`
--
ALTER TABLE `Projects`
  ADD CONSTRAINT `fk_P_Classes_CLID` FOREIGN KEY (`class`) REFERENCES `Classes` (`CLID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_P_Users_UID` FOREIGN KEY (`instructor`) REFERENCES `Users` (`UID`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `Scores`
--
ALTER TABLE `Scores`
  ADD CONSTRAINT `fk_S_Users_judge` FOREIGN KEY (`judge`) REFERENCES `Users` (`UID`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_S_Users_subject` FOREIGN KEY (`subject`) REFERENCES `Users` (`UID`) ON DELETE NO ACTION ON UPDATE CASCADE;


--
-- Constraints for table `Reviews`
--
ALTER TABLE `Reviews`
  ADD CONSTRAINT `fk_R_Behaviors_BID` FOREIGN KEY (`BID`) REFERENCES `Behaviors` (`BID`),
  ADD CONSTRAINT `fk_R_Evals_EID` FOREIGN KEY (`EID`) REFERENCES `Evals` (`EID`),
  ADD CONSTRAINT `fk_R_Users_judge` FOREIGN KEY (`judge`) REFERENCES `Users` (`UID`),
  ADD CONSTRAINT `fk_R_Users_subject` FOREIGN KEY (`subject`) REFERENCES `Users` (`UID`);
SET FOREIGN_KEY_CHECKS=1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
