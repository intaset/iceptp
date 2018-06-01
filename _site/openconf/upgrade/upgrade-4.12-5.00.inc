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

ocsql_query("ALTER TABLE `" . OCC_TABLE_AUTHOR . "`  ADD `phone` VARCHAR(100) NULL, ADD `url` VARCHAR(255) NULL, ADD `facebook` VARCHAR(255) NULL, ADD `google` VARCHAR(255) NULL, ADD `twitter` VARCHAR(255) NULL, ADD `linkedin` VARCHAR(255) NULL");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`=CONCAT(`value`, ',fs_authors:phone,fs_authors:url,fs_authors:facebook,fs_authors:google,fs_authors:twitter,fs_authors:linkedin') WHERE `module`='OC' AND `setting`='OC_hideSubFields' LIMIT 1");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_googleAnalytics', '', 'Google Analytics', 'Google Analytics code snippet for tracking access.  Must begin with script tag', 0)");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='5.00' WHERE `setting`='OC_version'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='5.00' WHERE `setting`='OC_versionLatest'");

