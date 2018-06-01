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

printHeader("Clear Review Data",1);

if (isset($_POST['submit']) && ($_POST['submit'] == 'Clear All Review Data')) {
	// Check for valid submission
	if (!validToken('chair')) {
		warn('Invalid submission');
	}

	// Clear out reviews
	issueSQL("TRUNCATE `" . OCC_TABLE_PAPERSESSION . "`");
	issueSQL("UPDATE `" . OCC_TABLE_PAPERREVIEWER . "` SET `completed`='F', `updated`=NULL, `score`=NULL, `recommendation`=NULL, `category`=NULL, `value`=NULL, `familiar`=NULL, `bpcandidate`=NULL, `length`=NULL, `difference`=NULL, `pccomments`=NULL, `authorcomments`=NULL");
	$r = ocsql_query("SHOW COLUMNS FROM `" . OCC_TABLE_PAPERREVIEWER . "` WHERE LEFT(`field`, 3) = 'cf_'") or err('Unable to delete custom fields (1)');
	if (ocsql_num_rows($r) >= 1) {
		$q = "ALTER TABLE `" . OCC_TABLE_PAPERREVIEWER . "`";
		while ($l = ocsql_fetch_assoc($r)) {
			$q .= " DROP `" . $l['Field'] . "`,";
		}
		ocsql_query(rtrim($q, ',')) or err('Unable to delete custom fields (2)', $hdr, $hdrfn);
	}


	// Hook
	if (oc_hookSet('chair-clear-review')) {
		foreach ($OC_hooksAR['chair-clear-review'] as $f) {
			require_once $f;
		}
	}
	
	print '<p>Review data has been cleared</p>';

} else {
	print '
<form method="post" action="">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />
<p>Clicking the button below will clear out all review data, while maintaining review assignments.  Only click the button if you intend on having reviewers start the review process anew.</p>
<p style="text-align: Center"><input type="submit" name="submit" value="Clear All Review Data" /></p>
</form>
';
}

printFooter();

?>