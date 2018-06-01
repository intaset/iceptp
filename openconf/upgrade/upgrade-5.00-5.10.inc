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

ocsql_query("ALTER TABLE `" . OCC_TABLE_PAPER . "` CHANGE `student` `student` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL");

ocsql_query("ALTER TABLE `" . OCC_TABLE_AUTHOR . "`  ADD `presenter` VARCHAR(1) NULL, ADD `biography` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`=CONCAT(`value`, ',fs_authors:presenter,fs_authors:biography') WHERE `module`='OC' AND `setting`='OC_hideSubFields' LIMIT 1");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_multipleCommitteeTopics', '1', 'Multiple Cmt. Topics', 'Allow multiple topics to be selected by committee members (1=unlimited, 0=one)', 0)");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='5.10' WHERE `setting`='OC_version'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='5.10' WHERE `setting`='OC_versionLatest'");

