-- Adminer 3.7.1 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = '+01:00';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `company_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `ic` varchar(15) NOT NULL,
  `size` int(11) NOT NULL,
  `address_street` varchar(100) NOT NULL,
  `address_city` varchar(20) NOT NULL,
  `address_postcode` varchar(50) NOT NULL,
  PRIMARY KEY (`company_id`),
  UNIQUE KEY `name_ic_size_address_street_address_city_address_postcode` (`name`,`ic`,`size`,`address_street`,`address_city`,`address_postcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `person`;
CREATE TABLE `person` (
  `person_id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `academy_title` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `position_id` int(11) NOT NULL,
  PRIMARY KEY (`person_id`),
  UNIQUE KEY `firstname_lastname_academy_title_phone_position_id` (`firstname`,`lastname`,`academy_title`,`phone`,`position_id`),
  KEY `position_id` (`position_id`),
  CONSTRAINT `person_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `position` (`position_id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `position`;
CREATE TABLE `position` (
  `position_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`position_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `questionnaire`;
CREATE TABLE `questionnaire` (
  `questionnaire_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(24) NOT NULL,
  `sector` int(11) DEFAULT NULL,
  `company_id` int(11) DEFAULT NULL,
  `xname` char(7) DEFAULT NULL,
  `work_intensity` float DEFAULT NULL,
  `work_duration` int(11) DEFAULT NULL,
  `work_position_id` int(11) DEFAULT NULL,
  `manager_person_id` int(11) DEFAULT NULL,
  `developer_person_id` int(11) DEFAULT NULL,
  `saved` datetime DEFAULT NULL,
  PRIMARY KEY (`questionnaire_id`),
  UNIQUE KEY `uid` (`uid`),
  KEY `company_id` (`company_id`),
  KEY `work_position_id` (`work_position_id`),
  KEY `manager_person_id` (`manager_person_id`),
  KEY `developer_person_id` (`developer_person_id`),
  CONSTRAINT `questionnaire_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `company` (`company_id`) ON DELETE NO ACTION,
  CONSTRAINT `questionnaire_ibfk_2` FOREIGN KEY (`work_position_id`) REFERENCES `position` (`position_id`) ON DELETE NO ACTION,
  CONSTRAINT `questionnaire_ibfk_3` FOREIGN KEY (`manager_person_id`) REFERENCES `person` (`person_id`),
  CONSTRAINT `questionnaire_ibfk_4` FOREIGN KEY (`developer_person_id`) REFERENCES `person` (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `questionnaire_log`;
CREATE TABLE `questionnaire_log` (
  `questionnaire_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(24) NOT NULL,
  `sector` varchar(50) DEFAULT NULL,
  `company_name` varchar(50) DEFAULT NULL,
  `company_ic` varchar(15) DEFAULT NULL,
  `company_size` varchar(50) DEFAULT NULL,
  `company_address_street` varchar(100) DEFAULT NULL,
  `company_address_city` varchar(20) DEFAULT NULL,
  `company_address_postcode` varchar(50) DEFAULT NULL,
  `xname` char(7) DEFAULT NULL,
  `work_intensity` float DEFAULT NULL,
  `work_duration` varchar(50) DEFAULT NULL,
  `work_position` varchar(50) DEFAULT NULL,
  `manager_position` varchar(50) DEFAULT NULL,
  `manager_firstname` varchar(50) DEFAULT NULL,
  `manager_academy_title` varchar(50) DEFAULT NULL,
  `developer_firstname` varchar(50) DEFAULT NULL,
  `manager_phone` varchar(20) DEFAULT NULL,
  `developer_lastname` varchar(50) DEFAULT NULL,
  `developer_academy_title` varchar(50) DEFAULT NULL,
  `developer_phone` varchar(20) DEFAULT NULL,
  `developer_position` varchar(50) DEFAULT NULL,
  `manager_lastname` varchar(50) DEFAULT NULL,
  `logged` datetime NOT NULL,
  PRIMARY KEY (`questionnaire_log_id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2013-11-21 10:41:39