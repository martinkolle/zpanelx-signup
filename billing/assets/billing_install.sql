-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 07, 2012 at 06:57 PM
-- Server version: 5.1.61
-- PHP Version: 5.3.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `zpanel_core`
--

-- --------------------------------------------------------

--
-- Table structure for table `x_rb_billing`
--

DROP TABLE IF EXISTS `x_rb_billing`;
CREATE TABLE IF NOT EXISTS `x_rb_billing` (
  `blg_id` int(9) NOT NULL AUTO_INCREMENT,
  `blg_user` varchar(100) NOT NULL,
  `blg_create` date NOT NULL,
  `blg_duedate` date NOT NULL COMMENT 'due date',
  `blg_inv_id` varchar(255) NOT NULL COMMENT 'invoice id',
  `blg_remind` varchar(100) NOT NULL,
  `blg_desc` text NOT NULL,
  PRIMARY KEY (`blg_id`)
)   DEFAULT CHARSET=utf8;

--
-- Dumping data for table `x_rb_billing`
--

INSERT INTO `x_rb_billing` (`blg_id`, `blg_user`, `blg_create`, `blg_duedate`, `blg_inv_id`, `blg_remind`, `blg_desc`) VALUES
(1, '88', '0000-00-00', '2012-09-19', '1', '', '{"pk_id":"4", "price":"488", "period":"12", "domain":"http://kmweb.dk"}'),
(24, '101', '2012-10-03', '1970-01-01', 'Array', '1970-01-01', ''),
(25, '101', '2012-10-03', '1970-01-01', 'Array', '1970-01-01', '');

-- --------------------------------------------------------

--
-- Table structure for table `x_rb_invoice`
--

DROP TABLE IF EXISTS `x_rb_invoice`;
CREATE TABLE IF NOT EXISTS `x_rb_invoice` (
  `inv_id` int(9) NOT NULL AUTO_INCREMENT,
  `inv_user` varchar(100) NOT NULL,
  `inv_amount` varchar(50) NOT NULL,
  `inv_type` varchar(100) NOT NULL,
  `inv_date` date NOT NULL COMMENT 'Created date',
  `inv_payment` varchar(50) DEFAULT NULL,
  `inv_payment_id` varchar(255) DEFAULT NULL,
  `inv_desc` text NOT NULL COMMENT 'json with domain, hosting, period',
  `inv_token` varchar(255) NOT NULL,
  `inv_status` int(9) NOT NULL DEFAULT '2',
  PRIMARY KEY (`inv_id`)
)   DEFAULT CHARSET=utf8;

--
-- Dumping data for table `x_rb_invoice`
--

INSERT INTO `x_rb_invoice` (`inv_id`, `inv_user`, `inv_amount`, `inv_type`, `inv_date`, `inv_payment`, `inv_payment_id`, `inv_desc`, `inv_token`, `inv_status`) VALUES
(1, '69', '488', 'Initial signup', '2012-08-28', 'paypal', 'id_goes_here', '   {"pk_id":4, "price":480, "period":12}   ', '7834ur9hoiu3rkewol', 1),
(2, '101', '499', 'Initial Signup', '2012-10-03', NULL, NULL, '{"pk_id":"4","price":499,"period":"12","domain":"http://martinkolle.dk","web_help":null}', 'qeJvp6MDp4sqvquGtXTYO8yE', 2),
(3, '88', '499', 'Automatical renew', '2012-09-19', NULL, NULL, '{"pk_id":"4","period":"12","price":499}', '78SSwIcKrTo682qZ1CCjxtol', 2);

-- --------------------------------------------------------

--
-- Table structure for table `x_rb_mail`
--

DROP TABLE IF EXISTS `x_rb_mail`;
CREATE TABLE IF NOT EXISTS `x_rb_mail` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(350) NOT NULL,
  `subject` varchar(350) NOT NULL,
  `message` text NOT NULL,
  `header` text NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8;

--
-- Dumping data for table `x_rb_mail`
--

