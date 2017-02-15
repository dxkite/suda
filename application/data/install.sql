-- ----------------------------------------------------------
-- PHP Simple Library Database Backup File
-- Create On 2017-02-15 09:04:28
-- Host: localhost   Database: test_api
-- Server version	10.1.10-MariaDB
-- ------------------------------------------------------
/*!40101 SET NAMES utf8 */;

--
-- Create Table mc_client
--

DROP TABLE IF EXISTS `mc_client`;
CREATE TABLE `mc_client` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '客户端ID',
  `ip` bigint(32) NOT NULL COMMENT '客户端IP',
  `machine` varchar(32) NOT NULL COMMENT '机器码MD5',
  `black` tinyint(1) NOT NULL COMMENT '黑名单',
  `offset` bigint(20) NOT NULL COMMENT '最后便偏移',
  `length` bigint(20) NOT NULL COMMENT '长度',
  `crc32` bigint(20) NOT NULL COMMENT 'crc32值',
  `last` int(11) NOT NULL COMMENT '最后活动时间',
  `online` tinyint(1) NOT NULL COMMENT '是否在线',
  PRIMARY KEY (`id`),
  KEY `black` (`black`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Create Table mc_key
--

DROP TABLE IF EXISTS `mc_key`;
CREATE TABLE `mc_key` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '关键字ID',
  `type` int(1) NOT NULL COMMENT '关键字类型',
  `key` varchar(255) NOT NULL COMMENT '关键字',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Create Table mc_keywords
--

DROP TABLE IF EXISTS `mc_keywords`;
CREATE TABLE `mc_keywords` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '关键字ID',
  `type` int(1) NOT NULL COMMENT '关键字类型',
  `key` varchar(255) NOT NULL COMMENT '关键字',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Create Table mc_user
--

DROP TABLE IF EXISTS `mc_user`;
CREATE TABLE `mc_user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `name` varchar(13) NOT NULL COMMENT '用户名',
  `password` varchar(60) NOT NULL COMMENT '密码HASH',
  `group` bigint(20) NOT NULL DEFAULT '0' COMMENT '分组ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `group` (`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


