<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

require_once OCC_FORM_INC_FILE;
require_once OCC_REVIEW_INC_FILE;

$authorcomments = array();
$reviewfields = array();
$advocatecomment = array();
$chairnotes = array();

// get acceptance type
if (preg_match("/_(\d+)/", $_POST['recipient'], $matches) && isset($OC_acceptanceValuesAR[$matches[1]])) {
	$accval = $OC_acceptanceValuesAR[$matches[1]]['value'];
	$accSQL = "`" . OCC_TABLE_PAPER . "`.`accepted`='" . safeSQLstr($accval) . "'";
} else {
	$accSQL = '';
}

// get review fields to display
$displayFieldAR = array();
foreach ($OC_reviewQuestionsAR as $fid => $far) {
	if (isset($far['showauthor']) && $far['showauthor']) {
		$displayFieldAR[] = $fid;
	}
}

// get review fields and reviewer comments
$tempq = "SELECT `" . OCC_TABLE_PAPER . "`.`paperid`, `" . OCC_TABLE_PAPERREVIEWER . "`.* FROM `" . OCC_TABLE_PAPER . "`, `" . OCC_TABLE_PAPERREVIEWER . "` WHERE " . (!empty($accSQL) ? ($accSQL . " AND ") : '') . "`" . OCC_TABLE_PAPER . "`.`paperid`=`" . OCC_TABLE_PAPERREVIEWER . "`.`paperid`";
$tempr = ocsql_query($tempq) or err("Unable to get " . oc_strtolower(OCC_WORD_AUTHOR) . " comments");
while ($templ = ocsql_fetch_array($tempr)) {
	// init vars for paperid
	if (!isset($authorcomments[$templ['paperid']])) {
		$authorcomments[$templ['paperid']] = "";
	}
	if (!isset($reviewfields[$templ['paperid']])) {
		$reviewfields[$templ['paperid']] = "";
	}
	// set author comments
	if (!empty($templ['authorcomments'])) {
		$authorcomments[$templ['paperid']] .= $commentSeparator . "\n" . $templ['authorcomments'] . "\n";
	}
	// set review fields
	$reviewInfo = '';
	foreach ($displayFieldAR as $fid) {
		if (!empty($templ[$fid])) {
			$reviewInfo .= oc_strtoupper(oc_($OC_reviewQuestionsAR[$fid]['short'])) . ': ' . oc_getFieldValue($OC_reviewQuestionsAR, $templ, $fid) ."\n\n";
		}
	}
	if (!empty($reviewInfo)) {
		$reviewfields[$templ['paperid']] .= $commentSeparator . "\n" . $reviewInfo . "\n";
	}
}

// get advocate comments
$tempq = "SELECT `" . OCC_TABLE_PAPER . "`.`paperid`, `" . OCC_TABLE_PAPERADVOCATE . "`.`adv_comments` FROM `" . OCC_TABLE_PAPER . "`, `" . OCC_TABLE_PAPERADVOCATE . "` WHERE " . (!empty($accSQL) ? ($accSQL . " AND ") : '') . "`" . OCC_TABLE_PAPER . "`.`paperid`=`" . OCC_TABLE_PAPERADVOCATE . "`.`paperid`";
$tempr = ocsql_query($tempq) or err("Unable to get advocate comments");
while ($templ = ocsql_fetch_array($tempr)) {
	// skip empty comments
	if (!isset($templ['adv_comments'])) { continue; }
	// init advocatecomment for paperid
	if (!isset($advocatecomment[$templ['paperid']])) {
		$advocatecomment[$templ['paperid']] = "";
	}
	// add comments with separator
	$advocatecomment[$templ['paperid']] .= "\n" . $templ['adv_comments'] . "\n";
}

// get chair comments
$tempq = "SELECT `paperid`, `pcnotes` FROM `" . OCC_TABLE_PAPER . "`" . (!empty($accSQL) ? ( " WHERE " . $accSQL) : '');
$tempr = ocsql_query($tempq) or err("Unable to get chair comments");
while ($templ = ocsql_fetch_array($tempr)) {
	// skip empty comments
	if (!isset($templ['pcnotes'])) { continue; }
	// init chairnotes for paperid
	if (!isset($chairnotes[$templ['paperid']])) {
		$chairnotes[$templ['paperid']] = "";
	}
	// add comments with separator
	$chairnotes[$templ['paperid']] .= "\n" . $templ['pcnotes'] . "\n";
}

$specialIndexAR['authorcomments'] = 'paperid';
$specialIndexAR['reviewfields'] = 'paperid';
$specialIndexAR['advocatecomment'] = 'paperid';
$specialIndexAR['chairnotes'] = 'paperid';

// Check for addt'l (hook) special variables
if (oc_hookSet('chair-email-authors')) {
	foreach ($OC_hooksAR['chair-email-authors'] as $f) {
		require_once $f;
	}
}
