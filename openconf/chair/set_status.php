<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$hdr = '';
$hdrfn = 1;

require_once '../include.php';

if (isset($_REQUEST['install']) && ($_REQUEST['install'] == 1)) {
	require_once "install-include.php";
	$token = '';
} else {
	beginChairSession();
	printHeader("Open/Close Status",1);
	$token = $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'];
}

if (isset($_POST['submit']) && ($_POST['submit'] == "Set Status")) {
	// Check for valid submission
	if (OCC_INSTALL_COMPLETE && !validToken('chair')) {
		warn('Invalid submission');
	}

	// Update form's OC_ fields - w/exceptions below requiring special handling
	if ((!isset($_REQUEST['install'])) && isset($_POST['OC_submissions_open']) && ($_POST['OC_submissions_open'] == 1) && ($OC_statusAR['OC_submissions_open'] == 0) && (defined('OCC_LICENSE_EXPIRES')) && (strtotime(OCC_LICENSE_EXPIRES) < time())) {
		unset($_POST['OC_submissions_open']);
		print '<p class="warn">' . base64_decode('TmV3IFN1Ym1pc3Npb25zIG1heSBub3QgYmUgb3BlbmVkIGFzIHRoZSBsaWNlbnNlIGhhcyBleHBpcmVkLiAgRXh0ZW5kIHRoZSBzdXBwb3J0IHBlcmlvZCBvciBwdXJjaGFzZSBhIG5ldyBsaWNlbnNlIGlmIHRoaXMgaXMgYSBuZXcgZXZlbnQu') . '</p>';
	}
	foreach (array_keys($_POST) as $p) {
		if (preg_match("/^[\w-]+/",$p) && isset($OC_statusAR[$p]) && preg_match("/^[01]$/i",$_POST[$p]) && ($OC_statusAR[$p] != $_POST[$p])) {
			updateStatusSetting($p, $_POST[$p]);
			$OC_statusAR[$p] = $_POST[$p];
		}
	}

	// Success - if install, redirect, else let user know
	if (isset($_REQUEST['install']) && ($_REQUEST['install'] == 1)) {
		header("Location: install-complete.php");
		exit;
	} else {
		print '<p style="text-align: center; font-weight: bold;" class="note">Status saved</p>';
	}
}

if (isset($_REQUEST['install']) && ($_REQUEST['install'] == 1)) {
	printHeader($hdr,$hdrfn);
	print '<p style="text-align: center; font-weight: bold;">Step 5 of 5: Open Submissions & Sign-Up/In</p>';
}

$ocq = "SELECT * FROM `" . OCC_TABLE_STATUS . "` WHERE `module`='OC' ORDER BY `order`, `setting`";
$ocr = ocsql_query($ocq) or err('Unable to retrieve status settings');

$nonocq = "SELECT * FROM `" . OCC_TABLE_STATUS . "` WHERE `module`!='OC' ORDER BY `module`, `order`, `setting`";
$nonocr = ocsql_query($nonocq) or err('Unable to retrieve additional status settings');

print '
<form method="post" action="'.$_SERVER['PHP_SELF'].'" class="ocform ocstatusform">
<input type="hidden" name="token" value="' . $token . '" />
';

if (isset($_REQUEST['install']) && ($_REQUEST['install'] == 1)) {
    print '<input type="hidden" name="install" value="1" />';
}

$divnum = 1;
print '
<script>
document.write(\'<p style="margin: 0 0 1em 1em;"><span style="color: #66f; text-decoration: underline; cursor: pointer;" onclick="oc_fsCollapseExpand(0)">collapse all</span> &nbsp; &nbsp; <span style="color: #66f; text-decoration: underline; cursor: pointer;" onclick="oc_fsCollapseExpand(1)">expand all</span></p>\');
</script>
';

if (!isset($_POST['submit'])) {
	print '<p class="note" style="text-align: center;">Make desired changes, then click <i>Set Status</i> button</p>';
}

print '
<fieldset id="oc_fs_' . $divnum . '">
<legend onclick="oc_fsToggle(this)">General <span>(collapse)</span></legend>
<div id="oc_fs_' . $divnum++ . '_div">
';

while ($l = ocsql_fetch_assoc($ocr)) {
	if (!isset($l['dependency']) || empty($l['dependency']) || $OC_configAR[$l['dependency']]) {
		print '<div class="field"><label>' . safeHTMLstr($l['name']) . ':</label><fieldset class="radio">' . generateRadioOptions($l['setting'], $OC_statusValueAR, $l['status']) . '</fieldset><div class="fieldnote note">' . safeHTMLstr($l['description']) . '</div></div>';
	}
}

$module = '';

while ($l = ocsql_fetch_assoc($nonocr)) {
	// skip inactive modules
	if (!oc_moduleActive($l['module'])) {
		continue;
	}
	// show module heading
	if ($module != $l['module']) {
		$module = $l['module'];
		print '<input type="submit" name="submit" value="Set Status" class="submit" /></div></fieldset><fieldset id="oc_fs_' . $divnum . '"><legend onclick="oc_fsToggle(this)">' . safeHTMLstr($OC_modulesAR[$module]['name']) . ' Module <span>(collapse)</span></legend><div id="oc_fs_' . $divnum++ . '_div">';
	}
	if (!isset($l['dependency']) || empty($l['dependency']) || $OC_configAR[$l['dependency']]) {
		print '<div class="field"><label>' . safeHTMLstr($l['name']) . ':</label><fieldset class="radio">' . generateRadioOptions($l['setting'], $OC_statusValueAR, $l['status']) . '</fieldset><div class="fieldnote note">' . safeHTMLstr($l['description']) . '</div></div>';
	}
}

print '
<p><input type="submit" name="submit" value="Set Status" class="submit" /></p>
</div>
<script language="javascript"><!--
'.((OCC_LICENSE!='Public')?('ocsm=new Image();ocsm.src="//openconf.com/images/ocsm.png?l='.urlencode(OCC_LICENSE).'&s='.urlencode(OCC_BASE_URL).'";'):'').'
// --></script>
</fieldset>

</form>
';

printFooter();
?>
