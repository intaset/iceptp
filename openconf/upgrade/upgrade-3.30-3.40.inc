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

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='3.40' WHERE `setting`='OC_version'");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_abstractLength', 0, 'Abstract Length', 'Max. number of words permitted in abstract field - use 0 for no limit', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_emailAuthorOnUpload', 0, 'Email Author on Upload', 'Emails the author when a file is uploaded.', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_includeSubmissionUploadField', 0, 'Include File Upload Field', 'Includes file upload field on submission page, overriding Include File Upload Link setting.', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_requireSubmissionUploadField', 0, 'Require File Upload Field', 'Requires file to be uploaded at submission if Include File Upload Field enabled.', 0)");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `description`='Includes file upload link on submission confirmation page if Include File Upload Field is not enabled. Note that Upload File status must also be set to open.' WHERE `module`='OC' AND `setting`='OC_includeSubmissionUploadLink' LIMIT 1");
