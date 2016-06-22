-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2013 年 12 月 03 日 07:56
-- 服务器版本: 5.6.12-log
-- PHP 版本: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `zhuanpan`
--
CREATE DATABASE IF NOT EXISTS `zhuanpan` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `zhuanpan`;

-- --------------------------------------------------------

--
-- 表的结构 `magic_config`
--

CREATE TABLE IF NOT EXISTS `magic_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `praisefeild` varchar(20) NOT NULL COMMENT '奖项标示字段',
  `praisename` varchar(20) NOT NULL COMMENT '奖项名字',
  `min` text NOT NULL COMMENT '最小角度',
  `max` text NOT NULL COMMENT '最大角度',
  `praisecontent` text NOT NULL COMMENT '奖项内容',
  `praisenumber` int(3) NOT NULL COMMENT '奖项库存次数',
  `chance` int(10) unsigned NOT NULL COMMENT '本奖项的概率',
  PRIMARY KEY (`id`),
  KEY `praisefeild` (`praisefeild`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='奖项配置' AUTO_INCREMENT=8 ;

--
-- 转存表中的数据 `magic_config`
--

INSERT INTO `magic_config` (`id`, `praisefeild`, `praisename`, `min`, `max`, `praisecontent`, `praisenumber`, `chance`) VALUES
(1, 'first', '一等奖', '1', '29', 'iphone5s土豪金一部', -1, 1),
(2, 'second', '二等奖', '302', '328', 'ipadmini', -1, 2),
(3, 'Third', '三等奖', '242', '268', '现金500', -1, 5),
(4, 'Fourth', '四等奖', '182', '208', '现金300', -1, 7),
(5, 'Fifth', '五等奖', '122', '148', '现金200', -1, 10),
(6, 'Sixth', '六等奖', '62', '88', '现金100', -1, 25),
(7, 'Seventh', '七等奖', '32,92,152,212,272,332', '58,118,178,238,298,358', '小熊宝宝一个', -1, 50);

-- --------------------------------------------------------

--
-- 表的结构 `magic_user`
--

CREATE TABLE IF NOT EXISTS `magic_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL COMMENT '用户名',
  `password` varchar(32) NOT NULL COMMENT '密码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='用户表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `magic_user`
--

INSERT INTO `magic_user` (`id`, `username`, `password`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3');

-- --------------------------------------------------------

--
-- 表的结构 `magic_useraddnumber`
--

CREATE TABLE IF NOT EXISTS `magic_useraddnumber` (
  `aid` int(10) unsigned NOT NULL COMMENT '对应用户id',
  `number` tinyint(3) unsigned NOT NULL COMMENT '该用户剩余抽奖次数'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户剩余抽奖次数';

--
-- 转存表中的数据 `magic_useraddnumber`
--

INSERT INTO `magic_useraddnumber` (`aid`, `number`) VALUES
(1, 2);

-- --------------------------------------------------------

--
-- 表的结构 `magic_useraddorder`
--

CREATE TABLE IF NOT EXISTS `magic_useraddorder` (
  `aid` int(10) unsigned NOT NULL COMMENT '对应用户id',
  `prizeid` int(10) unsigned NOT NULL COMMENT '对应用户已获得的奖项'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户已获得奖项';

--
-- 转存表中的数据 `magic_useraddorder`
--

INSERT INTO `magic_useraddorder` (`aid`, `prizeid`) VALUES
(1, 3);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
