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
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` int(11) NOT NULL,
  `site_name` varchar(255) NOT NULL,
  `doctype` varchar(255) NOT NULL,
  `content_type` varchar(255) NOT NULL,
  `x_ua_compatible` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `reply_to` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `resource_type` varchar(255) NOT NULL,
  `revisit_after` varchar(255) NOT NULL,
  `classification` varchar(255) NOT NULL,
  `distribution` varchar(255) NOT NULL,
  `rating` varchar(255) NOT NULL,
  `copyright` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `description` varchar(2048) NOT NULL,
  `keywords` varchar(2048) NOT NULL,
  `robots` varchar(255) NOT NULL,
  `other_meta` varchar(2048) NOT NULL,
  `cache` tinyint(4) NOT NULL DEFAULT '1',
  `protocol` varchar(255) NOT NULL,
  `base_url` varchar(255) NOT NULL,
  `home_page` varchar(255) NOT NULL,
  `test_var` tinyint(1) NOT NULL,
  `tour_status` tinyint(4) NOT NULL,
  `demo_note` tinyint(4) NOT NULL,
  `pdo_log` varchar(255) NOT NULL,
  `username_obscenity_checking` tinyint(4) NOT NULL,
  `username_minimum_length` int(11) NOT NULL,
  `password_minimum_length` int(11) NOT NULL,
  `auth_table` varchar(255) NOT NULL,
  `fund_table` varchar(255) NOT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Global Site Configuration' AUTO_INCREMENT=2 ;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`uid`, `timestamp`, `site_name`, `doctype`, `content_type`, `x_ua_compatible`, `title`, `reply_to`, `author`, `resource_type`, `revisit_after`, `classification`, `distribution`, `rating`, `copyright`, `language`, `description`, `keywords`, `robots`, `other_meta`, `cache`, `protocol`, `base_url`, `home_page`, `test_var`, `tour_status`, `demo_note`, `pdo_log`, `username_obscenity_checking`, `username_minimum_length`, `password_minimum_length`, `auth_table`, `fund_table`) VALUES
(1, 1394549460, 'Marketocracy', '<!DOCTYPE html>', 'text/html; charset=utf-8', 'IE=edge', 'Marketocracy', 'info@marketocracy.com', 'Marketocracy, Inc.', 'Document', '1 Day', 'Financial', 'Global', 'General', 'Marketocracy, Inc.', 'en-us', 'Marketocracy has a mutual fund, stock quotes, stock picks, hot stocks, an investment newsletter, forums, stock research, trading by top investors, and a portfolio tracker. All of these driven by our ability to find the best investors from our membership. Sign up for a free membership, and test your own investment prowess, or use our site to make more money in the market.', 'stocks, hot stocks, stock picks, investment newsletter, investing, forums, mutual fund, portfolio, investing, research, quotes', 'all', '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>|<meta http-equiv="imagetoolbar" content="false"/>|<meta name="MobileOptimized" content="320"/>', 1, 'http', 'alpha.marketocracy.com/', '01-00-00-001', 0, 0, 0, '/var/log/httpd/alpha-pdo_log', 1, 5, 8, 'system_authentication', 'funds_test');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
