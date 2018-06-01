<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

ocsql_query("ALTER TABLE `" . OCC_TABLE_AUTHOR . "`  ADD `position_title` VARCHAR(255) NULL, ADD `department` VARCHAR(255) NULL");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`=CONCAT(`value`, ',fs_authors:position_title,fs_authors:department') WHERE `module`='OC' AND `setting`='OC_hideSubFields' LIMIT 1");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_chairFailedSignIn', '', 'Chair Failed Sign Ins', 'Tracks failed Chair sign in attempts', 0)");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='4.11' WHERE `setting`='OC_version'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='4.11' WHERE `setting`='OC_versionLatest'");