INSERT INTO `x_rb_mail` (`id`, `name`, `subject`, `message`, `header`) VALUES
(1, 'user_payment', 'Pay for hosting', '<p>{{fullname}}, we''re very pleased you''ve created an account with us.</p>\r\n<p>If for some reason you was unable to complete your order, please follow the \r\nfollowing link:</p>\r\n<p><a href="{{billing_url}}/pay.php?id={{token}}">{{billing_url}}/pay.php?id={{token}}</a></p>\r\n<p>Once your payment is accepted, we will send your login credentails.</p>\r\n\r\nRegards, {{firm}}', ''),
(2, 'user_welcome', 'Welcome at {{firm}}', '<p><b>Welcome, {{username}}!</b></p>\r\n<p>Thank you for choosing us for your web hosting services. This email contains \r\nall the information you need to get started.</p>\r\n<p><b>Your control panel:</b></p>\r\n<p>{{zpanel_url}}<br>\r\nUsername: {{username}}<br>\r\npassword: {{password}}<br />\r\n</p>\r\n<p><b>Adding your domain name:</b></p>\r\n<p>This should be the first thing you do.</p>\r\n<ul>\r\n	<li>Login to your control panel</li>\r\n	<li>Click ''Domains''</li>\r\n	<li>This page allows you to add new domain names</li>\r\n	<li>Then go to Advanced --&gt; DNS Settings --&gt; Select your domain name --&gt; \r\n	Click ''add default records''</li>\r\n</ul>\r\n<p>The last thing to do is change the name server settings with your domain \r\nregistrar to the following:</p>\r\n<p>{{ns1}}<br>\r\n{{ns2}}</p>\r\n<p>Domain names should resolve to our service within 1 hour, however can take up \r\nto 48 hours.</p>\r\n<p><b>FTP:</b></p>\r\n<ul>\r\n	<li>Login to your control panel</li>\r\n	<li>Click ''FTP Accounts''</li>\r\n	<li>Create a username and password</li>\r\n</ul>\r\n<p>FTP can then be accessed using a range of free programs (we recommend \r\nFileZilla) with the address line:</p>\r\n<p>ftp.[your-domain-name].[ext] <br /> \r\nor {{ftp}}</p>\r\n\r\nRegards, {{firm}}', ''),
(3, 'user_expire', 'Account is going to be disabled', 'Dear {{fullname}}<br />\r\n\r\n<b>Your account at {{firm}} will expire in {{days}} days.</b>\r\n<br />\r\nIf you want to renew, please follow the link below. \r\n<br />\r\n{{billing_url}}/pay.php?id={{token}}\r\n<br />\r\nIf you don''t want, we will say thanks for the partnership. \r\n</br>\r\nRegards, {{firm}}', ''),
(4, 'user_disabled', '{{firm}}: {{username}} have been disabled', 'Dear {{fullname}},\n\nYour hosting account at {{firm}} have been disabled because you do not have  paid. \n\nIf you want to reactivate your account, please contact us at {{contact_mail}} <br /><br />\n\nRegards, {{firm}}22', ''),
(5, 'invoice_notify', 'Invoice have not been paid', 'Dear {{fullname}}, \r\n\r\nYou have created a invoice at {{firm}}, but have not completed the payment. Please follow this link to pay: \r\n\r\n<a href="{{link}}">{{link}}</link>\r\n\r\nRegards, {{firm}}', '');

-- --------------------------------------------------------

--
-- Table structure for table `x_rb_payment`
--

DROP TABLE IF EXISTS `x_rb_payment`;
CREATE TABLE IF NOT EXISTS `x_rb_payment` (
  `pm_id` int(9) NOT NULL AUTO_INCREMENT,
  `pm_name` varchar(255) NOT NULL,
  `pm_data` text NOT NULL,
  `pm_active` int(9) NOT NULL,
  PRIMARY KEY (`pm_id`)
)   DEFAULT CHARSET=utf8;

--
-- Dumping data for table `x_rb_payment`
--

INSERT INTO `x_rb_payment` (`pm_id`, `pm_name`, `pm_data`, `pm_active`) VALUES
(1, 'paypal', '<form name="form" method="post" action="{{action}}">\n<input name="charset" value="utf-8" type="hidden">\n<input name="cmd" value="_xclick" type="hidden">\n<input name="upload" value="1" type="hidden">\n\n<input name="first_name" value="{{user_firstname}}" type="hidden">\n<input name="invoice" value="{{invoice}}" type="hidden">\n<input name="email" value="{{email}}" type="hidden">\n<input name="return" value="{{return_url}}" type="hidden">\n<input name="business" value="{{business}}" type="hidden">\n<input name="item_name" value="{{item_name}}" type="hidden">\n<input name="quantity" value="1" type="hidden">\n<input name="country" value="{{country}}" type="hidden">\n<input name="amount" value="{{amount}}" type="hidden">\n<input name="currency_code" value="{{cs}}" type="hidden">\n<input name="image_url" value="{{logo}}" type="hidden">\n<input type="hidden" name="notify_url" value="{{notify_url}}" />\n<input value="Payment" class="defbtn" type="submit">\n</form>', 1);

-- --------------------------------------------------------

--
-- Table structure for table `x_rb_price`
--

