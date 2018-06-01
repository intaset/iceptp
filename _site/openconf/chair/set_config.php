<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

// Update any settings added/deleted in $settingsAR below

$hdr = '';
$hdrfn = 1;

require_once '../include.php';
require_once OCC_ZONE_FILE;

require_once OCC_PLUGINS_DIR . 'ckeditor.inc';

$OC_extraHeaderAR[] = '
<script language="javascript" type="text/javascript">
<!--
function oc_showHideDiv(fldName, divID) {
	if (document.getElementById) {
		if (document.getElementById(fldName).checked) {
			document.getElementById(divID).style.display="block";
		} else {
			document.getElementById(divID).style.display="none";
		}
	}
}
// -->
</script>
';

if (!OCC_INSTALL_COMPLETE && isset($_REQUEST['install']) && ($_REQUEST['install'] == 1)) {
	require_once "install-include.php";
	$token = '';
} else {
	beginChairSession();
	printHeader("Configuration", 1);
	$token = $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'];
	// Display active modules config
	if (isset($OC_activeModulesAR) && !empty($OC_activeModulesAR)) {
		$modules = array();
		foreach ($OC_activeModulesAR as $module) {
			if (is_file('../modules/' . $module . '/settings.inc')) {
				$modules[$module] = $OC_modulesAR[$module]['name'];
			}
		}
		asort($modules);
		$moduleOptions = '';
		foreach ($modules as $mid => $mname) {
			$moduleOptions .= '<option value="' . $mid . '">' . safeHTMLstr($mname) . '</option>';
		}
		if (!empty($moduleOptions)) {
			print '
<form method="get" action="../modules/request.php">
<p style="text-align: center">
<input type="hidden" name="action" value="settings.inc" />
Config Module: <select name="module">
<option value=""></option>
' . $moduleOptions . '
</select>
<input type="submit" value="Go" />
</p>
</form>
';
		}
	}
}

// YesNo fields
$yesNoFieldsAR = array('OC_notifyIncludeIP', 'OC_reviewerReadPapers', 'OC_reviewerSeeAssignedReviews', 'OC_reviewerCompleteBeforeSAR', 'OC_reviewerSeeOtherReviews', 'OC_reviewerSeeOtherReviewers', 'OC_reviewerSeeAuthors', 'OC_reviewerUnassignReviews', 'OC_advocateReadPapers', 'OC_advocateSeeOtherReviews', 'OC_advocateSeeAuthors', 'OC_paperAdvocates', 'OC_editAcceptedOnly', 'OC_authorOneContact');

// Notification array
$notifyAR = array(
	'OC_notifyAuthorSubmit'				=> OCC_WORD_AUTHOR . ' makes a submission',
	'OC_notifyAuthorEdit'				=> OCC_WORD_AUTHOR . ' updates (edits) submission',
	'OC_notifyAuthorEmailPapers'		=> OCC_WORD_AUTHOR . ' requests own submission list emailed',
	'OC_notifyAuthorUpload'				=> OCC_WORD_AUTHOR . ' uploads a file',
	'OC_notifyAuthorReset'				=> OCC_WORD_AUTHOR . ' requests password reset',
	'OC_notifyAuthorWithdraw'			=> OCC_WORD_AUTHOR . ' withdraws submission',
	'OC_notifyReviewerSignup'			=> 'Committee member signs up for account',
	'OC_notifyReviewerProfileUpdate'	=> 'Committee member updates profile',
	'OC_notifyReviewerReset'			=> 'Committee member resets password',
	'OC_notifyReviewerEmailUsername' 	=> 'Committee member requests username emailed'
);

$emailAuthorRecipientsAR = array(
	1 => 'All',
	0 => 'Contact Only'
);

// Fields that may be updated through this form
$settingsAR = array_merge($yesNoFieldsAR, array_keys($notifyAR), array(
	'OC_confNameFull', 'OC_confName', 'OC_confURL', 'OC_headerImage', 'OC_homePageNotice', 'OC_pcemail', 'OC_confirmmail', 'OC_keycode_reviewer', 'OC_reviewerSignUpNotice', 'OC_committeeFooter', 'OC_keycode_program', 'OC_programSignUpNotice', 'startid', 'OC_paperSubNote', 'OC_subConfirmNotice', 'OC_authorsMinDisplay', 'OC_authorsMax', 'OC_emailAuthorRecipients', 'OC_extar', 'OC_locales', 'OC_localeDefault', 'OC_timeZone'
));

