<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

// Restores a withdrawn submission
function restorePaper($paperid) {
	$q = "SELECT * FROM `" . OCC_TABLE_WITHDRAWN . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'";
	$r = ocsql_query($q) or err('Unable to retrieve submission information');
	if (ocsql_num_rows($r) == 1) {
		// paper
		$l = ocsql_fetch_assoc($r);
		ocsql_query($l['papersql']) or err('Unable to restore paper');
		if (!empty($l['authorsql'])) {
			ocsql_query($l['authorsql']) or err('Unable to restore ' . oc_strtolower(OCC_WORD_AUTHOR) . ' information');
		}
		if (!empty($l['topicsql'])) {
			ocsql_query($l['topicsql']) or err('Unable to restore topic information');
		}
		ocsql_query("DELETE FROM `" . OCC_TABLE_WITHDRAWN . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'") or err('Unable to update withdrawn submission status');
		// restore		
		if (oc_hookSet('restore_paper')) {
			foreach ($GLOBALS['OC_hooksAR']['restore_paper'] as $inc) {
				include $inc;
			}
		}
		
		// log
		ocsql_query("INSERT INTO `" . OCC_TABLE_LOG . "` SET `datetime`='" . safeSQLstr(gmdate('Y-m-d H:i:s')) . "', `type`='submission', `entry`='" . safeSQLstr('Submission ID ' . $paperid . ' restored') . "'");

	} else {
		print " &nbsp; &nbsp; SUBMISSION NOT FOUND<p />\n";
	}
}

// Withdraws submission
function withdrawPaper($paperid, $by) {
	$q = "SELECT * FROM `" . OCC_TABLE_PAPER . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'";
	$r = ocsql_query($q) or err('Unable to retrieve submission information');
	if (ocsql_num_rows($r) == 1) {
		// paper
		$l = ocsql_fetch_assoc($r);
		$papervalues = '';
		foreach ($l as $fld => $val) {
			$papervalues .= "`$fld`=";
			if (($val === NULL) || ($fld == 'format')) {
				$papervalues .= "NULL,";
			} else {
				$papervalues .= "'" . safeSQLstr($val) . "',";
			}
		}
		if (!empty($papervalues)) {
			$paperq = "INSERT INTO `" . OCC_TABLE_PAPER . "` SET " . rtrim($papervalues, ',');
		} else {
			$paperq = '';
		}
		$title = $l['title'];
		$contactid = $l['contactid'];
		$contact_author = '';
		$contact_email = '';
		// authors
		$q = "SELECT * FROM `" . OCC_TABLE_AUTHOR . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'";
		$r = ocsql_query($q) or err('Unable to retrieve ' . oc_strtolower(OCC_WORD_AUTHOR) . ' information');
		$authorvalues = "";
		$fields = '';
		while ($l = ocsql_fetch_assoc($r)) {
			if (empty($fields)) {
				$fields = "`" . implode('`,`', array_keys($l)) . "`";
			}
			$authorvalues .= "(";
			foreach ($l as $val) {
				if ($val === NULL) {
					$authorvalues .= "NULL,";
				} else {
					$authorvalues .= "'" . safeSQLstr($val) . "',";
				}
			}
			$authorvalues = rtrim($authorvalues, ',') . "),";
			// contact author?
			if ($l['position'] == $contactid) {
				$contact_author = $l['name_first'] . ' ' . $l['name_last'];
				$contact_email = $l['email'];
			}
		}
		if (!empty($authorvalues)) {
			$authorq = "INSERT INTO `" . OCC_TABLE_AUTHOR . "` ($fields) VALUES " . rtrim($authorvalues, ',');
		} else {
			$authorq = '';
		}
		//topics
		$q = "SELECT * FROM `" . OCC_TABLE_PAPERTOPIC . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'";
		$r = ocsql_query($q) or err('Unable to retrieve topics');
		$topicValues = '';
		while ($l = ocsql_fetch_assoc($r)) {
			$topicValues .= '(' . $l['paperid'] . ', ' . $l['topicid'] . '),';
		}
		if (!empty($topicValues)) {
			$topicq = "INSERT INTO `" . OCC_TABLE_PAPERTOPIC . "` (`paperid`, `topicid`) VALUES " . rtrim($topicValues, ',');
		} else {
			$topicq = '';
		}
		
		if (oc_hookSet('withdraw_paper-preprocess')) {
			foreach ($GLOBALS['OC_hooksAR']['withdraw_paper-preprocess'] as $inc) {
				include $inc;
			}
		}
		
		$q = "INSERT INTO `" . OCC_TABLE_WITHDRAWN . "` SET `paperid`='" . safeSQLstr($paperid) . "', `title`='" . safeSQLstr($title) . "', `contact_author`='" . safeSQLstr($contact_author) . "', `contact_email`='" . safeSQLstr($contact_email) . "', `papersql`='" . safeSQLstr($paperq) . "', `authorsql`='" . safeSQLstr($authorq) . "', `topicsql`='" . safeSQLstr($topicq) . "', `withdraw_date`='" . date('Y-m-d H:i:s') . "', `withdrawn_by`='" . safeSQLstr($by) . "'";
		ocsql_query($q) or err('Unable to save info');
		if (oc_hookSet('withdraw_paper')) {
			foreach ($GLOBALS['OC_hooksAR']['withdraw_paper'] as $inc) {
				include $inc;
			}
		}

		// log
		ocsql_query("INSERT INTO `" . OCC_TABLE_LOG . "` SET `datetime`='" . safeSQLstr(gmdate('Y-m-d H:i:s')) . "', `type`='submission', `entry`='" . safeSQLstr('Submission ID ' . $paperid . ' withdrawn by ' . $by . '.  Title: ' . $title) . "'");

	} else {
		return(false);
	}
	return(true);
}

// Deletes submission
function deletePaper($paperid, $log=true) {
	$q = "SELECT * FROM `" . OCC_TABLE_PAPER . "` WHERE paperid='" . safeSQLstr($paperid) . "'";
	$r = ocsql_query($q) or err("Unable to retrieve submission format");
	$l = ocsql_fetch_assoc($r);
	issueSQL("DELETE FROM `" . OCC_TABLE_PAPERADVOCATE . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'");
	issueSQL("DELETE FROM `" . OCC_TABLE_PAPERREVIEWER . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'");
	issueSQL("DELETE FROM `" . OCC_TABLE_PAPERSESSION . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'");
	issueSQL("DELETE FROM `" . OCC_TABLE_PAPERTOPIC . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'");
	issueSQL("DELETE FROM `" . OCC_TABLE_AUTHOR . "` WHERE `paperid`='" . safeSQLstr($paperid) . "'");
	issueSQL("DELETE FROM `" . OCC_TABLE_PAPER . "` WHERE `paperid`='" .safeSQLstr($paperid) . "'");
	if ($l['format'] && oc_isFile(($pfile = $GLOBALS['OC_configAR']['OC_paperDir'] . $paperid . '.' . $l['format']))) {
		if (!oc_deleteFile($pfile)) {
			print " &nbsp; &nbsp; &nbsp; Unable to delete the file for submission " . safeHTMLstr($paperid) . "<p />\n";
		}
	}
	if (oc_hookSet('delete_paper')) {
		foreach ($GLOBALS['OC_hooksAR']['delete_paper'] as $inc) {
			include $inc;
		}
	}

	// log
	if ($log) {
		ocsql_query("INSERT INTO `" . OCC_TABLE_LOG . "` SET `datetime`='" . safeSQLstr(gmdate('Y-m-d H:i:s')) . "', `type`='submission', `entry`='" . safeSQLstr('Submission ID ' . $paperid . ' deleted.  Title: ' . $l['title']) . "'");
	}

}
