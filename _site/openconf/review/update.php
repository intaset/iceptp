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

oc_sendNoCacheHeaders();

beginSession();

$hdr = ''; // set these so req OCC_COMMITTEE_INC_FILE below skips printHeader
$hdrfn = 0;

printHeader(oc_('Update Profile'), 2);

require_once OCC_FORM_INC_FILE;
require_once OCC_COMMITTEE_INC_FILE;

// Update fields for editing profile
unset($OC_reviewerFieldAR['username']);
if (isset($OC_reviewerFieldSetAR['fs_passwords'])) {
	$OC_reviewerFieldSetAR['fs_passwords']['fieldset'] = oc_('Change Password');
	$OC_reviewerFieldSetAR['fs_passwords']['note'] = oc_('Leave these fields blank if you do not want to change the password');
	$OC_reviewerFieldSetAR['fs_passwords']['fields'] = array('password1', 'password2');
}
if (isset($OC_reviewerFieldAR['password1']) && isset($OC_reviewerFieldAR['password2'])) {
	$OC_reviewerFieldAR['password1']['name'] = oc_('New Password');
	$OC_reviewerFieldAR['password1']['required'] = false;
	$OC_reviewerFieldAR['password2']['required'] = false;
}

// Process submission
if (isset($_POST['ocaction']) && ($_POST['ocaction'] == "Update Profile")) {
	// Check for valid submission
	if (!validToken('ac')) {
		warn(oc_('Invalid submission'));
	}

	$err = '';
	$qfields = array();
	$tfields = array();

	require_once 'committee-validate.inc';

	if (!empty($err)) {
		print '<div class="warn">' . oc_('Please check the following:') . '<ul>' . $err . '</ul></div><hr />';
	} else {
		// check password
		$q = "SELECT `password` FROM `" . OCC_TABLE_REVIEWER . "` WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "'";
		$r = ocsql_query($q) or err('Unable to retrieve reviewer information');
		$rinfo = ocsql_fetch_array($r);
		if (oc_password_verify($_POST['oldpwd'], $rinfo['password'])) {
			// Update fields
			$q = "UPDATE `" . OCC_TABLE_REVIEWER . "` SET `lastupdate`=NOW(), ";
			foreach ($qfields as $qid => $qval) {
				$q .= "`" . $qid . "`=" . $qval . ", ";
			}
			$q = rtrim($q, ', ');
			$q .= " WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "' LIMIT 1";
			ocsql_query($q) or err('Unable to update database');
			
			// Update topics
			issueSQL("DELETE FROM `" . OCC_TABLE_REVIEWERTOPIC . "` WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "'");
			if (!empty($tfields)) {
				$q = "INSERT INTO `" . OCC_TABLE_REVIEWERTOPIC . "` (`reviewerid`,`topicid`) VALUES";
				foreach ($tfields as $t) {
					$q .= " (" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . ",$t),";
				}
				$r = ocsql_query(rtrim($q, ',')) or err("unable to add reviewer topic, but account created ");
			}

			print '<p>' . sprintf(oc_('Your profile has been successfully updated.  <a href="%s">Return to the main Committee page</a>.'), 'reviewer.php') . '</p>';

			// ocIgnore included so poEdit picks up (DB) template translation
			$ocIgnoreSubject = oc_('Committee Member Profile Updated');
			$ocIgnoreBody = oc_('Your profile has been updated.  The submitted information follows below:

[:fields:]');

			list($mailsubject, $mailbody) = oc_getTemplate('committee-update');
			$fields = oc_genFieldMessage($OC_reviewerFieldSetAR, $OC_reviewerFieldAR, $_POST);
			$templateExtraAR = array(
				'fields' => oc_('Username') . ": " . $_SESSION[OCC_SESSION_VAR_NAME]['acusername'] . "\n\n" . $fields
			);
			$mailsubject = oc_replaceVariables($mailsubject, $templateExtraAR);
			$mailbody = oc_replaceVariables($mailbody, $templateExtraAR);
	
			if (oc_hookSet('committee-signup-update')) {
				foreach ($GLOBALS['OC_hooksAR']['committee-signup-update'] as $hook) {
					require_once $hook;
				}
			}
			
			sendEmail($_POST['email'], $mailsubject, $mailbody, $OC_configAR['OC_notifyReviewerProfileUpdate']);
	
			printFooter();
			exit;
		} else {
			print '<p class="warn">' . oc_('Current password is not correct') . '</p><hr />';
		}
	}
} else { // not submitting
	// get stored values
	$q = "SELECT * FROM `" . OCC_TABLE_REVIEWER . "` WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "'";
	$r = ocsql_query($q) or err("Unable to retrieve reviewer information ".ocsql_errno($r));
	$_POST = array_merge($_POST, ocsql_fetch_array($r));
	// Get list of reviewer topics
	$tq = "SELECT * FROM `" . OCC_TABLE_REVIEWERTOPIC . "` WHERE `reviewerid`='" . safeSQLstr($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']) . "'";
	$tr = ocsql_query($tq) or err("Unable to retrieve topics");
	$_POST['topics'] = array();
	while ($tl = ocsql_fetch_assoc($tr)) {
		$_POST['topics'][] = $tl['topicid'];	
	}
	if ( isset($OC_reviewerFieldAR['topics']['type']) && ($OC_reviewerFieldAR['topics']['type'] == 'radio') && isset($_POST['topics'][0]) ) {
		$_POST['topics'] = $_POST['topics'][0];
	}
}

print '
<p class="note">' . oc_('Make the changes you want below, then enter your password for verification and click the <em>Update Profile</em> button at the bottom.') . '</p>

<form method="post" action="' . $_SERVER['PHP_SELF'] . '" class="ocform">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['actoken'] . '" />
<input type="hidden" name="ocaction" value="Update Profile" />
';

oc_displayFieldSet($OC_reviewerFieldSetAR, $OC_reviewerFieldAR, $_POST);


print '
<span class="note2">' . oc_('Enter your current password and click the <em>Update Profile</em> button') . '</span><p>
' . oc_('Current Password') . ': <input size="20" name="oldpwd" type="password" style="background-color: #f6f6f6" />
&nbsp; &nbsp; &nbsp; 
<input type="submit" name="submit" value="' . oc_('Update Profile') . '" class="submit" />
</form>
';


printFooter();

?>
