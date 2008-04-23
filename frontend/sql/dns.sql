-- phpMyAdmin SQL Dump
-- version 2.6.1-pl2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Mar 05, 2005 at 08:01 PM
-- Server version: 4.1.7
-- PHP Version: 4.3.9
-- 
-- Database: `dns`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `add_queue`
-- 

CREATE TABLE add_queue (
  queueid int(11) NOT NULL auto_increment,
  domain varchar(150) NOT NULL default '',
  address varchar(15) NOT NULL default '0',
  `password` varchar(150) NOT NULL default '',
  completed tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (queueid),
  KEY domain (domain,address),
  KEY completed (completed)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `admins`
-- 

CREATE TABLE admins (
  userid int(11) NOT NULL auto_increment,
  username varchar(150) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  PRIMARY KEY  (userid),
  KEY username (username,`password`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `del_queue`
-- 

CREATE TABLE del_queue (
  queueid int(11) NOT NULL auto_increment,
  domainid int(11) NOT NULL default '0',
  completed tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (queueid),
  KEY domainid (domainid),
  KEY completed (completed)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `domains`
-- 

CREATE TABLE domains (
  domainid int(11) NOT NULL auto_increment,
  domain varchar(150) NOT NULL default '',
  address varchar(15) NOT NULL default '',
  `password` varchar(50) NOT NULL default '',
  PRIMARY KEY  (domainid),
  KEY domain (domain,`password`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `locks`
-- 

CREATE TABLE `locks` (
  lockid int(11) NOT NULL auto_increment,
  `process` varchar(150) NOT NULL default '',
  `timestamp` int(11) NOT NULL default '0',
  PRIMARY KEY  (lockid),
  KEY `process` (`process`,`timestamp`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `mod_queue`
-- 

CREATE TABLE mod_queue (
  queueid int(11) NOT NULL auto_increment,
  domainid int(11) NOT NULL default '0',
  `timestamp` int(11) NOT NULL default '0',
  completed tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (queueid),
  KEY domainid (domainid,completed),
  KEY `timestamp` (`timestamp`)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `records_a`
-- 

CREATE TABLE records_a (
  recordid int(11) NOT NULL auto_increment,
  domainid int(11) NOT NULL default '0',
  name varchar(150) NOT NULL default '',
  address varchar(15) NOT NULL default '',
  PRIMARY KEY  (recordid),
  KEY domainid (domainid,name),
  KEY ip (address)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `records_cname`
-- 

CREATE TABLE records_cname (
  recordid int(11) NOT NULL auto_increment,
  domainid int(11) NOT NULL default '0',
  name varchar(150) NOT NULL default '',
  address varchar(150) NOT NULL default '',
  PRIMARY KEY  (recordid),
  KEY domainid (domainid,name),
  KEY address (address)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `records_mx`
-- 

CREATE TABLE records_mx (
  recordid int(11) NOT NULL auto_increment,
  domainid int(11) NOT NULL default '0',
  priority tinyint(3) NOT NULL default '0',
  address varchar(150) NOT NULL default '',
  PRIMARY KEY  (recordid),
  KEY domainid (domainid),
  KEY address (address),
  KEY priority (priority)
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `sessions`
-- 

CREATE TABLE sessions (
  sesskey varchar(32) NOT NULL default '',
  expiry int(11) unsigned NOT NULL default '0',
  expireref varchar(64) default '',
  `data` longtext,
  PRIMARY KEY  (sesskey),
  KEY expiry (expiry)
) TYPE=MyISAM;
