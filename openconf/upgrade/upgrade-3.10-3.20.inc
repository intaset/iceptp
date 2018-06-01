<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$upgradeModulesAR = array('oc_discussion');

ocsql_query("ALTER TABLE `" . OCC_TABLE_LOG . "` ADD `type` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'sql',
ADD `extra` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL");

ocsql_query("ALTER TABLE `" . OCC_TABLE_LOG . "` ADD INDEX (`type`)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_reviewerSeeOtherReviewers', '1', 'Allow Rev. See Other Rev. Info', 'Allow reviewers to see each other''s information', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES('OC', 'OC_includeSubmissionUploadLink', '1', 'Include Upload Link', 'Include a file upload link on the submission confirmation page.', 0)");

ocsql_query("DELETE FROM `" . OCC_TABLE_CONFIG . "` WHERE `setting`='OC_maxTopics' AND `module`='OC'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='3.20' WHERE `setting`='OC_version'");
