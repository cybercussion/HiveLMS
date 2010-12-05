-- phpMyAdmin SQL Dump
-- version 2.8.0.1
-- http://www.phpmyadmin.net
-- 
-- Host: custsqlmoo07
-- Generation Time: Dec 05, 2010 at 03:02 PM
-- Server version: 5.0.83
-- PHP Version: 4.4.9
-- 
-- Database: `hive`
-- 
CREATE DATABASE `hive` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `hive`;

-- --------------------------------------------------------

-- 
-- Table structure for table `hive_course`
-- 

CREATE TABLE `hive_course` (
  `myID` int(8) NOT NULL auto_increment,
  `myTierID` int(8) default NULL,
  `myName` varchar(100) collate utf8_unicode_ci NOT NULL,
  `myValue` varchar(100) collate utf8_unicode_ci NOT NULL,
  `myOrder` int(4) default NULL,
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `hive_course`
-- 

INSERT INTO `hive_course` VALUES (1, NULL, 'title', 'Demo Innovation Challenge', 1);

-- --------------------------------------------------------

-- 
-- Table structure for table `hive_course_data`
-- 

CREATE TABLE `hive_course_data` (
  `myID` int(8) NOT NULL auto_increment,
  `myTierID` int(8) NOT NULL,
  `myName` varchar(150) character set latin1 NOT NULL,
  `myValue` varchar(150) character set latin1 NOT NULL,
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `hive_course_data`
-- 

INSERT INTO `hive_course_data` VALUES (1, 1, 'description', 'This is the Innovation Challenge demo assignment.');

-- --------------------------------------------------------

-- 
-- Table structure for table `hive_lesson`
-- 

CREATE TABLE `hive_lesson` (
  `myID` int(8) NOT NULL auto_increment,
  `myTierID` int(8) NOT NULL,
  `myName` varchar(100) collate utf8_unicode_ci NOT NULL,
  `myValue` varchar(100) collate utf8_unicode_ci NOT NULL,
  `myOrder` int(4) default NULL,
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- 
-- Dumping data for table `hive_lesson`
-- 

INSERT INTO `hive_lesson` VALUES (1, 1, 'title', 'IC SCO', 1);
INSERT INTO `hive_lesson` VALUES (2, 1, 'title', 'Literature SCO', 2);

-- --------------------------------------------------------

-- 
-- Table structure for table `hive_lesson_data`
-- 

CREATE TABLE `hive_lesson_data` (
  `myID` int(8) NOT NULL auto_increment,
  `myTierID` int(8) NOT NULL,
  `myName` varchar(150) collate utf8_unicode_ci NOT NULL,
  `myValue` varchar(150) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- 
-- Dumping data for table `hive_lesson_data`
-- 

INSERT INTO `hive_lesson_data` VALUES (1, 1, 'path', 'content/ic/demo1/player_dev.html');
INSERT INTO `hive_lesson_data` VALUES (2, 2, 'path', 'content/ic/demo2/player_dev.html');
INSERT INTO `hive_lesson_data` VALUES (3, 1, 'description', 'This is a joint SCO with a mix of templates.');
INSERT INTO `hive_lesson_data` VALUES (4, 2, 'description', 'This is a literature SCO featuring 4 templates.');

-- --------------------------------------------------------

-- 
-- Table structure for table `live_quiz`
-- 

CREATE TABLE `live_quiz` (
  `myID` int(4) NOT NULL auto_increment,
  `myUserID` varchar(50) collate utf8_unicode_ci default NULL,
  `myName` varchar(150) collate utf8_unicode_ci NOT NULL,
  `myValue` varchar(150) collate utf8_unicode_ci NOT NULL,
  `myPage` int(4) NOT NULL default '0',
  `myStatus` int(4) NOT NULL default '1',
  `myTimeStamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

-- 
-- Dumping data for table `live_quiz`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `live_quiz_class`
-- 

CREATE TABLE `live_quiz_class` (
  `myID` int(4) NOT NULL auto_increment,
  `myTierID` int(4) NOT NULL,
  `myUserID` varchar(50) collate utf8_unicode_ci NOT NULL,
  `myPage` int(4) default NULL,
  `myTotalPage` int(4) default NULL,
  `myCorrect` int(4) default NULL,
  `myAnswer` varchar(100) collate utf8_unicode_ci default NULL,
  `myTimeStamp` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

-- 
-- Dumping data for table `live_quiz_class`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `profile_access`
-- 

CREATE TABLE `profile_access` (
  `myID` int(4) NOT NULL auto_increment,
  `myAccess` varchar(50) character set latin1 NOT NULL default '',
  `myStatus` int(4) NOT NULL default '0',
  `myOrder` int(4) NOT NULL default '0',
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- 
-- Dumping data for table `profile_access`
-- 

INSERT INTO `profile_access` VALUES (1, 'Guest', 1, 1);
INSERT INTO `profile_access` VALUES (2, 'Student', 1, 2);
INSERT INTO `profile_access` VALUES (3, 'Teacher', 1, 3);
INSERT INTO `profile_access` VALUES (4, 'Super', 1, 4);
INSERT INTO `profile_access` VALUES (5, 'Administrator', 1, 5);

-- --------------------------------------------------------

-- 
-- Table structure for table `profile_accounts`
-- 

CREATE TABLE `profile_accounts` (
  `myID` int(8) NOT NULL auto_increment,
  `myGUID` varchar(50) collate utf8_unicode_ci NOT NULL,
  `myUsername` varchar(150) collate utf8_unicode_ci NOT NULL,
  `myPassword` varchar(50) collate utf8_unicode_ci NOT NULL,
  `myFirstname` varchar(50) collate utf8_unicode_ci default NULL,
  `myLastname` varchar(50) collate utf8_unicode_ci default NULL,
  `myPhone` varchar(50) collate utf8_unicode_ci default NULL,
  `myEmail` varchar(150) collate utf8_unicode_ci NOT NULL,
  `myDomain` varchar(150) collate utf8_unicode_ci default NULL,
  `myGender` varchar(10) collate utf8_unicode_ci default NULL,
  `myAvatar` varchar(250) collate utf8_unicode_ci default NULL,
  `myAccountType` int(4) NOT NULL default '1',
  `myAccess` int(4) NOT NULL default '1',
  `myStatus` int(4) NOT NULL default '3',
  `myModDate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `myCreateDate` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `profile_accounts`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `profile_assignments`
-- 

CREATE TABLE `profile_assignments` (
  `myID` int(8) NOT NULL auto_increment,
  `myUserID` varchar(50) collate utf8_unicode_ci NOT NULL,
  `myAssignID` int(8) NOT NULL,
  `myStatus` int(4) NOT NULL default '1',
  `myOrder` int(4) NOT NULL,
  `myStartDate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `myEndDate` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=18 ;

-- 
-- Dumping data for table `profile_assignments`
-- 

INSERT INTO `profile_assignments` VALUES (1, 'd5464516d59102dccd60d5471cbff3e4', 1, 1, 1, '2010-11-12 09:51:09', '2010-12-12 09:51:09');
INSERT INTO `profile_assignments` VALUES (2, '0c3a301f4fae6a7fc99b7079cabd8b25', 1, 1, 1, '2010-11-12 11:20:54', '2010-12-12 11:20:54');
INSERT INTO `profile_assignments` VALUES (3, '27705ec8d7d9a8b586cba2e71b70310d', 1, 1, 1, '2010-11-13 14:06:33', '2010-12-13 14:06:33');
INSERT INTO `profile_assignments` VALUES (4, 'd636816d3857fa9d801057e62118c9c8', 1, 1, 1, '2010-11-13 19:10:28', '2010-12-13 19:10:28');
INSERT INTO `profile_assignments` VALUES (5, 'e5386f88a34490fa388d4b5b7d05263d', 1, 1, 1, '2010-11-13 21:09:26', '2010-12-13 21:09:26');
INSERT INTO `profile_assignments` VALUES (6, '74c7b984e61a2b09693aec69d905595e', 1, 1, 1, '2010-11-14 09:49:31', '2010-12-14 09:49:31');
INSERT INTO `profile_assignments` VALUES (7, 'f741896604150e49b406a53c393ab9b8', 1, 1, 1, '2010-11-15 10:30:16', '2010-12-15 10:30:16');
INSERT INTO `profile_assignments` VALUES (8, '09a1404f4b9f84599dfffd8a76a7096b', 1, 1, 1, '2010-11-15 10:31:03', '2010-12-15 10:31:03');
INSERT INTO `profile_assignments` VALUES (9, '0a35ff9dfa3458c0a9eff1772d8f2f1c', 1, 1, 1, '2010-11-15 10:31:12', '2010-12-15 10:31:12');
INSERT INTO `profile_assignments` VALUES (10, '6879d8be5aaf379cb763ebab55395bdc', 1, 1, 1, '2010-11-15 10:31:50', '2010-12-15 10:31:50');
INSERT INTO `profile_assignments` VALUES (11, '57502e2418d3177acd59a64084bce7fe', 1, 1, 1, '2010-11-15 10:32:43', '2010-12-15 10:32:43');
INSERT INTO `profile_assignments` VALUES (12, 'f7cb37f78ae8248d2eefc43f10b3d166', 1, 1, 1, '2010-11-16 14:56:51', '2010-12-16 14:56:51');
INSERT INTO `profile_assignments` VALUES (13, '9f442d6e20cf900484883cd5204eefac', 1, 1, 1, '2010-11-16 14:58:43', '2010-12-16 14:58:43');
INSERT INTO `profile_assignments` VALUES (14, 'd6f749e7b8cbebc9ba87d825d6e27857', 1, 1, 1, '2010-11-19 01:04:45', '2010-12-19 01:04:45');
INSERT INTO `profile_assignments` VALUES (15, 'fcf3ac233f4c640dcdf4577b5a328692', 1, 1, 1, '2010-11-19 13:33:55', '2010-12-19 13:33:55');
INSERT INTO `profile_assignments` VALUES (16, 'd531a91c3ba8e9f5fab32fd7a96fe7dc', 1, 1, 1, '2010-11-19 13:38:57', '2010-12-19 13:38:57');
INSERT INTO `profile_assignments` VALUES (17, '3f3b08e245475c0b71b6d688062eb8ef', 1, 1, 1, '2010-11-19 16:02:22', '2010-12-19 16:02:22');

-- --------------------------------------------------------

-- 
-- Table structure for table `profile_avatars`
-- 

CREATE TABLE `profile_avatars` (
  `myID` int(4) NOT NULL auto_increment,
  `myName` varchar(50) character set latin1 NOT NULL default '',
  `myFilename` varchar(50) character set latin1 NOT NULL default '',
  `myType` varchar(10) character set latin1 NOT NULL default '',
  `myStatus` int(4) NOT NULL default '1',
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `profile_avatars`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `profile_history`
-- 

CREATE TABLE `profile_history` (
  `myID` int(4) NOT NULL auto_increment,
  `myUserID` varchar(50) collate utf8_unicode_ci default NULL,
  `myIP` varchar(20) collate utf8_unicode_ci default NULL,
  `myPlatform` varchar(50) collate utf8_unicode_ci default NULL,
  `myBrowser` varchar(20) collate utf8_unicode_ci default NULL,
  `myBrowserVersion` varchar(20) collate utf8_unicode_ci default NULL,
  `myScreenSize` varchar(20) collate utf8_unicode_ci default NULL,
  `myScreenPixelDepth` varchar(20) collate utf8_unicode_ci default NULL,
  `myLanguage` varchar(20) collate utf8_unicode_ci default NULL,
  `myFlash` varchar(20) collate utf8_unicode_ci default NULL,
  `myVisits` int(8) default NULL,
  `myModDate` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `myCreateDate` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `profile_history`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `status`
-- 

CREATE TABLE `status` (
  `myID` int(4) NOT NULL auto_increment,
  `myStatus` varchar(50) collate utf8_unicode_ci NOT NULL,
  `myOrder` int(4) default NULL,
  PRIMARY KEY  (`myID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

-- 
-- Dumping data for table `status`
-- 

INSERT INTO `status` VALUES (1, 'enabled', 1);
INSERT INTO `status` VALUES (2, 'disabled', 2);
INSERT INTO `status` VALUES (3, 'pending', 3);
