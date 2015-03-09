-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Oct 17, 2014 at 01:24 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `jeera`
--

-- --------------------------------------------------------

--
-- Table structure for table `y2m_admin`
--

CREATE TABLE IF NOT EXISTS `y2m_admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_firstname` varchar(100) NOT NULL,
  `admin_lastname` varchar(100) NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `admin_email` varchar(50) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `admin_about` text NOT NULL,
  `admin_phone` varchar(25) NOT NULL,
  `admin_status` enum('Active','Deactive') NOT NULL,
  `admin_added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `admin_added_ip` varchar(25) NOT NULL,
  `admin_modified_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `admin_mdified_ip` varchar(25) NOT NULL,
  `admin_picture` varchar(255) NOT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `y2m_admin`
--

INSERT INTO `y2m_admin` (`admin_id`, `admin_firstname`, `admin_lastname`, `admin_username`, `admin_email`, `admin_password`, `admin_about`, `admin_phone`, `admin_status`, `admin_added_date`, `admin_added_ip`, `admin_modified_date`, `admin_mdified_ip`, `admin_picture`) VALUES
(1, 'Super Admin', '', 'admin', 'admin@jeera.com', '$2y$14$F0ZMyXt3jedpLotuBMFyK.eakA9JkeDCxqAantv78qQ71uH4yItyG', '', '', 'Active', '2014-10-06 12:43:31', '', '2014-10-05 22:43:31', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `y2m_city`
--

CREATE TABLE IF NOT EXISTS `y2m_city` (
  `city_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`city_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=28 ;

--
-- Dumping data for table `y2m_city`
--

INSERT INTO `y2m_city` (`city_id`, `country_id`, `name`, `status`) VALUES
(1, 2, 'Abu Dhabi', 1),
(2, 2, 'Ajman', 1),
(3, 2, 'Dubai', 1),
(4, 2, 'Fujairah', 1),
(5, 2, 'Ras al-Khaimah', 1),
(6, 2, 'Sharjah', 1),
(8, 2, 'Umm al-Quwain', 1),
(9, 3, 'Ad Dakhiliyah', 1),
(10, 3, 'Ad Dhahirah North', 1),
(11, 3, 'Al Batinah North', 1),
(12, 3, 'Al Batinah South', 1),
(13, 3, 'Al Buraimi', 1),
(14, 3, 'Al Wusta', 1),
(15, 3, 'Ash Sharqiyah North', 1),
(16, 3, 'Ash Sharqiyah South', 1),
(17, 3, 'Dhofar', 1),
(18, 3, 'Mascat', 1),
(19, 3, 'Musandam', 1),
(20, 4, 'Kerala', 1),
(21, 4, 'Tamilnadu', 1),
(22, 4, 'Karnataka', 1),
(27, 4, 'Assam', 1);

-- --------------------------------------------------------

--
-- Table structure for table `y2m_country`
--

CREATE TABLE IF NOT EXISTS `y2m_country` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_title` varchar(50) DEFAULT NULL,
  `country_code` char(8) DEFAULT NULL,
  `country_code_googlemap` varchar(5) NOT NULL,
  `country_added_ip_address` int(7) unsigned DEFAULT NULL,
  `country_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `country_status` tinyint(1) NOT NULL DEFAULT '0',
  `country_modified_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `country_modified_ip_address` int(7) unsigned DEFAULT NULL,
  PRIMARY KEY (`country_id`),
  UNIQUE KEY `country-seo-title` (`country_code`),
  UNIQUE KEY `country_title` (`country_title`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `y2m_country`
--

INSERT INTO `y2m_country` (`country_id`, `country_title`, `country_code`, `country_code_googlemap`, `country_added_ip_address`, `country_added_timestamp`, `country_status`, `country_modified_timestamp`, `country_modified_ip_address`) VALUES
(2, 'United Arab Emirates', 'UAE', 'ae', NULL, '2013-08-05 05:12:29', 1, '2013-08-05 05:12:29', NULL),
(3, 'Oman', 'OM', 'om', NULL, '2014-02-16 07:13:37', 1, '0000-00-00 00:00:00', NULL),
(4, 'India', 'IN', 'IN', NULL, '2014-09-15 08:27:04', 1, '2014-09-15 08:27:04', NULL),
(5, 'Bahrain', 'BH', 'BH', NULL, '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `y2m_group`
--

CREATE TABLE IF NOT EXISTS `y2m_group` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_title` varchar(100) DEFAULT NULL,
  `group_seo_title` varchar(200) DEFAULT NULL,
  `group_status` tinyint(1) DEFAULT '0',
  `group_description` text,
  `group_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `group_added_ip_address` int(7) unsigned DEFAULT NULL,
  `group_parent_group_id` int(11) DEFAULT '0',
  `group_location` varchar(200) DEFAULT NULL,
  `group_city_id` int(11) NOT NULL,
  `group_country_id` int(11) NOT NULL,
  `group_location_lat` float(30,25) NOT NULL,
  `group_location_lng` float(30,25) NOT NULL,
  `group_web_address` varchar(100) DEFAULT NULL,
  `group_welcome_message_members` text NOT NULL,
  `group_photo_id` int(11) DEFAULT NULL,
  `group_modified_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `group_modified_ip_address` int(7) unsigned DEFAULT NULL,
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_title` (`group_title`),
  UNIQUE KEY `group_seo_title` (`group_seo_title`),
  KEY `parent_group_id` (`group_parent_group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

--
-- Dumping data for table `y2m_group`
--

INSERT INTO `y2m_group` (`group_id`, `group_title`, `group_seo_title`, `group_status`, `group_description`, `group_added_timestamp`, `group_added_ip_address`, `group_parent_group_id`, `group_location`, `group_city_id`, `group_country_id`, `group_location_lat`, `group_location_lng`, `group_web_address`, `group_welcome_message_members`, `group_photo_id`, `group_modified_timestamp`, `group_modified_ip_address`) VALUES
(91, 'NY Lifestyle', 'NY_Lifestyle', 1, 'NY Lifestyle', '2014-10-07 13:59:49', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 1, '0000-00-00 00:00:00', NULL),
(92, 'Europe Tour', 'Europe_Tour', 1, 'Europe Tour', '2014-10-08 08:58:34', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 2, '0000-00-00 00:00:00', NULL),
(93, 'Scuba Diving', 'Scuba_Diving', 1, 'Scuba Diving', '2014-10-08 09:03:53', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 3, '0000-00-00 00:00:00', NULL),
(94, 'NY Lifestyle1', 'NY_Lifestyle1', 1, 'NY Lifestyle', '2014-10-07 09:59:49', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 1, '0000-00-00 00:00:00', NULL),
(95, 'Europe Tour1', 'Europe_Tour1', 1, 'Europe Tour', '2014-10-08 04:58:34', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 2, '0000-00-00 00:00:00', NULL),
(96, 'Scuba Diving1', 'Scuba_Diving1', 1, 'Scuba Diving', '2014-10-08 05:03:53', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 3, '0000-00-00 00:00:00', NULL),
(97, 'NY Lifestyle2', 'NY_Lifestyle2', 1, 'NY Lifestyle', '2014-10-07 09:59:49', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 1, '0000-00-00 00:00:00', NULL),
(98, 'Europe Tour2', 'Europe_Tour2', 1, 'Europe Tour', '2014-10-08 04:58:34', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 2, '0000-00-00 00:00:00', NULL),
(99, 'Scuba Diving2', 'Scuba_Diving2', 1, 'Scuba Diving', '2014-10-08 05:03:53', NULL, 0, 'Media city', 3, 2, 0.0000000000000000000000000, 0.0000000000000000000000000, NULL, '', 3, '0000-00-00 00:00:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `y2m_group_photo`
--

CREATE TABLE IF NOT EXISTS `y2m_group_photo` (
  `group_photo_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_photo_group_id` int(11) NOT NULL,
  `group_photo_photo` varchar(255) NOT NULL,
  PRIMARY KEY (`group_photo_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `y2m_group_photo`
--

INSERT INTO `y2m_group_photo` (`group_photo_id`, `group_photo_group_id`, `group_photo_photo`) VALUES
(1, 91, 'group-img_1.png'),
(2, 92, 'group-img_3.png'),
(3, 93, 'group-img_2.png'),
(4, 94, 'group-img_1.png'),
(5, 95, 'group-img_3.png'),
(6, 96, 'group-img_2.png'),
(7, 97, 'group-img_1.png'),
(8, 98, 'group-img_3.png'),
(9, 99, 'group-img_2.png');

-- --------------------------------------------------------

--
-- Table structure for table `y2m_group_tag`
--

CREATE TABLE IF NOT EXISTS `y2m_group_tag` (
  `group_tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_tag_group_id` int(11) DEFAULT NULL,
  `group_tag_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `group_tag_added_ip_address` int(7) unsigned DEFAULT NULL,
  `group_tag_tag_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`group_tag_id`),
  KEY `group_tag_group_id` (`group_tag_group_id`),
  KEY `group_tag_tag_id` (`group_tag_tag_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `y2m_group_tag`
--

INSERT INTO `y2m_group_tag` (`group_tag_id`, `group_tag_group_id`, `group_tag_added_timestamp`, `group_tag_added_ip_address`, `group_tag_tag_id`) VALUES
(1, 91, '2014-10-08 08:07:33', NULL, 2),
(2, 91, '2014-10-08 08:07:33', NULL, 3),
(3, 92, '2014-10-08 08:59:45', 0, 1),
(5, 93, '2014-10-08 09:05:38', NULL, 2),
(6, 93, '2014-10-08 09:05:38', NULL, 3),
(8, 94, '2014-10-08 04:07:33', NULL, 3),
(9, 95, '2014-10-08 04:59:45', 0, 1),
(10, 95, '2014-10-08 04:59:45', NULL, 4),
(11, 96, '2014-10-08 05:05:38', NULL, 2),
(12, 96, '2014-10-08 05:05:38', NULL, 3),
(13, 97, '2014-10-08 04:07:33', NULL, 2),
(14, 97, '2014-10-08 04:07:33', NULL, 3),
(15, 98, '2014-10-08 04:59:45', 0, 1),
(16, 98, '2014-10-08 04:59:45', NULL, 4),
(17, 99, '2014-10-08 05:05:38', NULL, 2),
(18, 99, '2014-10-08 05:05:38', NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `y2m_tag`
--

CREATE TABLE IF NOT EXISTS `y2m_tag` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `tag_title` varchar(200) NOT NULL,
  `tag_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `tag_added_ip_address` int(7) unsigned DEFAULT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `tag_title` (`tag_title`),
  KEY `y2m_tag_ibfk_1` (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `y2m_tag`
--

INSERT INTO `y2m_tag` (`tag_id`, `category_id`, `tag_title`, `tag_added_timestamp`, `tag_added_ip_address`) VALUES
(1, 1, 'fashion', '2014-10-08 08:06:39', NULL),
(2, 1, 'sports', '2014-10-08 08:06:39', NULL),
(3, 1, 'games', '2014-10-08 08:06:57', NULL),
(4, 1, 'culture', '2014-10-08 08:06:57', NULL),
(5, 1, 'tag1', '2014-10-09 10:39:04', NULL),
(6, 1, 'tag2', '2014-10-09 10:39:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `y2m_tag_category`
--

CREATE TABLE IF NOT EXISTS `y2m_tag_category` (
  `tag_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_category_title` varchar(100) NOT NULL,
  `tag_category_icon` varchar(100) NOT NULL,
  `tag_category_desc` varchar(255) DEFAULT NULL,
  `tag_category_status` tinyint(1) DEFAULT NULL,
  `tag_category_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tag_category_id`),
  UNIQUE KEY `tag_category_title` (`tag_category_title`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `y2m_tag_category`
--

INSERT INTO `y2m_tag_category` (`tag_category_id`, `tag_category_title`, `tag_category_icon`, `tag_category_desc`, `tag_category_status`, `tag_category_timestamp`) VALUES
(1, 'photography', 'photography-icon.png', 'photography', 1, '2014-10-08 07:06:37');

-- --------------------------------------------------------

--
-- Table structure for table `y2m_user`
--

CREATE TABLE IF NOT EXISTS `y2m_user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_given_name` varchar(200) DEFAULT NULL,
  `user_first_name` varchar(200) DEFAULT NULL,
  `user_middle_name` varchar(200) DEFAULT NULL,
  `user_last_name` varchar(200) DEFAULT NULL,
  `user_profile_name` varchar(255) NOT NULL,
  `user_status` enum('live','suspend','block','delete','not activated') CHARACTER SET utf8 COLLATE utf8_bin DEFAULT NULL COMMENT 'live,suspend,block',
  `user_added_ip_address` int(7) unsigned DEFAULT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `user_password` varchar(200) DEFAULT NULL,
  `user_gender` enum('male','female') DEFAULT NULL,
  `user_timeline_photo_id` int(11) DEFAULT NULL,
  `user_profile_photo_id` int(11) DEFAULT NULL,
  `user_mobile` varchar(30) DEFAULT NULL,
  `user_verification_key` varchar(200) DEFAULT NULL,
  `user_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_modified_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_modified_ip_address` int(7) unsigned DEFAULT NULL,
  `user_register_type` enum('facebook','admin','site') NOT NULL DEFAULT 'admin',
  `user_fbid` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_email` (`user_email`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `y2m_user`
--

INSERT INTO `y2m_user` (`user_id`, `user_given_name`, `user_first_name`, `user_middle_name`, `user_last_name`, `user_profile_name`, `user_status`, `user_added_ip_address`, `user_email`, `user_password`, `user_gender`, `user_timeline_photo_id`, `user_profile_photo_id`, `user_mobile`, `user_verification_key`, `user_added_timestamp`, `user_modified_timestamp`, `user_modified_ip_address`, `user_register_type`, `user_fbid`) VALUES
(1, 'anoop', NULL, NULL, NULL, 'anoop', 'live', NULL, 'anpmtp23@gmail.com', '$2y$14$AR/zYeP7kUV13x0u2TIy5eyPLB27X0CcG.yMAEp8oRKyNUaY.kyRu', NULL, NULL, NULL, NULL, 'c9899196a592d6e2f657cca688fd6a63', '2014-10-13 14:13:07', '2014-10-13 12:13:06', NULL, 'site', ''),
(2, 'anoop', NULL, NULL, NULL, 'anoop_ZWoNs', 'not activated', NULL, 'anpmtp2@gmail.com', '$2y$14$92q73gqeYi0ZY7nK8M1FreL0sjzQR.xrOSYKsuYC8gGcnAL35wDtC', NULL, NULL, NULL, NULL, '84589c68e73893ba185f77ca3210f4bb', '2014-10-13 14:24:40', '2014-10-13 12:24:40', NULL, 'site', ''),
(3, 'anoop', NULL, NULL, NULL, 'anoop_yYVNP', 'not activated', NULL, 'anpmtp3@gmail.com', '$2y$14$XzBr1y5joi0CcnB6WkoETu7zSiHf4s48MsGh9fD3YYict80G3YGWO', NULL, NULL, NULL, NULL, 'ade6565bad55dfe2b5ab8950d8b10fd4', '2014-10-13 14:25:30', '2014-10-13 12:25:30', NULL, 'site', ''),
(4, 'anoop', NULL, NULL, NULL, 'anoop_v4MJc', 'not activated', NULL, 'anpmtp4@gmail.com', '$2y$14$kBM70QQTdcAp06c566q2A.JlxM.VojA.U3bZo7AIFpznSdGjZR4xi', NULL, NULL, NULL, NULL, 'ec0635ada2904f2b36d13717cb401ab6', '2014-10-13 14:29:37', '2014-10-13 12:29:37', NULL, 'site', ''),
(5, 'anoop', NULL, NULL, NULL, 'anoop_Uj1I0', 'not activated', NULL, 'anpmtp5@gmail.com', '$2y$14$vKs800jCWLC73OVYnHiZK.Lf9RAyq5JkS.JsDdUgMalILNkEmM2IC', NULL, NULL, NULL, NULL, '8f4f3c4bb33a3ea623745b1aa789296e', '2014-10-13 14:30:37', '2014-10-13 12:30:37', NULL, 'site', ''),
(6, 'anoop', NULL, NULL, NULL, 'anoop_wyQLX', 'not activated', NULL, 'anpmtp6@gmail.com', '$2y$14$AScHM9t3/Y/DTgeWOIyw2eee9PcTMERK7NWXGbCFgT1CwpmkLsdo2', NULL, NULL, NULL, NULL, '1d4a1fa1ceb33ba3b37ec35a7a342897', '2014-10-13 14:31:43', '2014-10-13 12:31:43', NULL, 'site', ''),
(7, 'anoop', NULL, NULL, NULL, 'anoop_A0rbj', 'not activated', NULL, 'anpmtp7@gmail.com', '$2y$14$YXxeJO/qWu3vjn2ykYHFrOg2ENUP6lK97kZjKUGkRwpiVdxkQoYS6', NULL, NULL, NULL, NULL, '8bf2f751c4a9b215ff4f5feda83d7b0e', '2014-10-13 14:33:09', '2014-10-13 12:33:09', NULL, 'site', ''),
(8, 'anoop', NULL, NULL, NULL, 'anoop_mPx0M', 'not activated', NULL, 'anpmtp8@gmail.com', '$2y$14$l/QgWGfj1c.RSYyPKsYgqOrY/0sS5R6zoUEcbMik.kcTY8J7jte1e', NULL, NULL, NULL, NULL, '02d9697d17c58f29d5ebdcb5e86832ae', '2014-10-14 05:27:47', '2014-10-14 03:27:47', NULL, 'site', ''),
(9, 'anoop', NULL, NULL, NULL, 'anoop_v6gMk', 'not activated', NULL, 'anpmtp10@gmail.com', '$2y$14$BltFfFG9/2.uYW9.ceIrD.xO37O8UXLA3noIdjnwrouTwdVMTO6pu', NULL, NULL, NULL, NULL, 'bfe226f1156150cc6ba11fc1e6f943c2', '2014-10-14 05:48:18', '2014-10-14 03:48:18', NULL, 'site', ''),
(10, 'anoop', NULL, NULL, NULL, 'anoop_lHjvW', 'live', NULL, 'anpmtp@gmail.com', '$2y$14$.6y.BrtRAoD6sYF.d5M1X.rDJfy7r86DMmV6ZoA5ljMoHPQgYNLQi', NULL, NULL, NULL, NULL, '17f3de8c98930f6cf02c789e95b78147', '2014-10-14 06:22:21', '2014-10-14 04:22:21', NULL, 'site', ''),
(11, 'anoop', NULL, NULL, NULL, 'anoop_2bDVm', 'not activated', NULL, 'anpmtp124@gmail.com', '$2y$14$pvs9UVSyElASd5UZ2DwRHO7a2XRDiu//P2rkTCzmOeYjvueV8D86e', NULL, NULL, NULL, NULL, 'f52aff4391708321c5f16ed9dd9aaa0f', '2014-10-14 08:35:44', '2014-10-14 06:35:44', NULL, 'site', '');

-- --------------------------------------------------------

--
-- Table structure for table `y2m_user_friend`
--

CREATE TABLE IF NOT EXISTS `y2m_user_friend` (
  `user_friend_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_friend_sender_user_id` int(11) DEFAULT NULL,
  `user_friend_friend_user_id` int(11) DEFAULT NULL,
  `user_friend_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_friend_added_ip_address` int(7) unsigned DEFAULT NULL,
  `user_friend_status` enum('deleted','available') DEFAULT 'available',
  PRIMARY KEY (`user_friend_id`),
  KEY `user_friend_sender_id` (`user_friend_sender_user_id`),
  KEY `user_friend_friend_id` (`user_friend_friend_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `y2m_user_group`
--

CREATE TABLE IF NOT EXISTS `y2m_user_group` (
  `user_group_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_group_user_id` int(11) DEFAULT NULL,
  `user_group_group_id` int(11) DEFAULT NULL,
  `user_group_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_group_added_ip_address` int(7) unsigned DEFAULT NULL,
  `user_group_status` enum('deleted','available') DEFAULT 'available',
  `user_group_is_owner` tinyint(1) NOT NULL DEFAULT '0',
  `user_group_role` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_group_id`),
  KEY `user_group_user_id` (`user_group_user_id`),
  KEY `user_group_group_id` (`user_group_group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `y2m_user_profile`
--

CREATE TABLE IF NOT EXISTS `y2m_user_profile` (
  `user_profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_profile_dob` date DEFAULT NULL,
  `user_profile_about_me` text,
  `user_profile_profession` varchar(200) DEFAULT NULL,
  `user_profile_profession_at` varchar(200) DEFAULT NULL,
  `user_profile_user_id` int(11) DEFAULT NULL,
  `user_profile_city_id` int(11) DEFAULT NULL,
  `user_profile_country_id` int(11) DEFAULT NULL,
  `user_address` text,
  `user_profile_current_location` varchar(80) DEFAULT NULL,
  `user_profile_phone` varchar(20) DEFAULT NULL,
  `user_profile_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_profile_added_ip_address` int(7) unsigned DEFAULT NULL,
  `user_profile_modified_timestamp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_profile_modified_ip_address` int(7) unsigned DEFAULT NULL,
  `user_profile_status` enum('deleted','available') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`user_profile_id`),
  KEY `user_id` (`user_profile_user_id`),
  KEY `user_profile_country_id` (`user_profile_country_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `y2m_user_profile`
--

INSERT INTO `y2m_user_profile` (`user_profile_id`, `user_profile_dob`, `user_profile_about_me`, `user_profile_profession`, `user_profile_profession_at`, `user_profile_user_id`, `user_profile_city_id`, `user_profile_country_id`, `user_address`, `user_profile_current_location`, `user_profile_phone`, `user_profile_added_timestamp`, `user_profile_added_ip_address`, `user_profile_modified_timestamp`, `user_profile_modified_ip_address`, `user_profile_status`) VALUES
(1, NULL, NULL, NULL, NULL, 5, 1, 2, NULL, NULL, NULL, '2014-10-13 14:30:37', NULL, '2014-10-13 12:30:37', NULL, 'available'),
(2, NULL, NULL, NULL, NULL, 6, 1, 2, NULL, NULL, NULL, '2014-10-13 14:31:44', NULL, '2014-10-13 12:31:44', NULL, 'available'),
(3, NULL, NULL, NULL, NULL, 7, 1, 2, NULL, NULL, NULL, '2014-10-13 14:33:09', NULL, '2014-10-13 12:33:09', NULL, 'available'),
(4, NULL, NULL, NULL, NULL, 8, 1, 2, NULL, NULL, NULL, '2014-10-14 05:27:47', NULL, '2014-10-14 03:27:47', NULL, 'available'),
(5, NULL, NULL, NULL, NULL, 9, 1, 2, NULL, NULL, NULL, '2014-10-14 05:48:19', NULL, '2014-10-14 03:48:19', NULL, 'available'),
(6, NULL, NULL, NULL, NULL, 10, 1, 2, NULL, NULL, NULL, '2014-10-14 06:22:21', NULL, '2014-10-14 04:22:21', NULL, 'available'),
(7, NULL, NULL, NULL, NULL, 11, 1, 2, NULL, NULL, NULL, '2014-10-14 08:35:44', NULL, '2014-10-14 06:35:44', NULL, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `y2m_user_profile_photo`
--

CREATE TABLE IF NOT EXISTS `y2m_user_profile_photo` (
  `profile_photo_id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_user_id` int(11) NOT NULL,
  `profile_photo` varchar(255) NOT NULL,
  `profile_photo_added_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_photo_added_ip` varchar(50) NOT NULL,
  `user_profile_photo_status` enum('deleted','available') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`profile_photo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `y2m_user_tag`
--

CREATE TABLE IF NOT EXISTS `y2m_user_tag` (
  `user_tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_tag_user_id` int(11) DEFAULT NULL,
  `user_tag_tag_id` int(11) DEFAULT NULL,
  `user_tag_added_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_tag_added_ip_address` int(7) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_tag_id`),
  KEY `user_tag_user_id` (`user_tag_user_id`),
  KEY `user_tag_tag_id` (`user_tag_tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `y2m_group_tag`
--
ALTER TABLE `y2m_group_tag`
  ADD CONSTRAINT `y2m_group_tag_ibfk_2` FOREIGN KEY (`group_tag_group_id`) REFERENCES `y2m_group` (`group_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `y2m_group_tag_ibfk_3` FOREIGN KEY (`group_tag_tag_id`) REFERENCES `y2m_tag` (`tag_id`) ON UPDATE CASCADE;

--
-- Constraints for table `y2m_tag`
--
ALTER TABLE `y2m_tag`
  ADD CONSTRAINT `y2m_tag_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `y2m_tag_category` (`tag_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `y2m_user_group`
--
ALTER TABLE `y2m_user_group`
  ADD CONSTRAINT `y2m_user_group_ibfk_1` FOREIGN KEY (`user_group_user_id`) REFERENCES `y2m_user` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `y2m_user_group_ibfk_3` FOREIGN KEY (`user_group_group_id`) REFERENCES `y2m_group` (`group_id`) ON UPDATE CASCADE;

--
-- Constraints for table `y2m_user_profile`
--
ALTER TABLE `y2m_user_profile`
  ADD CONSTRAINT `y2m_user_profile_ibfk_1` FOREIGN KEY (`user_profile_user_id`) REFERENCES `y2m_user` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `y2m_user_profile_ibfk_3` FOREIGN KEY (`user_profile_country_id`) REFERENCES `y2m_country` (`country_id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
