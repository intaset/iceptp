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

printHeader("Clear Advocate Data",1);

if (isset($_POST['submit']) && ($_POST['submit'] == 'Clear All Advocate Recommendations')) {
	// Check for valid submission
	if (!validToken('chair')) {
		warn('Invalid submission');
	}

	// Clear out recommendations
	issueSQL("UPDATE `" . OCC_TABLE_PAPERADVOCATE . "` SET `adv_recommendation`=NULL, `adv_comments`=NULL");

	// Hook
	if (oc_hookSet('chair-clear-advocate')) {
		foreach ($OC_hooksAR['chair-clear-advocate'] as $f) {
			require_once $f;
		}
	}
	
	print '<p>Advocate recommendation data has been cleared</p>';

} else {
	print '
<form method="post" action="">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />
<p>Clicking the button below will clear out all advocate recommendation data, while maintaining advocate assignments.  Only click the button if you intend on having advocates start the recommendation process anew.</p>
<p style="text-align: Center"><input type="submit" name="submit" value="Clear All Advocate Recommendations" /></p>
</form>
';
}

printFooter();

?>