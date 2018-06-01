<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

require_once "install-include.php";

$uname = varValue('OC_chair_uname', $OC_configAR);
$e = "";
if (isset($_POST['submit']) && ($_POST['submit'] == "Create Account")) {
	$uname = safeHTMLstr(varValue('uname', $_POST));
	// Check if input is valid
	if (!isset($_POST['uname']) || empty($_POST['uname']) || !preg_match("/^\w{5,50}$/",$_POST['uname'])) {
		$e = 'Username needs to be alphanumeric and between 5 and 50 characters';
	}
	elseif (!isset($_POST['pwd1']) || !isset($_POST['pwd2']) || empty($_POST['pwd1']) || ($_POST['pwd1'] != $_POST['pwd2'])) {
		$e = 'Passwords do not match or are blank';
	}
	else {
		updateConfigSetting('OC_chair_uname', $_POST['uname'], 'OC') or err('Unable to save username', $hdr, $hdrfn);
		updateConfigSetting('OC_chair_pwd', oc_password_hash(stripslashes($_POST['pwd1'])), 'OC') or err('Unable to save password', $hdr, $hdrfn);
		header("Location: set_config.php?install=1");
	}
}

printHeader($hdr,$hdrfn);

print '<p style="text-align: center; font-weight: bold">Step 2 of 5: Create ' . OCC_WORD_CHAIR . ' Account</p>';

if (!empty($e)) {
	print '<p style="text-align: center" class="warn">' . $e . '</p>';
}

print '
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<table border="0" cellspacing="0" cellpadding="5" style="margin: 0 auto">
<tr>
<td><strong><label for="uname">Username:</label></strong></td>
<td><input name="uname" id="uname" value="' . safeHTMLstr($uname) . '" size=20 maxlength=250></td>
</tr>
<tr><td colspan=2>&nbsp;</td></tr>
<tr>
<td><strong><label for="pwd1">Password:</label></strong></td>
<td><input type="password" name="pwd1" id="pwd1" size=20 maxlength=250></td>
</tr>
<tr>
<td><strong><label for="pwd2">Confirm <span title="Password">Pwd</span>:</label></strong></td>
<td><input type="password" name="pwd2" id="pwd2" size=20 maxlength=250></td>
</tr>
<tr><td>&nbsp;</td><td style="padding-top: 1.5em"><input type="submit" name="submit" value="Create Account"></td></tr>
</table>

</form>

';

printFooter();
?>