// Allow submission start ID to change?
if (($air = ocsql_query("SELECT COUNT(`paperid`) AS `count` FROM `" . OCC_TABLE_PAPER . "`")) && ($ail = ocsql_fetch_assoc($air)) && ($ail['count'] == 0) 
	&& ($air = ocsql_query("SHOW TABLE STATUS WHERE `name`='" . OCC_TABLE_PAPER . "'")) && (ocsql_num_rows($air) == 1) && ($ail = ocsql_fetch_assoc($air)) 
) {
	$startid = $ail['Auto_increment'];
}

// Submission?
$e = array();
if (isset($_POST['submit']) && ($_POST['submit'] == "Save Settings")) {
	// Check for valid submission
	if (OCC_INSTALL_COMPLETE && !validToken('chair')) {
		warn('Invalid submission', $hdr, $hdrfn);
	}
	// Check input
	if (!isset($_POST['OC_confName']) || !preg_match("/\p{L}/u", $_POST['OC_confName'])) {
		$e[] = 'Event Short Name must include at least one alphanumeric character';
	}
	if (!isset($_POST['OC_confNameFull']) || !preg_match("/\p{L}/u", $_POST['OC_confNameFull'])) {
		$e[] = 'Event Full Name must include at least one alphanumeric character';
	}
	if (isset($_POST['OC_confURL']) && !empty($_POST['OC_confURL']) && !preg_match("/^(?:https?:\/\/|\/)/i", $_POST['OC_confURL'])) {
		$e[] = 'Event Web Address should start with http:// or https:// (if on another server) or / (for local server)';
	}
	if (isset($_POST['OC_headerImage']) && !empty($_POST['OC_headerImage']) && !preg_match("/^(?:https?:\/\/|\/)/i", $_POST['OC_headerImage'])) {
		$e[] = 'Header Image should start with http:// or https:// (if on another server) or / (if on local server)';
	}
	if (!isset($_POST['OC_pcemail'])) {
		$e[] = OCC_WORD_CHAIR . ' Email address invalid.';
	} elseif (preg_match("/,/", $_POST['OC_pcemail'])) {   // Multiple addresses
		$cmAR = explode(",", $_POST['OC_pcemail']);
		foreach ($cmAR as $cm) {
			$cm = trim($cm);
			if (!validEmail($cm)) {
				$e[] = OCC_WORD_CHAIR . ' Email does not appear to be valid';
				break;
	 		}
    	}
	} elseif (!validEmail($_POST['OC_pcemail'])) {  // Single address
		$e[] = OCC_WORD_CHAIR . ' Email does not appear to be valid';
	}
	if (!isset($_POST['OC_confirmmail'])) {
		$e[] = 'Notification Email address invalid.  Try setting it the same as the ' . OCC_WORD_CHAIR . ' Email';
	} elseif (preg_match("/,/", $_POST['OC_confirmmail'])) {   // Multiple addresses
		$cmAR = explode(",", $_POST['OC_confirmmail']);
		foreach ($cmAR as $cm) {
			$cm = trim($cm);
			if (!validEmail($cm)) {
				$e[] = 'Notification Email does not appear to be valid';
				break;
	 		}
    	}
	} elseif (!validEmail($_POST['OC_confirmmail'])) {  // Single address
		$e[] = 'Notification Email does not appear to be valid';
	}
	if (isset($_POST['startid']) && ! preg_match("/^[1-9]\d*$/", $_POST['startid'])) {
		$e[] = 'Submission Starting ID invalid';
	}
	if (isset($_POST['OC_authorsMinDisplay'])) {
		if (! preg_match("/^[1-9][0-9]?$/", $_POST['OC_authorsMinDisplay'])) {
			$e[] = 'Min Authors to Display must be a number between 1-99';
		} elseif (isset($_POST['OC_authorsMax']) && ($_POST['OC_authorsMinDisplay'] > $_POST['OC_authorsMax'] )) {
			$e[] = 'Min Authors must be less than or equal to the Max Authors Allowed value';
		}
	}
	if (isset($_POST['OC_authorsMax']) && ! preg_match("/^[1-9][0-9]?$/", $_POST['OC_authorsMax'])) {
		$e[] = 'Max Authors Allowed must be a number between 1-99';
	}
	if (!isset($_POST['OC_extar']) || count($_POST['OC_extar']) == 0) {
		$e[] = 'Select at least one file format';
	} else { // check formats are valid
		foreach ($_POST['OC_extar'] as $fmat) {
			if (!isset($OC_formatAR[$fmat])) {
				$e[] = 'Invalid format selected';
				continue;
			}
		}
	}

	$notifyKeysAR = array_keys($notifyAR);
	foreach ($notifyKeysAR as $nk) {
		if (!isset($_POST[$nk])) {
			$_POST[$nk] = 0;
		} elseif (!preg_match("/^[01]$/", $_POST[$nk])) {
			$e[] = 'Invalid notification selection';
			continue;
		}
	}
	foreach ($yesNoFieldsAR as $ynf) {
		if (!isset($_POST[$ynf]) || !isset($yesNoAR[$_POST[$ynf]])) {
			$e[] = 'Invalid option (' . safeHTMLstr($ynf) . ')';
		}
	}
	if (!isset($_POST['OC_timeZone']) || !in_array($_POST['OC_timeZone'], $OC_zoneAR)) {
		$e[] = 'Time Zone is invalid';
	}
	if (!isset($_POST['OC_locales']) || empty($_POST['OC_locales']) || !is_array($_POST['OC_locales'])) {
		$e[] = 'At least one language must be selected';
		$_POST['OC_locales'] = array();
	} else {
		if (!isset($_POST['OC_localeDefault']) || !isset($OC_languageAR[$_POST['OC_localeDefault']])) {
			$e[] = 'Default Language is invalid';
		} elseif (!in_array($_POST['OC_localeDefault'], $_POST['OC_locales'])) {
			$_POST['OC_locales'][] = $_POST['OC_localeDefault'];	// auto-select default language
		}
		foreach ($_POST['OC_locales'] as $locale) {
			if (!isset($OC_languageAR[$locale])) {
				$e[] = 'Invalid locale selected: ' . safeHTMLstr($locale);
			}
		}
	}
	if (empty($e)) {
		// Update form's OC_ fields
		$_POST['OC_extar'] = implode(',', $_POST['OC_extar']);
		$_POST['OC_locales'] = implode(',', $_POST['OC_locales']);
		foreach (array_keys($_POST) as $p) {
			if (preg_match("/^OC_[\w-]+$/", $p) && in_array($p, $settingsAR) && isset($OC_configAR[$p]) && ($OC_configAR[$p] != $_POST[$p])) {
				updateConfigSetting($p, $_POST[$p], 'OC');
				$OC_configAR[$p] = $_POST[$p];
			}
		}
		
		// Auto increment?
		if (isset($startid) && ($startid != $_POST['startid'])) {
			ocsql_query("ALTER TABLE `" . OCC_TABLE_PAPER . "` AUTO_INCREMENT=" . (int) $_POST['startid']);
		}
		
		// if install, redirect
		if (isset($_REQUEST['install']) && ($_REQUEST['install'] == 1)) {
			header("Location: set_topics.php?install=1");
			exit;
		}
		
		print '<p class="note" style="font-weight: bold; text-align: center;">Configuration successfully updated</p>';
		
		// reset special vars to array
		$_POST['OC_extar'] = explode(',', $OC_configAR['OC_extar']);
		$_POST['OC_locales'] = explode(',', $OC_configAR['OC_locales']);
	}
} else { // not submit; init POST with config values
	$_POST = $OC_configAR;
	$_POST['OC_locales'] = explode(',', $OC_configAR['OC_locales']);
	// Allow submission auto increment to be set?
	if (isset($startid)) {
		$_POST['startid'] = $startid;
	}
}

