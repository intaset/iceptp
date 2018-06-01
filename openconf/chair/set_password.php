<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

require_once "../include.php";

beginChairSession();

printHeader("Change Password",1);

if (! $OC_configAR['OC_chairChangePassword']) {
  warn('Config settings do not permit ' . OCC_WORD_CHAIR . ' to change password');
}

$e = "";
if (isset($_POST['submit']) && ($_POST['submit'] == "Change Password")) {
	// Check for valid submission
	if (!validToken('chair')) {
		warn('Invalid submission');
	}

	$pwdhash = oc_password_hash($_POST['currpwd']);
	if (empty($_POST['pwd1']) || ($_POST['pwd1'] != $_POST['pwd2'])) {
		$e = '<p align="center"><span class="err">New passwords do not match or are blank.</span></p>';
	} elseif (oc_password_verify($_POST['currpwd'], $OC_configAR['OC_chair_pwd'])) {
		updateConfigSetting('OC_chair_pwd', oc_password_hash($_POST['pwd1'])) or err('Unable to change password');
		print '<p style="text-align: center; font-style: italic" class="warn">Password has been changed.</p>';
		printFooter();
		exit;
	} else {
   		$e = '<p style="text-align: center;" class="err">Current password is incorrect.</p>';
	}
}

if (!empty($e)) {
	print '<p style="text-align: center;" class="warn">' . $e . '</p>';
}

print '
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />
<table border="0" cellspacing="0" cellpadding="5" style="margin: 0 auto">
<tr>
<tr>
<td><b><label for="pwd1">New Password:</label></b></td>
<td><input type="password" name="pwd1" id="pwd1" size=20 maxlength=250></td>
</tr>
<tr>
<td><b><label for="pwd2">Confirm New:</label></b></td>
<td><input type="password" name="pwd2" id="pwd2" size=20 maxlength=250></td>
</tr>
<tr><td colspan="2">&nbsp;</td></tr>
<tr>
<td><b><label for="currpwd">Current Password:</label></b></td>
<td><input type="password" name="currpwd" id="currpwd" size=20 maxlength=250></td>
</tr>
</table>

<p style="text-align: center"><input type="submit" name="submit" value="Change Password"></p>

</form>
';

printFooter();
?>
