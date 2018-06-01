<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$hdr = 'Export Committee Members';
$hdrfn = 1;

require_once "../include.php";

beginChairSession();

require_once 'export.inc';

$scope = 'committee_members';	// scope of export for filename

// check that matching submissions exist
$r = ocsql_query("SELECT COUNT(*) AS `count` FROM `" . OCC_TABLE_REVIEWER . "`");
$l = ocsql_fetch_assoc($r);
if ($l['count'] == 0) {
	warn('There are no reviewers to export', $hdr, $hdrfn);
	exit;
}

require_once OCC_FORM_INC_FILE;
require_once OCC_COMMITTEE_INC_FILE;

$skip = 1;

$fieldAR = array(
	'reviewerid'   		=> 'Reviewer ID',
	'name'				=> 'Full Name',
);

if ($OC_configAR['OC_paperAdvocates']) {
	$fieldAR['onprogramcommittee'] = 'PC/Advocate';
}

foreach ($OC_reviewerFieldSetAR as $fsKey => $fsAR) {
	foreach ($fsAR['fields'] AS $fieldID) {
		if (preg_match("/^password/i", $fieldID) || ($fieldID == 'topics') || empty($OC_reviewerFieldAR[$fieldID]['name'])) { continue; }
		$fieldAR[$fieldID] = $OC_reviewerFieldAR[$fieldID]['name'];
	}
}

$fieldAR['_topics'] = 'Topics';

$fieldAR['skip' . $skip++]	= '';

// Default list of checked fields
$checkedFieldAR = array_keys($fieldAR);

// Include extra fields
if (oc_hookSet('chair-export_reviewers-fields')) {
	foreach ($OC_hooksAR['chair-export_reviewers-fields'] as $v) {
		require_once $v;
	}
}

if (isset($_POST['submit']) && ($_POST['submit'] == "Generate File") && isset($_POST['fields']) && !empty($_POST['fields'])) {
	// Check for valid submission
	if (!validToken('chair')) {
		warn('Invalid submission');
	}
	// Verify Fields
	$fieldARkeys = array_keys($fieldAR);
	foreach ($_POST['fields'] as $f) {
		if (!in_array($f, $fieldARkeys)) {
			err('Invalid field name selection');
		}
	}
	
	// Init extra field AR
	$extraAR = array();

	// List of fields to export
	$exportFieldsAR = $_POST['fields'];

	// Topics
	if (in_array('_topics', $exportFieldsAR)) {
		$q = "SELECT `" . OCC_TABLE_REVIEWER . "`.`reviewerid`, `" . OCC_TABLE_TOPIC . "`.`topicname`, `" . OCC_TABLE_TOPIC . "`.`short` FROM `" . OCC_TABLE_REVIEWER . "`, `" . OCC_TABLE_TOPIC . "`, `" . OCC_TABLE_REVIEWERTOPIC . "` WHERE `" . OCC_TABLE_REVIEWER . "`.`reviewerid`=`" . OCC_TABLE_REVIEWERTOPIC . "`.`reviewerid` AND `" . OCC_TABLE_REVIEWERTOPIC . "`.`topicid`=`" . OCC_TABLE_TOPIC . "`.`topicid`";
		$r = ocsql_query($q) or err("Unable to retrieve data for export (t)", $hdr, $hdrfn);
		while ($l = ocsql_fetch_assoc($r)) {
			if (isset($extraAR[$l['reviewerid']]['_topics'])) {
				$extraAR[$l['reviewerid']]['_topics'] .= ',' . useTopic($l['short'], $l['topicname']);
			} else {
				$extraAR[$l['reviewerid']]['_topics'] = useTopic($l['short'], $l['topicname']);
			}
		}
	}

	// Get extra fields data
	if (oc_hookSet('chair-export_reviewers-data')) {
		foreach ($OC_hooksAR['chair-export_reviewers-data'] as $v) {
			require_once $v;
		}
	}

	// Get sub. data & iterate through each
	$q = "SELECT *,`reviewerid` AS `id`, CONCAT_WS(' ', `name_first`, `name_last`) AS `name` FROM `" . OCC_TABLE_REVIEWER . "` ORDER BY `reviewerid`";
	$r = ocsql_query($q) or err("Unable to retrieve data for export");
	if (ocsql_num_rows($r) == 0) {
		warn("There are no committee members to export", 'Export Committee Members', 1);
		exit;
 	} else {  // Export file
		oc_export($scope, $exportFieldsAR, $fieldAR, $r, $extraAR, $OC_reviewerFieldAR);
		exit;
	}
	
}

// Display form
printHeader($hdr, $hdrfn);

print '
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />
<p>Export will generate one row/entry per committee member.</p>

<p style="font-weight: bold">Select Fields to Export:</p>
';

foreach ($fieldAR as $fieldID => $fieldName) {
	if (preg_match("/^skip\d+$/", $fieldID)) {
		print '<p />';
		continue;
	}
	print '<label><input type="checkbox" name="fields[]" value="' . $fieldID . '" ';
	if (in_array($fieldID,$checkedFieldAR)) { print 'checked '; }
	print '/> ' . $fieldName . "</label><br />\n";
}

print '
<p />
<input type="submit" name="submit" value="Generate File" />
&nbsp; &nbsp;
Format: <select name="format">
';

foreach ($OC_exportFormatAR as $fmt => $fmtAR) {
	print '<option value="' . $fmt . '">' . $fmtAR['name'] . '</option>';
}

print '
</select>
</p>
</form>

<p class="note">Note: When opening up a CSV or Tab-delimited file in a spreadsheet, you may need to specify the character encoding: UTF-8.</p>

';

printFooter();

?>