if ((!OCC_INSTALL_COMPLETE) && isset($_REQUEST['install']) && ($_REQUEST['install'] == 1)) {
	printHeader($hdr,$hdrfn);
	print '<p align="center"><strong>Step 3 of 5: Tailor Configuration Settings</strong></p>';
}

if (!empty($e)) {
	print '<div class="warn">Please correct the following:<br /><ul><li>' . implode('</li><li>',$e) . '</li></ul></div>';
}

if (!preg_match("/^OpenConf/", $OC_configAR['OC_confNameFull']) && (OCC_LICENSE != 'Public')) {
	$checkName = true;
	print '
<script>
function oc_checkName(newName) {
	alert("Please purchase a new license if this is not ' . safeHTMLstr(OCC_LICENSE_EVENT) . '");
}
</script>
';
} else {
	$checkName = false;
}

print '
<form method="post" action="'.$_SERVER['PHP_SELF'].'" class="ocform occonfigform">
<input type="hidden" name="token" value="' . $token . '" />
';

if ((!OCC_INSTALL_COMPLETE) && isset($_REQUEST['install'])) {
    print '
<input type="hidden" name="install" value="' . safeHTMLstr($_REQUEST['install']) . '" />
';
}

print '
<script>
document.write(\'<p style="margin: 0 0 1em 1em;"><span style="color: #66f; text-decoration: underline; cursor: pointer;" onclick="oc_fsCollapseExpand(0)">collapse all</span> &nbsp; &nbsp; <span style="color: #66f; text-decoration: underline; cursor: pointer;" onclick="oc_fsCollapseExpand(1)">expand all</span></p>\');

function oc_sampleTextUpdate(id) {
	var sampleTextAR = {
		"OC_reviewerSignUpNotice": "<p>Thank you for agreeing to be a reviewer. The Review Committee is a key part of the conference organization. Its role is to review and comment on submissions, thus providing the input to the the Program Committee which makes the final decision on which submissions are accepted and rejected.</p>\n\n<p><strong>Note:</strong> Members of the Review Committee see unpublished work of other authors. Your professional ethics preclude disclosure to any other party the contents of the submissions you read.</p>",
		"OC_committeeFooter": "<p><strong>Reminder:</strong> By acting as a reviewer, you are seeing unpublished works created by others. Your professional ethics require that you do not distribute these, or discuss their contents with anyone other than fellow reviewers.</p>\n\n<p><strong>Note:</strong> <em>If you will not be able to review all your submissions, please notify us as soon as possible so we can assign additional reviewers.</em> It is unfair to authors and your fellow reviewers if the reviews are not provided. If you have a colleague who would be a good reviewer for a submission, please email their contact information to the Program Chair.</p>",
		"OC_programSignUpNotice": "<p>Thank you for agreeing to be a program committee (PC) member. As a PC member, you will have a say on what submissions are included in the conference program and be an advocate (champion) for a set of submissions. You will be provided with all the reviews for submissions you are an advocate for, and if there is a lack of agreement from the reviewers, you will be expected to read the submission and make a recommendation.</p>\n\n<p><strong>Note:</strong> Members of the Program Committee see unpublished work of other authors. Your professional ethics preclude disclosure to any other party the contents of the submissions you read, or the reviews of those submissions.</p>",
		"OC_paperSubNote": "<p>Please review the entire form before starting to fill it out to ensure you have all the required information.</p>"
	};

	if (typeof CKEDITOR !== "undefined"){
		CKEDITOR.instances[id].setData(sampleTextAR[id]);
	} else {
		document.getElementById(id).value = sampleTextAR[id];
	}
}
</script>
';

if (!isset($_POST['submit'])) {
	print '<p class="note" style="text-align: center;">Make desired changes, then click <i>Save Settings</i> button</p>';
}

print '
<fieldset id="oc_fs_event" role="header">
<legend onclick="oc_fsToggle(this)">Event <span>(collapse)</span></legend>
<div id="oc_fs_event_div">
';

if ((OCC_LICENSE != 'Public') && defined('OCC_LICENSE_EVENT') && !defined('OCHS')) {
	print '
<div class="field"><label>Licensed Entity:</label><b>' . safeHTMLstr(OCC_LICENSE_EVENT) . '</b><div class="fieldnote note">For an entity or year other than the one listed above, please purchase and install a new <a href="https://www.openconf.com/sales/" target="_blank">OpenConf license</a>.<br />Each license permits a single installation of the OpenConf software.</div></div>
';
}

print '
<div class="field"><label for="OC_confNameFull">Event Full Name:</label><input size="60" name="OC_confNameFull" id="OC_confNameFull" value="' . safeHTMLstr($_POST['OC_confNameFull']) . '" ' . ($checkName ? 'onchange="oc_checkName(this.value)" ' : '') . '/><div class="fieldnote note">Event name used on Web pages and in email messages</div></div>
<div class="field"><label for="OC_confName">Event Short Name:</label><input size="60" name="OC_confName" id="OC_confName" value="' . safeHTMLstr($_POST['OC_confName']) . '"><div class="fieldnote note">Event abbreviated name, primarily used in email subject lines</div></div>
<div class="field"><label for="OC_confURL">Event Web Address:</label><input size="60" name="OC_confURL" id="OC_confURL" value="' . safeHTMLstr($_POST['OC_confURL']) . '"><div class="fieldnote note">Full web address (including http:// ) for event web page</div></div>
<div class="field"><label for="OC_headerImage">Header Image:</label><input size="60" name="OC_headerImage" id="OC_headerImage" value="' . safeHTMLstr($_POST['OC_headerImage']) . '"><div class="fieldnote note">Full web address (including http:// ) for image to display atop every page.  Leave blank to display event full name</div></div>
<div class="field"><label for="OC_homePageNotice">Home Page Notice:<br /><br /><span class="note">Optional notice atop<br />OpenConf home page</span></label><textarea name="OC_homePageNotice" id="OC_homePageNotice" rows="6" cols="70">' . safeHTMLstr($_POST['OC_homePageNotice']) . '</textarea></div>

<input type="submit" name="submit" value="Save Settings" class="submit" />
</div>
</fieldset>

<fieldset id="oc_fs_notification" role="header">
<legend onclick="oc_fsToggle(this)">Chair Email &amp; Notification <span>(collapse)</span></legend>
<div id="oc_fs_notification_div">
<div class="field"><label for="OC_pcemail">' . OCC_WORD_CHAIR . ' Email:</strong></label><input size="50" name="OC_pcemail" id="OC_pcemail" value="' . safeHTMLstr($_POST['OC_pcemail']) . '" onchange="oc_checkEmail(this.value)"><div class="fieldnote note">Used for the From header of outgoing messages, as the general contact email address, and in case of errors or other follow-up.  Although a comma-delimited list of addresses (without spaces) is permitted, this is not recommended as mail servers may reject messages with more than one.</div></div>
<div class="field"><label for="OC_confirmmail">Notification Email:</label><input size="50" name="OC_confirmmail" id="OC_confirmmail" value="' . safeHTMLstr($_POST['OC_confirmmail']) . '"><div class="fieldnote note">Receives a copy of confirmation emails sent to ' . oc_strtolower(OCC_WORD_AUTHOR) . 's and committee members; see options below. A comma-delimited list of addresses (without spaces) is permitted.</div></div>

<label>Notify when:</label>
<div class="subfieldset"><fieldset class="checkbox">
';

foreach ($notifyAR as $nk => $nv) {
	print '<label><input type="checkbox" name="' . $nk . '" id="' . $nk . '" value="1" ';
	if ($_POST[$nk] == 1) { print 'checked '; }
	print '/> ' . safeHTMLstr($nv) . '</label><br />';
}

print '
</fieldset>
<p />
Include IP address in notifications?<fieldset class="radio">' . generateRadioOptions('OC_notifyIncludeIP', $yesNoAR, $_POST['OC_notifyIncludeIP']) . '</fieldset>
</div>

<input type="submit" name="submit" value="Save Settings" class="submit" />
</div>
</fieldset>

<fieldset id="oc_fs_reviewers" role="header">
<legend onclick="oc_fsToggle(this)">Reviewers <span>(collapse)</span></legend>
<div id="oc_fs_reviewers_div">
<div class="field"><label for="OC_keycode_reviewer">Sign Up Keycode:</label><input size="20" name="OC_keycode_reviewer" id="OC_keycode_reviewer" value="' . safeHTMLstr($_POST['OC_keycode_reviewer']) . '"><div class="fieldnote note">Keycode for signing up as a review committee member.  May enter a comma-delimited list (no spaces).</div></div>
<div class="field"><label for="OC_reviewerSignUpNotice">Sign Up Notice:<br /><br /><span class="note">Optional notice atop<br />reviewer sign up page<br /><br />(<span style="color: #009; text-decoration: underline;" onclick="oc_sampleTextUpdate(\'OC_reviewerSignUpNotice\')" title="overwrites field with sample text">sample text</span>)</span></label><textarea name="OC_reviewerSignUpNotice" id="OC_reviewerSignUpNotice" rows="6" cols="70">' . safeHTMLstr($_POST['OC_reviewerSignUpNotice']) . '</textarea></div>

<div class="field"><label for="OC_committeeFooter">Committee Page Notice:<br /><br /><span class="note">Optional notice at bottom of<br />the main committee page<br /><br />(<span style="color: #009; text-decoration: underline;" onclick="oc_sampleTextUpdate(\'OC_committeeFooter\')" title="overwrites field with sample text">sample text</span>)</span></label><textarea name="OC_committeeFooter" id="OC_committeeFooter" rows="6" cols="70">' . safeHTMLstr($_POST['OC_committeeFooter']) . '</textarea></div>

<div class="field"><label>Reviewer Permissions:</label>
<div class="subfieldset">
<div class="field">Allow reviewers to view all submissions?<fieldset class="radio">' . generateRadioOptions('OC_reviewerReadPapers', $yesNoAR, $_POST['OC_reviewerReadPapers']) . '</fieldset></div>
<div class="field">Allow reviewers to see others\' review of assigned submissions?<fieldset class="radio">' .generateRadioOptions('OC_reviewerSeeAssignedReviews', $yesNoAR, $_POST['OC_reviewerSeeAssignedReviews']) . '</fieldset></div>
<div class="field">Only allow reviewer to see reviews of assigned submissions if own is complete?<fieldset class="radio">' . generateRadioOptions('OC_reviewerCompleteBeforeSAR', $yesNoAR, $_POST['OC_reviewerCompleteBeforeSAR']) . '</fieldset></div>
<div class="field">Allow reviewer to see reviews of non-assigned submissions?<fieldset class="radio">' . generateRadioOptions('OC_reviewerSeeOtherReviews', $yesNoAR, $_POST['OC_reviewerSeeOtherReviews']) . '</fieldset></div>
<div class="field">Allow reviewers to see each other\'s information? (e.g., name, email)<fieldset class="radio">' . generateRadioOptions('OC_reviewerSeeOtherReviewers', $yesNoAR, $_POST['OC_reviewerSeeOtherReviewers']) . '</fieldset></div>
<div class="field">Allow reviewers to see ' . oc_strtolower(OCC_WORD_AUTHOR) . 's (i.e., non-blind reviews)?<fieldset class="radio">' . generateRadioOptions('OC_reviewerSeeAuthors', $yesNoAR, $_POST['OC_reviewerSeeAuthors']) . '</fieldset></div>
<div class="field">Allow reviewers to unassign (own) reviews, deleting all review data?<fieldset class="radio">' . generateRadioOptions('OC_reviewerUnassignReviews', $yesNoAR, $_POST['OC_reviewerUnassignReviews']) . '</fieldset></div>
</div>
</div>

<input type="submit" name="submit" value="Save Settings" class="submit" />
</div>
</fieldset>

<fieldset id="oc_fs_advocates" role="header">
<legend onclick="oc_fsToggle(this)">Advocates (Program Committee) <span>(collapse)</span></legend>
<div id="oc_fs_advocates_div">
<div class="field"><label for="OC_paperAdvocates">Use Advocates?</label><fieldset class="radio">' . generateRadioOptions('OC_paperAdvocates', $yesNoAR, $_POST['OC_paperAdvocates'], 1, 'onclick="oc_showHideDiv(\'OC_paperAdvocates1\', \'advocates\')"') . '</fieldset><div class="fieldnote note">Select whether to make use of Advocates</div></div>

<div id="advocates">
<div class="field"><label for="OC_keycode_program">Sign Up Keycode:</label><input size="20" name="OC_keycode_program" id="OC_keycode_program" value="' . safeHTMLstr($_POST['OC_keycode_program']) . '"><div class="fieldnote note">Keycode for signing up as a program committee member.  May enter a comma-delimited list (no spaces).</div></div>
<div class="field"><label for="OC_programSignUpNotice">Sign Up Notice:<br /><br /><span class="note">Optional notice atop<br />advocate sign up page<br /><br />(<span style="color: #009; text-decoration: underline;" onclick="oc_sampleTextUpdate(\'OC_programSignUpNotice\')" title="overwrites field with sample text">sample text</span>)</span></label><textarea name="OC_programSignUpNotice" id="OC_programSignUpNotice" rows="6" cols="70">' . safeHTMLstr($_POST['OC_programSignUpNotice']) . '</textarea></div>
<div class="field"><label>Advocate Permissions:</label>
<div class="subfieldset">
<div class="field">Allow advocates to view all submissions?<fieldset class="radio">' . generateRadioOptions('OC_advocateReadPapers', $yesNoAR, $_POST['OC_advocateReadPapers']) . '</fieldset></div>
<div class="field">Allow advocates to see reviews of non-assigned submissions?<fieldset class="radio">' . generateRadioOptions('OC_advocateSeeOtherReviews', $yesNoAR, $_POST['OC_advocateSeeOtherReviews']) . '</fieldset></div>
<div class="field">Allow advocates to see ' . oc_strtolower(OCC_WORD_AUTHOR) . 's?<fieldset class="radio">' . generateRadioOptions('OC_advocateSeeAuthors', $yesNoAR, $_POST['OC_advocateSeeAuthors']) . '</fieldset></div>
<div class="note">NOTE: Reviewer permissions are evaluated before advocate\'s</div>
</div>
</div>

<input type="submit" name="submit" value="Save Settings" class="submit" />
</div>
</fieldset>


<fieldset id="oc_fs_submission" role="header">
<legend onclick="oc_fsToggle(this)">Submissions <span>(collapse)</span></legend>
<div id="oc_fs_submission_div">
';

// Allow submission ID auto increment value?
if (isset($_POST['startid'])) { 
	print '<div class="field"><label for="startid">Starting ID:</label><input type="number" name="startid" id="startid" min="1" max="99999" step="1" maxlength="5" style="width: 70px; text-align: right;" value="' . safeHTMLstr(varValue('startid', $_POST, '', true)) . '" /><div class="fieldnote note">Starting ID for submissions.  Available only if there are no submissions in the system.</div></div>';
}

print '
<div class="field"><label for="OC_paperSubNote">Submission Notice:<br /><br /><span class="note">Optional notice atop<br />submission page<br /><br />(<span style="color: #009; text-decoration: underline;" onclick="oc_sampleTextUpdate(\'OC_paperSubNote\')" title="overwrites field with sample text">sample text</span>)</span></label><textarea name="OC_paperSubNote" id="OC_paperSubNote" rows="8" cols="70">' . safeHTMLstr($_POST['OC_paperSubNote']) . '</textarea></div>

<div class="field"><label for="OC_subConfirmNotice">Confirmation Message:<br /><br /><span class="note">Message displayed upon<br />successful submission<br /><br />Variables:<br /><br />submission ID = [:sid:]<br />form fields = [:formfields:]</span></label><textarea name="OC_subConfirmNotice" id="OC_subConfirmNotice" rows="9" cols="70">' . safeHTMLstr($_POST['OC_subConfirmNotice']) . '</textarea></div>

<div class="field"><label for="OC_authorsMinDisplay">Min. Authors to Display:</label><input type="number" name="OC_authorsMinDisplay" id="OC_authorsMinDisplay" min="1" max="99" step="1" maxlength="2" style="width:50px; text-align: right;" value="' . safeHTMLstr($_POST['OC_authorsMinDisplay']) . '" /><div class="fieldnote note">Minimum number of authors to display on submission form</div></div>

<div class="field"><label for="OC_authorsMax">Max. Authors Allowed:</label><input type="number" name="OC_authorsMax" id="OC_authorsMax" min="1" max="99" step="1" maxlength="2" style="width:50px; text-align: right;" value="' . safeHTMLstr($_POST['OC_authorsMax']) . '" /><div class="fieldnote note">Maximum number of authors allowed per submission (max: 99)</div></div>

<div class="field"><label for="OC_authorOneContact">Set Author 1 as Contact?</label><fieldset class="radio">' . generateRadioOptions('OC_authorOneContact', $yesNoAR, $_POST['OC_authorOneContact']) . '</fieldset><div class="fieldnote note">Auto set Author 1 as contact author and hide Contact ID field on submission form</div></div>

<div class="field" id="OC_emailAuthorRecipientsField"><label for="OC_emailAuthorRecipients">Author Email Recipients:</label><fieldset class="radio">' . generateRadioOptions('OC_emailAuthorRecipients', $emailAuthorRecipientsAR, $_POST['OC_emailAuthorRecipients']) . '</fieldset><div class="fieldnote note">Author(s) to receive notices and Chair emails</div></div>

<div class="field"><label for="OC_editAcceptedOnly">Edit Accepted Only?</label><fieldset class="radio">' . generateRadioOptions('OC_editAcceptedOnly', $yesNoAR, $_POST['OC_editAcceptedOnly']) . '</fieldset><div class="fieldnote note">Restrict Edit Submission to accepted submissions only</div></div>

<input type="submit" name="submit" value="Save Settings" class="submit" />
</div>
</fieldset>
';

if (oc_hookSet('set-config-fileupload')) {
	foreach ($GLOBALS['OC_hooksAR']['set-config-fileupload'] as $hook) {
		require_once $hook;
	}
} else {
	print '
<fieldset id="oc_fs_files" role="header">
<legend onclick="oc_fsToggle(this)">Files <span>(collapse)</span></legend>
<div id="oc_fs_files_div">
<div class="field"><label for="OC_extar">File Formats:<br /><br /><span class="note">Available upload formats.<br />Use FileType module to<br />verify file is in proper<br />format</span></label><select name="OC_extar[]" id="OC_extAR" size="7" multiple>' . generateSelectOptions($OC_formatAR, $_POST['OC_extar'], TRUE, TRUE) . '</select></div>

<input type="submit" name="submit" value="Save Settings" class="submit" />
</div>
</fieldset>
';
}

print '
<fieldset id="oc_fs_localization" role="header">
<legend onclick="oc_fsToggle(this)">Localization <span>(collapse)</span></legend>
<div id="oc_fs_localization_div">
<div class="note" style="margin-bottom: 2em;">Selecting multiple languages will enable a menu on the main OpenConf page to select one\'s language choice.  Both ' . oc_strtolower(OCC_WORD_AUTHOR) . ' and reviewer pages are then displayed in the selected language.  For additional information, or to assist with translations, visit <a href="https://www.OpenConf.com/translate/" target="_blank">www.OpenConf.com/translate/</a>.' . (function_exists('gettext') ? '' : ' <strong>PHP must have gettext enabled.</strong>') . '</div>
<div class="field"><label for="OC_locales">Languages:</label><select name="OC_locales[]" id="OC_locales" multiple size="5">
';

foreach ($OC_languageAR as $locale => $localeAR) {
	print '<option value="' . safeHTMLstr($locale) . '"' . (in_array($locale, $_POST['OC_locales']) ? ' selected' : '') . '>' . safeHTMLstr($localeAR['language']) . '</option>';
}

print '
</select></div>

<div class="field"><label for="OC_localeDefault">Default Language:</label><select name="OC_localeDefault" id="OC_localeDefault">
';

foreach ($OC_languageAR as $locale => $localeAR) {
	print '<option value="' . safeHTMLstr($locale) . '"' . (($locale == $_POST['OC_localeDefault']) ? ' selected' : '') . '>' . safeHTMLstr($localeAR['language']) . '</option>';
}

print '
</select></div>

<div class="field"><label for="OC_timeZone">Time Zone:</label><select name="OC_timeZone" id="OC_timeZone">
' . generateSelectOptions($OC_zoneAR, $_POST['OC_timeZone'], FALSE) . '
</select></div>

<input type="submit" name="submit" value="Save Settings" class="submit" />
</div>
</fieldset>

</form>

<script language="javascript" type="text/javascript">
<!--
oc_showHideDiv("OC_paperAdvocates1","advocates");
function oc_checkEmail(e) {
	if (e.match(/,/)) {
		alert("Note that some mail servers may reject messages if the ' . OCC_WORD_CHAIR . ' Email contains multiple addresses. It is recommended only one address be used.");
	}
}
// -->
</script>
';

oc_replaceCKEditor(array('OC_homePageNotice', 'OC_reviewerSignUpNotice', 'OC_committeeFooter', 'OC_programSignUpNotice', 'OC_paperSubNote', 'OC_subConfirmNotice'));

printFooter();
?>
