<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ( 'OC', 'OC_reviewerSeeAuthors', '0', 'Allow Rev. See Authors', 'Allow reviewers to see submission authors (i.e., non-blind reviews)', '0')");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ( 'OC', 'OC_advocateSeeAuthors', '0', 'Allow Adv. See Authors?', 'Allow advocates to see submission authors', '0')");

ocsql_query("UPDATE `" . OCC_TABLE_STATUS . "` SET `order`=5 WHERE `setting`='OC_view_file_open' AND `module`='OC'");

ocsql_query("UPDATE `" . OCC_TABLE_STATUS . "` SET `order`=6 WHERE `setting`='OC_rev_signup_open' AND `module`='OC'");

ocsql_query("UPDATE `" . OCC_TABLE_STATUS . "` SET `description`='Modify advanced config option OC_subNonEditFields to change field editing restrictions.' WHERE `setting`='OC_edit_open' AND `module`='OC'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='3.30' WHERE `setting`='OC_version'");
