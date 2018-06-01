<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$MOD_CAPTCHA_configVars = array('MOD_CAPTCHA_version', 'MOD_CAPTCHA_private_key', 'MOD_CAPTCHA_public_key');

$MOD_CAPTCHA_versionAR = array('1.0', '2.0');

if (isset($_POST['submit']) && ($_POST['submit'] == 'Save Settings')) {
	// Check for valid submission
	if (!validToken('chair')) {
		warn('Invalid submission');
	}

	// Validate settings
	$err = array();
	if (!isset($_POST['MOD_CAPTCHA_version']) || !in_array($_POST['MOD_CAPTCHA_version'], $MOD_CAPTCHA_versionAR)) {
		$err[] = 'Version number invalid';
	} elseif (($_POST['MOD_CAPTCHA_version'] == '2.0') && !ini_get('allow_url_fopen')) {
		$err[] = 'Version 2.0 requires PHP have allow_url_fopen enabled';
	}
	
	// Errors?
	if (!empty($err)) {
		print '<p class="warn">Please re-enter your settings, noting the following:<ul><li>' . implode('</li><li>', $err) . '</li></ul></p><hr /><p />';
	} else { // Update settings
		updateAllConfigSettings($MOD_CAPTCHA_configVars, $_POST, OCC_MODULE_ID);
	}

	// notify user		
	print '<p style="text-align: center" class="note">Settings Saved</p>';
}

print '
<form method="post" action="' . OCC_SELF . '">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />

<p>OpenConf uses the reCAPTCHA system for verifying submissions.  You must sign up for a <a href="http://www.google.com/recaptcha" target="_blank">reCAPTCHA account</a>, and enter your public/private keys below.</p>

<p><strong>API Version:</strong> ' . generateRadioOptions('MOD_CAPTCHA_version', $MOD_CAPTCHA_versionAR, $OC_configAR['MOD_CAPTCHA_version'], 0) . '</p>

<p><strong>Public key:</strong> <input name="MOD_CAPTCHA_public_key" size="60" value="' . safeHTMLstr($OC_configAR['MOD_CAPTCHA_public_key']) . '" /></p>

<p><strong>Private key:</strong> <input name="MOD_CAPTCHA_private_key" size="60" value="' . safeHTMLstr($OC_configAR['MOD_CAPTCHA_private_key']) . '" /></p>

<br />
<p><input type="submit" name="submit" value="Save Settings" /></p>
</form>
';
