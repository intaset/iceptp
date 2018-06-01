<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$upgradeModulesAR = array('filetype', 'oc_rebuttal');

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='4.00' WHERE `setting`='OC_version'");

ocsql_query("UPDATE `" . OCC_TABLE_CONFIG . "` SET `value`='4.00' WHERE `setting`='OC_versionLatest'");

ocsql_query("ALTER TABLE `" . OCC_TABLE_PAPER . "` ADD `edittoken` VARCHAR(100) NULL, ADD `edittime` DATETIME NULL");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_headerImage', '', 'Header Image', 'Full web address for an image to be displayed in place of conference name atop pages', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "`  (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_requiredField', '', 'Required Field', 'Required field designator - experimental', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_topicDisplayAlpha', '0', 'Display Topics Alphabetically', 'Display topics on submission and committee sign up forms alphabetically', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_notifyReviewerProfileUpdate', '0', 'Notify Rev. Profile Update', 'Notify when a committee member updates profile', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_authorsMinDisplay', '3', 'Min. Authors', 'Minimum number of authors to display on submission form', '0')");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_authorsMax', '9', 'Max. Authors', 'Maximum number of authors allowed per submission', '0')");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_localeDefault', 'en', 'Default Locale', 'Default locale for initial display', 0)");

ocsql_query("INSERT INTO `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_locales', 'en', 'Available Locales', 'Locales available for user to choose between', 0)");

ocsql_query("DELETE `" . OCC_TABLE_CONFIG . "` WHERE `module`='OC' AND `setting`= 'OC_topicColumns'");

ocsql_query("ALTER TABLE `" . OCC_TABLE_PAPER . "` CHANGE `contactemail` `contactemail` VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT ''");

ocsql_query("ALTER TABLE `" . OCC_TABLE_PAPER . "` ADD `altcontact` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `contactid`");

ocsql_query("ALTER TABLE `" . OCC_TABLE_PAPER . "` CHANGE `student` `student` ENUM( 'T', 'F' ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL");

ocsql_query("UPDATE `" . OCC_TABLE_PAPER . "` SET `contactemail`=NULL WHERE `contactemail`=''");

ocsql_query("UPDATE `" . OCC_TABLE_PAPER . "` SET `contactphone`=NULL WHERE `contactphone`=''");

ocsql_query("UPDATE `" . OCC_TABLE_PAPER . "` SET `altcontact`=CONCAT_WS(', ', `contactemail`, `contactphone`)");

ocsql_query("UPDATE `" . OCC_TABLE_STATUS . "` SET `description` = 'When open, lets author check acceptance status and, if not pending, view reviewer comments' WHERE `setting` = 'OC_status_open'");

if (is_file('../review/signup_reviewer.html')) {
	$text = safeSQLstr(preg_replace("/\r?\n/", "\\n", file_get_contents('../review/signup_reviewer.html')));
} else {
	$text = 'Thank you for agreeing to be a reviewer. The Review Committee is a key part of the conference organization. Its role is to review and comment on submissions, thus providing the input to the the Program Committee which makes the final decision on which submissions are accepted and rejected.\n\n<strong>Note:</strong> Members of the Review Committee see unpublished work of other authors. Your professional ethics preclude disclosure to any other party the contents of the submissions you read.';
}
ocsql_query("DELETE `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_reviewerSignUpNotice', '" . $text . "', 'Rev. Sign Up Notice', 'Notice to appear atop reviewer sign up page', 0)");

if (is_file('../review/signup_program.html')) {
	$text = safeSQLstr(preg_replace("/\r?\n/", "\\n", file_get_contents('../review/signup_program.html')));
} else {
	$text = 'Thank you for agreeing to be a program committee (PC) member. As a PC member, you will have a say on what submissions are included in the conference program and be an advocate (champion) for a set of submissions. You will be provided with all the reviews for submissions you are an advocate for, and if there is a lack of agreement from the reviewers, you will be expected to read the submission and make a recommendation.\n\n<strong>Note:</strong> Members of the Program Committee see unpublished work of other authors. Your professional ethics preclude disclosure to any other party the contents of the submissions you read, or the reviews of those submissions.';
}
ocsql_query("DELETE `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_programSignUpNotice', $text, 'Adv. Sign Up Notice', 'Notice to appear atop program committee sign up page', 0)");

if (is_file('../review/reviewer_info.html')) {
	$text = safeSQLstr(preg_replace("/\r?\n/", "\\n", file_get_contents('../review/reviewer_info.html')));
} else {
	$text = '<strong>Reminder:</strong> By acting as a reviewer, you are seeing unpublished works created by others.  Your professional ethics require that you do not distribute these, or discuss their contents with anyone other than fellow reviewers.\n\n<strong>Note:</strong> <em>If you will not be able to review all your submissions, please notify us as soon as possible so we can assign additional reviewers.</em>  It is unfair to authors and your fellow reviewers if the reviews are not provided.\n\nIf you have a colleague who would be a good reviewer for a submission, please email their contact information to the Chair.';
}
ocsql_query("DELETE `" . OCC_TABLE_CONFIG . "` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('OC', 'OC_committeeFooter', $text, 'Rev. Footer', 'Notice to appear at the bottom of the main committee page', 0)");
