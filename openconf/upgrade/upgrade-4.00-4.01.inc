<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='4.01' WHERE `setting`='OC_version'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='4.01' WHERE `setting`='OC_versionLatest'");

ocsql_query("ALTER TABLE `" . OCC_TABLE_AUTHOR . "` CHANGE `name_first` `name_first` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");

ocsql_query("ALTER TABLE `" . OCC_TABLE_REVIEWER . "` CHANGE `name_first` `name_first` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");

ocsql_query("ALTER TABLE `" . OCC_TABLE_PAPER . "` CHANGE `altcontact` `altcontact` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL");