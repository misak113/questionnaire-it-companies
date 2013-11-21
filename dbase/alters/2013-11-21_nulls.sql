ALTER TABLE `company`
CHANGE `name` `name` varchar(50) COLLATE 'utf8_general_ci' NULL AFTER `company_id`,
CHANGE `ic` `ic` varchar(15) COLLATE 'utf8_general_ci' NULL AFTER `name`,
CHANGE `size` `size` int(11) NULL AFTER `ic`,
CHANGE `address_street` `address_street` varchar(100) COLLATE 'utf8_general_ci' NULL AFTER `size`,
CHANGE `address_city` `address_city` varchar(20) COLLATE 'utf8_general_ci' NULL AFTER `address_street`,
CHANGE `address_postcode` `address_postcode` varchar(50) COLLATE 'utf8_general_ci' NULL AFTER `address_city`,
COMMENT=''; -- 0.014 s

ALTER TABLE `person`
CHANGE `firstname` `firstname` varchar(50) COLLATE 'utf8_general_ci' NULL AFTER `person_id`,
CHANGE `lastname` `lastname` varchar(50) COLLATE 'utf8_general_ci' NULL AFTER `firstname`,
CHANGE `academy_title` `academy_title` varchar(50) COLLATE 'utf8_general_ci' NULL AFTER `lastname`,
CHANGE `phone` `phone` varchar(20) COLLATE 'utf8_general_ci' NULL AFTER `academy_title`,
CHANGE `position_id` `position_id` int(11) NULL AFTER `phone`,
COMMENT=''; -- 0.013 s