<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='3.10' WHERE `setting`='OC_version'");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_notifyAuthorWithdraw', '1', 'Notify Sub. Withdrawn', 'Notify when a submission is withdrawn', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_STATUS . "` (`module`, `setting`, `description`, `status`, `name`, `open`, `close`, `dependency`, `order`) VALUES ('OC', 'OC_withdraw_open', NULL, 0, 'Withdraw Submission', NULL, NULL, NULL, 4)");

ocsql_query("ALTER TABLE `" . OCC_TABLE_WITHDRAWN . "` ADD `withdraw_date` DATETIME NULL, ADD `withdrawn_by` ENUM( 'Author', 'Chair' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` VALUES ('OC', 'OC_mailCopyLast', '0', 'Forward Last Bulk Email', 'Forwards to the Confirm Email address a copy of the last bulk email sent', 0)");
