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

printHeader("Assign Advocates", 1);

if (isset($_POST['submit']) && ($_POST['submit'] == "Assign Advocates")) {
	// Check for valid submission
	if (!validToken('chair')) {
		warn('Invalid submission');
	}
    // Check that we have at least one paper and advocate
    if (empty($_POST['papers']) || empty($_POST['advocates'])) {
        print '<span class="err">Please go back and select at least one submission and one advocate</span><p>';
    } else {
		// Get conflicts?
		if ($OC_configAR['OC_allowConflictOverride'] && isset($_POST['conflict_override']) && ($_POST['conflict_override'] == 1)) {
			$conflictAR = array();
		} else {
			$conflictAR = getConflicts();
		}
		// Assign advocates (and reviewers?)
		// NOTE: Although a foreach is used below for future expansion, there should only be 1 advocate
		$advocateAssignmentAR = array();    // keep track of advocates w/successful assignments
		$submissionAssignmentAR = array();	// keep track of submissions assigned
		foreach ($_POST['advocates'] as $i) {
			if (!is_numeric($i)) { err('Invalid advocate ID: ' . safeHTMLstr($i)); }
			foreach ($_POST['papers'] as $j) {
				if (!is_numeric($j)) { err('Invalid submission ID: ' . safeHTMLstr($j)); }
				// Check for conflict
				if (in_array("$j-$i",$conflictAR)) {
					print "<p class=\"warn\">! Submission $j is in conflict with advocate $i.</p>\n";
					continue;
				}
				// Delete current assignment?
				if (isset($_POST['assignment_override']) && ($_POST['assignment_override'] == 1)) {
					oc_deleteAssignments($j, null, 'advocate');
				}
				// Make assignment
				$q = "INSERT INTO `" . OCC_TABLE_PAPERADVOCATE . "` (`paperid`,`advocateid`) VALUES ('" . safeSQLstr($j) . "','" . safeSQLstr($i) . "')";
				ocsql_query($q);
				if (($merr = ocsql_errno()) != 0) {
					if ($merr == 1062) {	// Duplicate entry
					    print "<span class=\"warn\">! Submission $j already has an advocate assigned.  Please delete the existing advocate first before assigning a new one, or select <em>Override Current Assingment</em>.</span><p />\n";
					} else {
	                    print "<span class=\"err\">!! Error assigning submission $j to advocate $i</span><p />\n";
					}
                } else {
                    print "<p>Submission $j assigned to advocate $i.\n";
		  			if (!isset($advocateAssignmentAR[$i])) {
						$advocateAssignmentAR[$i] = array();
					}
					$advocateAssignmentAR[$i][] = $j;
					if (!in_array($j, $submissionAssignmentAR)) {
						$submissionAssignmentAR[] = $j;
					}
					// Also assign a reviewer?
					if (isset($_POST['asrev']) && ($_POST['asrev'] == "yes")) {
						$q = "INSERT INTO `" . OCC_TABLE_PAPERREVIEWER . "` (`paperid`,`reviewerid`,`assigned`) VALUES ('" . safeSQLstr($j) . "','" . safeSQLstr($i) . "','" . safeSQLstr(date('Y-m-d')) . "')";
						ocsql_query($q);
        	        	if (($merr = ocsql_errno()) != 0) {
							if ($merr == 1062) {	// Duplicate entry
							    print "<dd><span class=\"warn\">! Submission $j has already been assigned reviewer $i.</span></dd><p />\n";
							} else {
	            	    	    print "<span class=\"err\">!! Error assigning submission $j to reviewer $i</span><p />\n";
							}
						}
					}
                } 
            } 
        } 

		print '<p><hr /></p>';

        // Notify?
		if ( isset($_POST['notify']) && ($_POST['notify'] == 1) && (count($advocateAssignmentAR) > 0) ) {
			// Get list of sub titles
			$r = ocsql_query("SELECT `paperid`, `title` FROM `" . OCC_TABLE_PAPER . "` WHERE `paperid` IN (" . implode(',',$submissionAssignmentAR) . ")") or err('Unable to retrieve submission titles for notification');
			$submissionTitleAR = array();
			while ($l = ocsql_fetch_assoc($r)) {
				$submissionTitleAR[$l['paperid']] = $l['paperid'] . '. ' . $l['title'];
			}

			// Get advocates to notify
			$q = "SELECT `reviewerid`, `name_first`, `name_last`, CONCAT_WS(' ', `name_first`, `name_last`) AS `name`, `email`, `username` FROM `" . OCC_TABLE_REVIEWER . "` WHERE `reviewerid` IN (" . implode(',', array_keys($advocateAssignmentAR)) . ")";
			$r = ocsql_query($q) or err('Unable not get advocate email address(es) for notification');
			
			// Get notification template
			// ocIgnore included so poEdit picks up (DB) template translation
			$ocIgnoreSubject = oc_('New Advocate Assignment(s)');
			//T: [:OC_confName:] is the event name
			$ocIgnoreBody = oc_('New assignments have been made for you to advocate in the [:OC_confName:] OpenConf system:


[:assignments:]

Thank you.');
			list($subject, $message) = oc_getTemplate('chair-assign_advocates');
			
			// Hook
			if (oc_hookSet('chair-assign-advocate-notify')) {
				foreach ($OC_hooksAR['chair-assign-advocate-notify'] as $f) {
					require_once $f;
				}
			}

			// Iterate through advocates
			while ($l = ocsql_fetch_assoc($r)) {
				$templateExtraAR = $l;
				$templateExtraAR['assignments'] = '';
				$templateExtraAR['advocateid'] = $l['reviewerid'];
				foreach ($advocateAssignmentAR[$l['reviewerid']] as $sid) {
					$templateExtraAR['assignments'] .= $submissionTitleAR[$sid] . "\n\n";
				}
				$tmpsubject = oc_replaceVariables($subject, $templateExtraAR);
				$tmpmessage = oc_replaceVariables($message, $templateExtraAR);
				if (sendEmail($l['email'], $tmpsubject, $tmpmessage)) {
					print '<p>Notification sent to ' . safeHTMLstr($l['name']) . ' (' . safeHTMLstr($l['reviewerid']) . ')</p>';
				} else {
					print '<p class="err">!! Unable to email notification to <a href="mailto:' . safeHTMLstr($l['email']) . '">' . safeHTMLstr($l['name']) . '</a> (' . safeHTMLstr($l['reviewerid']) . ')</p>';
				}
			}
			print '<p><hr /></p>';
		}
    }
	
	print '
<p>&#187; <a href="' . $_SERVER['PHP_SELF'] . '">Make additional assignments</a></p>
<p>&#187; <a href="list_advocates.php">View/Edit assignments</a></p>
<p>&#187; <a href="list_conflicts.php">Manage conflicts</a></p>
';
	
} else {
    $pq = "SELECT `" . OCC_TABLE_PAPER . "`.`paperid`, `" . OCC_TABLE_PAPER . "`.`title`, `" . OCC_TABLE_PAPERADVOCATE . "`.`advocateid` FROM `" . OCC_TABLE_PAPER . "` LEFT JOIN `" . OCC_TABLE_PAPERADVOCATE . "` ON `" . OCC_TABLE_PAPER . "`.`paperid`=`" . OCC_TABLE_PAPERADVOCATE . "`.`paperid` ORDER  BY `" . OCC_TABLE_PAPER . "`.`paperid`";
    $pr = ocsql_query($pq) or err("Unable to get submissions");
	// Get pad size for paper id's - yes, we really need the max id, but this should do:)
	$psize = oc_strlen((string) ocsql_num_rows($pr));
    if (ocsql_num_rows($pr) == 0) {
        print '<span class="warn">No submissions have been made yet</span><p>';
    } else {
		if (!isset($_GET['s']) || ($_GET['s'] == "id"))  {
			$idsortstr = 'ID';
		    $nsortstr = '<a href="' . $_SERVER['PHP_SELF'].'?s=name">Name</a>';
			$rsortstr = '<a href="' . $_SERVER['PHP_SELF'].'?s=reviews">No. Submission</a>';
			$legend = "[ Advocate ID - $nsortstr ($rsortstr) ]";
			$sortby = "`" . OCC_TABLE_REVIEWER . "`.`reviewerid`";
		} elseif ($_GET['s'] == "reviews") {
			$rsortstr = 'No. Reviews';
		    $idsortstr = '<a href="' . $_SERVER['PHP_SELF'].'?s=id">ID</a>';
		    $nsortstr = '<a href="' . $_SERVER['PHP_SELF'].'?s=name">Name</a>';
			$legend = "[ No. Submissions - Advocate $nsortstr - $idsortstr ]";
			$sortby = "`acount`, `" . OCC_TABLE_REVIEWER . "`.`name_last`, `" . OCC_TABLE_REVIEWER . "`.`name_first`";
		} else {
		    $idsortstr = '<a href="' . $_SERVER['PHP_SELF'].'?s=id">ID</a>';
			$nsortstr = 'Name';
			$rsortstr = '<a href="' . $_SERVER['PHP_SELF'].'?s=reviews">No. Submissions</a>';
			$legend = "[ Advocate Name - $idsortstr ($rsortstr) ]";
			$sortby = "`" . OCC_TABLE_REVIEWER . "`.`name_last`, `" . OCC_TABLE_REVIEWER . "`.`name_first`";
		}
		$rq = "SELECT `" . OCC_TABLE_REVIEWER . "`.`reviewerid`, CONCAT_WS(' ', `" . OCC_TABLE_REVIEWER . "`.`name_first`, `" . OCC_TABLE_REVIEWER . "`.`name_last`) AS `name`, count(`" . OCC_TABLE_PAPERADVOCATE . "`.`advocateid`) AS `acount` FROM `" . OCC_TABLE_REVIEWER . "` LEFT JOIN `" . OCC_TABLE_PAPERADVOCATE . "` ON `" . OCC_TABLE_REVIEWER . "`.`reviewerid`=`" . OCC_TABLE_PAPERADVOCATE . "`.`advocateid` WHERE `" . OCC_TABLE_REVIEWER . "`.`onprogramcommittee`='T' GROUP BY `" . OCC_TABLE_REVIEWER . "`.`reviewerid` ORDER BY $sortby";
        $rr = ocsql_query($rq) or err("Unable to get program committee members");
		// Get pad size for advocate id's - yes, we really need the max id, but this should do:)
		$rsize = oc_strlen((string) ocsql_num_rows($rr));
        if (ocsql_num_rows($rr) == 0) {
            print '<span class="warn">No program committee members have signed up yet</span><p>';
        } else {
            print '
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />
<div style="float: left; margin-right: 50px;">
<p><strong>Select Submission(s):</strong></p>
<p>[ Submission ID - Title (Advocate ID or ***) ]</p>
<select multiple size="20" name="papers[]">
';

            while ($paper = ocsql_fetch_assoc($pr)) {
                print '<option value="' . $paper['paperid'] . '">' . padNumber($paper['paperid'],$psize) . ' - ' . safeHTMLstr(shortenStr($paper['title'],80)) . ' (';
		if ($paper['advocateid']) { print $paper['advocateid']; }
		else { print '***'; }
		print ")</option>\n";
            } 

            print '
</select>
</div>
<div style="float: left;">
<p><strong>Select Advocate:</strong> &nbsp; <span class="note">(one per submission)</span><p />
<p>' . $legend . '</p>
<select size="20" name="advocates[]">
';

            while ($advocate = ocsql_fetch_assoc($rr)) {
                print '<option value="' . $advocate['reviewerid'] . '">';
				if (!isset($_GET['s']) || ($_GET['s'] == "id")) {
					print padNumber($advocate['reviewerid'],$rsize) . ' - ' . safeHTMLstr($advocate['name']) . " (" . $advocate['acount'] . ")</option>\n";
				} elseif ($_GET['s'] == "reviews") {
					print padNumber($advocate['acount'],2) . ' - ' . safeHTMLstr($advocate['name']) . " - " . $advocate['reviewerid'] . "</option>\n";
				} else {
					print safeHTMLstr($advocate['name']) . " - " . $advocate['reviewerid'] . " (" . $advocate['acount'] . ")</option>\n";
				}
            } 

            print '
</select>
<br />
<span class="note">Tip: Click the ID, Name, or Submission links<br />above to re-sort this list (page will reload)</span>
</div>
<br style="clear: left;" />

<p><strong>Options:</strong></p>
<p><label><input type="checkbox" name="asrev" value="yes"> <strong><em>Assign as Reviewer</em></strong></label> &#8211; check box to also assign advocate as reviewer of submission</p>
<p><label><input type="checkbox" name="assignment_override" value="1"> <strong><em>Override Current Assignment</em></strong></label> &#8211; check box to change advocate if one already assigned</p>
';
			if ($OC_configAR['OC_allowConflictOverride']) {
				print '<p><label><input type="checkbox" name="conflict_override" value="1" /> <strong><em>Override Conflicts</em></strong></label> &#8211; check box to make assignments even if there is a conflict</p>';
			}
			print '
<p><label><input type="checkbox" name="notify" value="1"> <strong><em>Notify Advocate(s)</em></strong></label> &#8211; check box to notify advocate(s) that new assignments have been made</p>
<br />
<p><input type="submit" name="submit" value="Assign Advocates"></p>
</form><p />
';
        } 
    } 
} 

printFooter();

?>
