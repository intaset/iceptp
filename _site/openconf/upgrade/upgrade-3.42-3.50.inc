<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$upgradeModulesAR = array('oc_program');

ocsql_query("CREATE TABLE IF NOT EXISTS `" . OCC_TABLE_EMAIL_QUEUE . "` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `queued` datetime NOT NULL,
  `sent` datetime default NULL,
  `tries` tinyint(1) NOT NULL default '0',
  `to` varchar(255) collate utf8_unicode_ci NOT NULL,
  `subject` varchar(255) collate utf8_unicode_ci NOT NULL,
  `body` text collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `queued` (`queued`),
  KEY `sent` (`sent`)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT=1
");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_versionLatest', '3.50', 'Latest Version', 'Latest version of software - used for update notification', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_queueEmails', '1', 'Queue Emails', 'Queues emails for delivery, storing each in the database.', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_STATUS . "` (`module`, `setting`, `description`, `status`, `name`, `open`, `close`, `dependency`, `order`) VALUES ('OC', 'OC_status_open', NULL, 0, 'Check Status', NULL, NULL, NULL, 6)");

ocsql_query("UPDATE `" . OCC_TABLE_STATUS . "` SET `order`=10 WHERE `setting`='OC_rev_signup_open'");

ocsql_query("UPDATE `" . OCC_TABLE_STATUS . "` SET `order`=11 WHERE `setting`='OC_rev_signin_open'");

ocsql_query("UPDATE `" . OCC_TABLE_STATUS . "` SET `order`=15 WHERE `setting`='OC_pc_signup_open'");

ocsql_query("UPDATE `" . OCC_TABLE_STATUS . "` SET `order`=16 WHERE `setting`='OC_pc_signin_open'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='3.50' WHERE `setting`='OC_version'");

ocsql_query("DELETE FROM `" . OCC_TABLE_CONFIG . "` WHERE `module`='OC' AND `setting`='OC_allowPaperTransfers' LIMIT 1");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Bolivia' WHERE `country`='Bolivia, Plurinational State Of'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Bolivia' WHERE `country`='Bolivia, Plurinational State Of'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Cocos (Keeling) Islands' WHERE `country`='Cocos (keeling) Islands'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Cocos (Keeling) Islands' WHERE `country`='Cocos (keeling) Islands'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Falkland Islands (Malvinas)' WHERE `country`='Falkland Islands (malvinas)'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Falkland Islands (Malvinas)' WHERE `country`='Falkland Islands (malvinas)'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Guinea-Bissau' WHERE `country`='Guinea-bissau'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Guinea-Bissau' WHERE `country`='Guinea-bissau'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Holy See (Vatican City State)' WHERE `country`='Holy See (vatican City State)'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Holy See (Vatican City State)' WHERE `country`='Holy See (vatican City State)'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Iran' WHERE `country`='Iran, Islamic Republic Of'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Iran' WHERE `country`='Iran, Islamic Republic Of'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Macedonia' WHERE `country`='Macedonia, The Former Yugoslav Republic Of'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Macedonia' WHERE `country`='Macedonia, The Former Yugoslav Republic Of'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Micronesia' WHERE `country`='Micronesia, Federated States Of'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Micronesia' WHERE `country`='Micronesia, Federated States Of'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Moldova' WHERE `country`='Moldova, Republic Of'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Moldova' WHERE `country`='Moldova, Republic Of'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Palestinian Territory' WHERE `country`='Palestinian Territory, Occupied'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Palestinian Territory' WHERE `country`='Palestinian Territory, Occupied'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Tanzania' WHERE `country`='Tanzania, United Republic Of'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Tanzania' WHERE `country`='Tanzania, United Republic Of'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Timor-Leste' WHERE `country`='Timor-leste'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Timor-Leste' WHERE `country`='Timor-leste'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Venezuela' WHERE `country`='Venezuela, Bolivarian Republic Of'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Venezuela' WHERE `country`='Venezuela, Bolivarian Republic Of'");

ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='Virgin Islands, U.S.' WHERE `country`='Virgin Islands, U.s.'");
ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='Virgin Islands, U.S.' WHERE `country`='Virgin Islands, U.s.'");

