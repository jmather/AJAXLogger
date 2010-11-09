-- phpMyAdmin SQL Dump
-- version 2.11.9.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 03, 2009 at 10:29 AM
-- Server version: 5.0.67
-- PHP Version: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- --------------------------------------------------------

--
-- Table structure for table `AJAXLogger_messages`
--

DROP TABLE IF EXISTS `AJAXLogger_messages`;
CREATE TABLE IF NOT EXISTS `AJAXLogger_messages` (
  `ts` int(11) NOT NULL,
  `id` bigint(63) NOT NULL auto_increment,
  `file` varchar(255) NOT NULL,
  `function` varchar(255) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY  (`ts`,`id`),
  KEY `file` (`file`),
  KEY `function` (`function`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `AJAXLogger_sessions`
--

DROP TABLE IF EXISTS `AJAXLogger_sessions`;
CREATE TABLE IF NOT EXISTS `AJAXLogger_sessions` (
  `id` char(32) NOT NULL,
  `last_hit` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
