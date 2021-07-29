-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 11, 2014 at 09:50 PM
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
-- Table structure for table `funds_test`
--

CREATE TABLE IF NOT EXISTS `funds_test` (
  `uid` int(10) NOT NULL AUTO_INCREMENT,
  `fund_id` varchar(255) NOT NULL,
  `seq_id` int(10) NOT NULL,
  `member_id` int(10) NOT NULL,
  `timestamp` int(10) NOT NULL,
  `fund_name` varchar(255) NOT NULL,
  `fund_symbol` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `version` int(10) NOT NULL,
  `fund_color` varchar(225) NOT NULL,
  PRIMARY KEY (`fund_id`),
  UNIQUE KEY `uid` (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `funds_test`
--

INSERT INTO `funds_test` (`uid`, `fund_id`, `seq_id`, `member_id`, `timestamp`, `fund_name`, `fund_symbol`, `active`, `version`, `fund_color`) VALUES
(4, '1-1', 1, 1, 1396038131, 'Jeff''s Mutual Fund', 'JMF', 1, 1, ''),
(6, '1-2', 1, 1, 1396038131, 'Jeff''s Short Fund', 'JSF', 1, 1, ''),
(1, '2-1', 1, 2, 1395873115, 'Brandon''s Mutual Fund', 'BMF', 1, 1, ''),
(2, '2-2', 2, 2, 1395880091, 'Aggressive Mutual Fund', 'AMF', 1, 1, ''),
(3, '2-3', 3, 2, 1395940440, 'Brandon''s Short Fund', 'BSF', 1, 1, ''),
(5, '2-4', 4, 2, 1396038131, 'Brandon''s Value Fund', 'BVF', 1, 1, '');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
