<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

// Setup constants and hooks
if (!defined('OC_CAPTCHA_public_key') && !defined('OC_CAPTCHA_private_key')) {
	define('OC_CAPTCHA_public_key', $OC_configAR['MOD_CAPTCHA_public_key']);
	define('OC_CAPTCHA_private_key', $OC_configAR['MOD_CAPTCHA_private_key']);

	// Register chair settings hook
	oc_addHook('chair-menu-settings-modules', array($OC_modulesAR[$moduleId]['name'], '<a href="../modules/request.php?module=captcha&action=settings.inc">CAPTCHA</a>'));
	
	// Settings Export/Import
	oc_addHook('settings-export-options', '../modules/captcha/settings-export-options.inc');
	oc_addHook('settings-export-prep', '../modules/captcha/settings-export-prep.inc');
}

// Add to submission / contact if keys set
if ((OC_CAPTCHA_public_key != '') && (OC_CAPTCHA_private_key != '')) {
	if (preg_match("/submit\.php/", $_SERVER['PHP_SELF'])) {
		oc_addHook('author-submit-fields', '../modules/captcha/author-fields.inc');
		oc_addHook('author-submission-validate', '../modules/captcha/author-validate.inc');
	}
	oc_addHook('author-contact-fields', '../modules/captcha/contact-fields.inc');
	oc_addHook('author-contact-validate', '../modules/captcha/contact-validate.inc');
}

