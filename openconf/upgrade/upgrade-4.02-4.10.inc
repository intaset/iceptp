<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$upgradeModulesAR = array('oc_multifile');

ocsql_query("UPDATE `" . OCC_TABLE_PAPER . "` SET `edittime`=NULL, `edittoken`=NULL");

ocsql_query("ALTER TABLE `" . OCC_TABLE_PAPER . "` CHANGE `edittime` `edittime` INT(10) NULL DEFAULT NULL");

ocsql_query("ALTER TABLE `" . OCC_TABLE_AUTHOR . "`  ADD `honorific` VARCHAR(100) NULL, ADD `suffix` VARCHAR(100) NULL, ADD `address` VARCHAR(255) NULL, ADD `address2` VARCHAR(255) NULL, ADD `city` VARCHAR(255) NULL, ADD `spc` VARCHAR(255) NULL, ADD `postcode` VARCHAR(100) NULL");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_homePageNotice', '', 'Home Page Notice', 'Notice appearing atop OpenConf home page', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_hideRevFields', '', 'Hide Rev. Form Fields', 'Comma-delimited list of review form fields to not display (e.g., field1,field2,field3)', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_hideSubFields', 'fs_authors:honorific,fs_authors:suffix,fs_authors:address,fs_authors:address2,fs_authors:city,fs_authors:spc,fs_authors:postcode', 'Hide Sub. Form Fields', 'Comma-delimited list of submission form fields to not display (e.g., fieldset:field,fieldset:field,fieldset:field)', 0)");


ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='4.10' WHERE `setting`='OC_version'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='4.10' WHERE `setting`='OC_versionLatest'");



// Change author/reviewer country names to ISO-3166 2-character codes
require_once OCC_LIB_DIR . 'countries/countries.php'; // base update off original country list
$revCountryAR = array_flip($OC_countryAR);
$revCountryAR['USA'] = 'US';
$revCountryAR['UK'] = 'GB';
$upg_r = ocsql_query("SELECT `paperid`, `position`, `country` FROM `" . OCC_TABLE_AUTHOR . "`") or err('Upgrade complete, but unable to update country names');
while ($upg_l = ocsql_fetch_assoc($upg_r)) {
	if (!empty($upg_l['country']) && isset($revCountryAR[$upg_l['country']])) {
		ocsql_query("UPDATE `" . OCC_TABLE_AUTHOR . "` SET `country`='" . safeSQLstr($revCountryAR[$upg_l['country']]) . "' WHERE `paperid`=" . (int) $upg_l['paperid'] . " AND `position`=" . (int) $upg_l['position']) or err('Upgrade complete, but unable to completely update author country names');
	}
}
$upg_r = ocsql_query("SELECT `reviewerid`, `country` FROM `" . OCC_TABLE_REVIEWER . "`") or err('Upgrade complete, but unable to update reviewer country names');
while ($upg_l = ocsql_fetch_assoc($upg_r)) {
	if (!empty($upg_l['country']) && isset($revCountryAR[$upg_l['country']])) {
		ocsql_query("UPDATE `" . OCC_TABLE_REVIEWER . "` SET `country`='" . safeSQLstr($revCountryAR[$upg_l['country']]) . "' WHERE `reviewerid`=" . (int) $upg_l['reviewerid']) or err('Upgrade complete, but unable to completely update reviewer country names');
	}
}