DROP TABLE IF EXISTS `x_rb_price`;
CREATE TABLE IF NOT EXISTS `x_rb_price` (
  `pkp_id` int(9) NOT NULL AUTO_INCREMENT,
  `pk_id` varchar(9) NOT NULL COMMENT 'Package id',
  `pkp_domain` text COMMENT 'domain price -json',
  `pkp_hosting` text COMMENT 'hosting price -json',
  PRIMARY KEY (`pkp_id`)
)   DEFAULT CHARSET=utf8;

--
-- Dumping data for table `x_rb_price`
--

INSERT INTO `x_rb_price` (`pkp_id`, `pk_id`, `pkp_domain`, `pkp_hosting`) VALUES
(1, '3', '', '{"hosting":[{"month":12,"price":499},{"month":6,"price":220}]}');

-- --------------------------------------------------------

--
-- Table structure for table `x_rb_settings`
--

DROP TABLE IF EXISTS `x_rb_settings`;
CREATE TABLE IF NOT EXISTS `x_rb_settings` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(350) NOT NULL,
  `value` varchar(350) NOT NULL,
  `title` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  PRIMARY KEY (`id`)
)   DEFAULT CHARSET=utf8;

--
-- Dumping data for table `x_rb_settings`
--

INSERT INTO `x_rb_settings` (`id`, `name`, `value`, `title`, `desc`) VALUES
(1, 'email.from', 'from@zpanelx.com', 'Email from', 'Emails from the system will be send from this email. '),
(2, 'email.fromname', 'Billing firm', 'Email from name', 'Your name will be showed as the sender. '),
(3, 'system.url_billing', 'http://billing.zpanelx.com', 'Billing url', 'The url to your billing website. '),
(4, 'system.url_zpanel', 'zpanel.zpanelx.com', 'Zpanel url', 'The url to the zpanel'),
(5, 'system.firm', 'Firm', 'Firm', 'All email will be using this as a regards.  Eg. Zpanel'),
(6, 'user.expire_days', '20', 'User expire', 'How many days should there go before the user gets a invoice that their account is going to be disabled. '),
(7, 'user.disable_days', '2', 'User disable', 'How many days should there go from the expire day to the user will be disabled.'),
(8, 'email.contact_email', 'billing@zpanelc.com', 'Contact email', 'Your email the customer can contact you at. '),
(9, 'system.test', 'true', 'Test', 'Are you testing the script? true for yes | false for not'),
(10, 'system.debug', 'false', 'Debug', 'This is a feature.'),
(11, 'payment.notify_url', 'http://billing.zpanelx.com/ipn.php', 'IPN url', 'The url to your IPN file. If you are using my eg. is it http://your_uploaded_files.dk/ipn.php'),
(12, 'domain.ns1', 'name1.server.dk', 'Name server 1', ''),
(13, 'domain.ns2', 'name2.server.dk', 'Name server 2', ''),
(14, 'payment.cs', 'DKK', 'Currency', 'Enter the currency you are using. Please see paypal for the right syntax.'),
(15, 'payment.country', 'Denmark', 'Country', ''),
(16, 'payment.logo', 'http://zpanelx.com/logo.png', 'Payment logo', 'eg. http://kagemand.dk/logo.png'),
(17, 'payment.email_paypal', 'bussiness.paypal@zpanelx.com', 'Paypal business email', 'The money will be "send" to this email. '),
(18, 'payment.email_error', 'error.paypal@zpanelx.com', 'Paypal error email', 'All errors from paypal will be send to this email. '),
(19, 'payment.return_url', 'http://zpanelx.com/billing-succes', 'Paypal return url', 'If you activate automatical return url from paypal, will the user be redirected to this url after payment success. '),
(20, 'user.reseller_id', '1', 'Reseller id', 'The user will be assigned to this user.'),
(21, 'user.group_id', '3', 'User group', 'The user will be assigned to this user group. Normal user is 3'),
(22, 'invoice.notify', '2', 'Invoice notify', 'Notify the user who not have paid their invoice after :days:');

ALTER TABLE `zpanel_core`.`x_rb_mail` UNIQUE (`name`);

INSERT INTO `zpanel_core`.`x_rb_mail` (`id` ,`name` ,`subject` ,`message` ,`header`) VALUES (
NULL , 'user_signup', 'A new user has signed up for hosting', 'Congratulations <br /><br /> A New user has signed up {{username}} <br /><br /> <hr /> Details :<br /> Name : {{fullname}}<br /> Email : {{email}}<br /> Address : {{address}} {{post}}<br /> Phone : {{phone}}<br /> <br /> <hr /> {{domain}} {{transfer_help}} {{buy_domain}}', ''
);

ALTER TABLE `x_rb_mail` CHANGE `name` `name` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
ALTER TABLE `x_rb_mail` CHANGE `subject` `subject` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;