-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 11, 2014 at 09:51 PM
-- Server version: 5.5.36-MariaDB
-- PHP Version: 5.5.9

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `nova`
--

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE IF NOT EXISTS `sections` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `section_id` varchar(255) NOT NULL,
  `timestamp` int(10) NOT NULL,
  `section_name` varchar(255) NOT NULL,
  `section_url` varchar(255) NOT NULL,
  `sequence` varchar(255) NOT NULL,
  `section_level` int(10) NOT NULL,
  `modified_by` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `main_nav` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`section_id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`uid`, `section_id`, `timestamp`, `section_name`, `section_url`, `sequence`, `section_level`, `modified_by`, `icon`, `main_nav`, `active`) VALUES
(1, '01', 1395775763, 'Dashboard', '?page=01-00-00-001', '01-001', 1, '2', 'dashboard', 1, 1),
(2, '02', 1395775763, 'Make A Trade', '', '02-001', 1, '2', 'random', 1, 1),
(3, '03', 1395775763, 'My Funds', '', '02-002', 1, '2', 'money', 1, 1),
(22, '03-01', 1395873115, 'Fund Symbol', '', '00-001', 2, '2', '', 0, 0),
(23, '03-01-01', 1395873115, 'Attribution', '', '00-001', 3, '2', '', 0, 1),
(24, '03-01-02', 1395873115, 'Benchmarks', '', '00-002', 3, '2', '', 0, 1),
(25, '03-01-03', 1395873115, 'Stratification', '', '00-003', 3, '2', '', 0, 1),
(4, '04', 1395775763, 'Community', '', '02-003', 1, '2', 'group', 1, 1),
(5, '05', 1395775763, 'Research', '', '02-004', 1, '2', 'globe', 1, 1),
(12, '05-01', 1395775763, 'Stock Info', '', '00-001', 2, '2', '', 0, 1),
(13, '05-02', 1395775763, 'Weekly Insight', '', '00-001', 2, '2', '', 0, 1),
(6, '06', 1395775763, 'Rankings', '', '02-005', 1, '2', 'bar-chart', 1, 1),
(14, '06-01', 1395775763, 'mRankings', '', '00-001', 2, '2', '', 0, 1),
(15, '06-02', 1395775763, 'Top Rankings', '', '00-002', 2, '2', '', 0, 1),
(16, '06-03', 1395775763, 'Sector Rankings', '', '00-003', 2, '2', '', 0, 1),
(17, '06-04', 1395775763, 'Style Rankings', '', '00-004', 2, '2', '', 0, 1),
(7, '07', 1395775763, 'Trade School', '', '03-001', 1, '2', 'book', 1, 1),
(18, '07-01', 1395775763, 'Investing Insights', '', '00-001', 2, '2', '', 0, 1),
(19, '07-02', 1395775763, 'Tutorials', '', '00-002', 2, '2', '', 0, 1),
(8, '08', 1395775763, 'Support', '', '04-001', 1, '2', 'briefcase', 1, 1),
(20, '08-01', 1395775763, 'Support Tickets', '', '00-001', 2, '2', '', 0, 1),
(9, '09', 1395775763, 'Policies', '', '04-002', 1, '2', 'file-text', 1, 1),
(21, '09-01', 1395775763, 'Rules', '', '00-001', 2, '2', '', 0, 1),
(10, '10', 1395775763, 'User', '', '00-000', 1, '2', '', 0, 1),
(11, '11', 1395775763, 'System', '', '00-000', 1, '2', '', 0, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
