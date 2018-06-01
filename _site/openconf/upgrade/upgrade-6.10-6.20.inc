<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$upgradeModulesAR = array('oc_program', 'oc_ieee_copyright');

ocsql_query("ALTER TABLE `" . OCC_TABLE_TEMPLATE . "` ADD `updated` DATE NULL, CHANGE `body` `body` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='6.20' WHERE `setting`='OC_version'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='6.20' WHERE `setting`='OC_versionLatest'");
