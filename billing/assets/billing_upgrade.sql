-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 07, 2012 at 06:57 PM
-- Server version: 5.1.61
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `zpanel_core`
--

-- --------------------------------------------------------

--
-- Table structure for table `x_rb_billing`
--
ALTER TABLE `zpanel_core`.`x_rb_mail` UNIQUE (`name`);

INSERT INTO `zpanel_core`.`x_rb_mail` (`id` ,`name` ,`subject` ,`message` ,`header`) VALUES (
NULL , 'user_signup', 'A new user has signed up for hosting', 'Congratulations <br /><br /> A New user has signed up {{username}} <br /><br /> <hr /> Details :<br /> Name : {{fullname}}<br /> Email : {{email}}<br /> Address : {{address}} {{post}}<br /> Phone : {{phone}}<br /> <br /> <hr /> {{domain}} {{transfer_help}} {{buy_domain}}', ''
);

ALTER TABLE `x_rb_mail` CHANGE `name` `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `x_rb_mail` CHANGE `subject` `subject` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;