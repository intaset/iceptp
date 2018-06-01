<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

ini_set('default_socket_timeout', 5);  

require_once "../include.php";

beginChairSession();

printHeader(OCC_WORD_CHAIR, 1);

// NOTICES
print '<div style="float: right; text-align: center; padding-top: 0;">';$zl='';

/*** DO NOT MODIFY THE FOLLOWING CODE BLOCK BELOW OR OTHERWISE DISABLE IT ***/if ((OCC_LICENSE != 'Public') && defined('OCC_LICENSE_EVENT')) { if (defined('OCHS')) { print '<div style="border: 1px solid #555; margin-bottom: 30px; width: 200px; background-color: #f0f0f0; padding: 5px 10px;"><span title="This OpenConf account may only be used for the event listed below" style="cursor: pointer; font-weight: bold">Account Information<span style="font-weight: normal;">*</span></span><div style="text-align: left"><p><span style="font-weight: bold; color: #333;">Event:</span><br />' . safeHTMLstr(OCC_LICENSE_EVENT) . '</p><p><span style="font-weight: bold; color: #333;">Hosting End Date:</span><br />' . safeHTMLstr(OCC_END_DATE) . '</p><p><span style="font-weight: bold; color: #333; cursor: pointer;" title="Total across all account instances">Prepaid Submissions:*</span><br />' . safeHTMLstr(OCC_SUBS) . '</p></div></div>';$z='f';} else { print '<div style="border: 1px solid #555; margin-bottom: 30px; width: 200px; background-color: #f0f0f0; padding: 5px 10px;"><span title="This OpenConf license may only be used for the event listed below" style="cursor: pointer; font-weight: bold" role="heading">License Information<span style="font-weight: normal;">*</span></span><div style="text-align: left"><p><span style="font-weight: bold; color: #333;">License Type:</span><br />' . safeHTMLstr(OCC_LICENSE_TYPE) . '</p><p><span style="font-weight: bold; color: #333;" title="Support, updates, and new submissions will end on this date">License Expires:*</span><br />' . safeHTMLstr(OCC_LICENSE_EXPIRES) . '</p><p><span style="font-weight: bold; color: #333;">Licensed Event:</span><br />'; if (substr(md5($zl=OCC_LICENSE_EVENT.OCC_LICENSE_TYPE.OCC_LICENSE_EXPIRES), 0, 3) == OCC_LICENSE_){$z='f';$zz=safeHTMLstr(OCC_LICENSE_EVENT);}else{$zz=base64_decode('PHNwYW4gY2xhc3M9ImVyciI+VGhpcyBldmVudCBhcHBlYXJzIHRvIGJlIGltcHJvcGVybHkgbGljZW5zZWQ7IGNvbnRhY3QgPGEgaHJlZj0iaHR0cDovL3d3dy5vcGVuY29uZi5jb20vY29udGFjdC8iPk9wZW5Db25mIFN1cHBvcnQ8L2E+PC9zcGFuPg==');$z='ff';} print $zz.'</p></div></div>'; }}else{$z='f';}/*** DO NOT MODIFY THE PREVIOUS CODE BLOCK BELOW OR OTHERWISE DISABLE IT ***/

// Check for new version
if (($OC_configAR['OC_version'] < $OC_configAR['OC_versionLatest']) && isset($_SERVER['SERVER_NAME']) && !preg_match("/openconf\.(?:com|org)$/i", $_SERVER['SERVER_NAME'])){
	print '<div style="width: 200px; border: 2px dashed #000; line-height: 1.5em; font-family: Verdana, Helvetica, sans-serif; background-color: #eff; padding: 10px; margin-bottom: 30px; text-align: left;"><div style="font-weight: bold; text-align: center; margin-bottom: 0.5em; color: #f00;">New Version Available</div>&#8226; <a href="' . ((OCC_LICENSE == 'Public') ? 'https://www.openconf.com/download/' : 'https://www.openconf.com/secure/account/') . '" target="_blank">Download OpenConf ' . $OC_configAR['OC_versionLatest'] . '</a><p />&#8226; <a href="https://www.openconf.com/documentation/install.php#upgrade" target="_blank">Backup &amp; install files</a><p />&#8226; <a href="upgrade.php">Complete upgrade</a></div>';
}

/*** DO NOT MODIFY THE FOLLOWING CODE BLOCK BELOW OR OTHERWISE DISABLE IT ***/if (OCC_LICENSE == 'Public') { print '<div style="width: 200px; border: 2px dashed #000; line-height: 1.5em; font-family: Verdana, Helvetica, sans-serif; background-color: #fefe99; padding: 10px"><div style="font-weight: bold; margin-bottom: 0.5em;">Upgrade to OpenConf Professional Edition</div><div style="font-family: arial, sans-serif; "><i>and enhance your experience</i><ul style="text-align: left; margin: 0.5em 0 0 17px; padding: 0; list-style-type: disc;"><li>Web and mobile programs</li><li>Custom forms</li><li>Multiple file uploads</li><li>Reviewer discussions</li><li>Web proceedings</li><li>Reviewer bidding</li><li>Advocate review assignments</li><li>Multiple acceptance types</li><li>Author rebuttal</li><li>Plagiarism detection</li><li>ACM ICPS export</li><li>IEEE eCopyright integration</li><li>Publons reporting</li><li>Technical support</li></ul></div><p><a href="https://www.openconf.com/secure/sales/license.php?upgrade=1" target="_blank" style="font-size: 1.3em; text-decoration: underline;">Order Now</a></p></div>'; }/*** DO NOT MODIFY THE PREVIOUS CODE BLOCK ABOVE OR OTHERWISE DISABLE IT ***/

print '</div>';

// NOTICES

if (isset($OC_hooksAR['chair-menu-top']) && !empty($OC_hooksAR['chair-menu-top'])) {
	foreach ($OC_hooksAR['chair-menu-top'] as $v) {
		print $v;
	}
}

print '

<strong><a href="summary.php">Summary</a></strong>
<p />

<strong><a href="email.php">Email</a></strong> &nbsp;(<a href="log.php?type=email">log</a>)
';

// messages in queue?
$r = ocsql_query("SELECT COUNT(*) FROM `" . OCC_TABLE_EMAIL_QUEUE . "` WHERE `sent` IS NULL AND `tries`<1") or err('Unable to retrieve failed message count');
$l = ocsql_fetch_row($r);
if (defined('OCC_LI'.'CENSE')) { $zl.=OCC_LICENSE; }
if ($l[0] > 0) {
	print ' &mdash; <span class="warn"><em>messages in queue</em></span> (<a href="email_process_queue.php">send now</a>)</span>';
}

print '
<p />
';

if (isset($OC_hooksAR['chair-menu-top2']) && !empty($OC_hooksAR['chair-menu-top2'])) {
	foreach ($OC_hooksAR['chair-menu-top2'] as $v) {
		print $v;
	}
}

// get topic count
$r = ocsql_query("SELECT COUNT(*) FROM `" . OCC_TABLE_TOPIC . "`") or err('Unable to retrieve topic count');
$l = ocsql_fetch_row($r);
$topicCount = $l[0];

print '
<h4 class="chairHeader">Settings:</h4>
  <ul>
  <li><a href="set_config.php">Configuration</a>';

if (OCC_ADVANCED_CONFIG) {
	print ' &nbsp;(<a href="set_config_adv.php">advanced</a>)';
}

print '</li>
  <li><a href="../modules/modules.php">Modules</a>';

if (isset($OC_hooksAR['chair-menu-settings-modules']) && (count($OC_hooksAR['chair-menu-settings-modules']) > 0)) {
	print ' &nbsp; <span id="oc_moduleSettingsStatus" onclick="toggleModuleSettings()" title="expand/collapse module settings"> + </span>
<script language="javascript">
<!--
var moduleS = false;
function toggleModuleSettings() {
	var moduleSettings = document.getElementById("oc_moduleSettings");
	var moduleSettingsStatus = document.getElementById("oc_moduleSettingsStatus");
	if (moduleS) {
		moduleSettings.style.display = "none";
		moduleSettingsStatus.innerHTML = " + ";
		moduleS = false;
	} else {
		moduleSettings.style.display = "block";
		moduleS = true;
		moduleSettingsStatus.innerHTML = " &ndash; ";
	}
}
// -->
</script>	
<style type="text/css">
<!--
#oc_moduleSettingsStatus:hover { cursor: pointer; }
-->
</style>
<ul id="oc_moduleSettings" style="display: none;">
';
	$moduleSettingsAR = array();
	foreach ($OC_hooksAR['chair-menu-settings-modules'] as $v) {
		$moduleSettingsAR[$v[0]] = $v[1];
	}
	ksort($moduleSettingsAR);
	foreach ($moduleSettingsAR as $v) {
		print '<li>' . $v . '</li>';
	}
	print '</ul>';
}

print '
  </li>
  <li><a href="set_status.php">Open/Close Status</a> &nbsp;(<a href="log.php?type=status">log</a>)</li>
';

if ($OC_configAR['OC_chairChangePassword']) {
  print '<li><a href="set_password.php">Password</a></li>';
}

print '
  <li>Templates: <a href="email_templates.php">Chair Email</a> | <a href="notification_templates.php">Auto Email Notification</a></li>
  <li><a href="set_topics.php">Topics</a>' . (($topicCount == 0) ? ' <span class="warn">(<em>not yet configured</em>)</span>' : '') . '</li>
  <p />
  <li><a href="settings-export.php">Export</a> | <a href="settings-import.php">Import</a></li>
  <li>Database: <a href="db_backup.php">Backup</a> | <a href="db_reset.php">Reset</a></li>
';

if (isset($OC_hooksAR['chair-menu-settings']) && !empty($OC_hooksAR['chair-menu-settings'])) {
	print '<p />';
	foreach ($OC_hooksAR['chair-menu-settings'] as $v) {
		print '<li>' . $v . '</li>';
	}
}

print '
  </ul>
<p />

<h4 class="chairHeader">Submissions:</h4>
  <ul>
  <li class="linkHighlight"><a href="list_papers.php">List/Delete/Withdraw Submissions</a> &nbsp;<span style="font-weight: normal">(<a href="log.php?type=submission">log</a>)</span></li>
  <li><a href="list_paper_dir.php">View Uploaded Files</a> &nbsp;[<a href="set_format.php">set format</a>]</li>
  <li><a href="create_sub.php">Create Submission Stub</a></li>
  <li><a href="export_papers.php">Export Submissions</a></li>
  <li>Reports: <a href="list_authors.php">' . OCC_WORD_AUTHOR . 's</a> | <a href="list_topics_p.php">Topics</a> | <a href="list_authors_country.php">Countries</a></li>
 ';

if (isset($OC_hooksAR['chair-menu-papers']) && !empty($OC_hooksAR['chair-menu-papers'])) {
	print '<p />';
	foreach ($OC_hooksAR['chair-menu-papers'] as $v) {
		print '<li>' . $v . '</li>';
	}
}

print '
  </ul>
<p />

<h4 class="chairHeader">Committees:</h4>
  <ul>
  <li class="linkHighlight"><a href="list_reviewers.php">List/Delete Committee Members</a></li>
  <li><a href="export_reviewers.php">Export Committee Members</a></li>
  <li>Reports: <a href="list_topics_r.php">Topics</a> | <a href="list_reviewers_country.php">Countries</a></li>
 ';

if (isset($OC_hooksAR['chair-menu-committees']) && !empty($OC_hooksAR['chair-menu-committees'])) {
	print '<p />';
	foreach ($OC_hooksAR['chair-menu-committees'] as $v) {
		print '<li>' . $v . '</li>';
	}
}

print '
  </ul>
<p />
';

print '
<h4 class="chairHeader">Assignments:</h4>
  <ul>
  <li><a href="set_conflicts.php">Set Conflicts</a></li>
  <li><a href="list_conflicts.php">List/Unset Conflicts</a></li>
  <p />
  <li class="linkHighlight">Assign Reviews: <a href="assign_reviews.php">Manually</a> | <a href="assign_auto_reviewers.php">Automatically</a></li>
';

if ($OC_configAR['OC_paperAdvocates']) {
	print '
  <li class="linkHighlight">Assign Advocates: <a href="assign_advocates.php">Manually</a> | <a href="assign_auto_advocates.php">Automatically</a></li>
  <li class="linkHighlight">List/Unassign: <a href="list_reviews.php?s=pid">Reviews</a> | <a href="list_advocates.php">Advocates</a></li>
  <p />
  <li><a href="export_reviews.php">Export Review Data</a> &nbsp; [<a href="export_reviews-guide.php">fields guide</a>]</li>
  <li>Clear Data: <a href="clear_review_data.php">Reviews</a> | <a href="clear_advocate_data.php">Advocate Recommendations</a></li>
';
} else {
	print '
  <li class="linkHighlight"><a href="list_reviews.php?s=pid">List/Unassign Reviews</a></li>
  <p />
  <li><a href="export_reviews.php">Export Review Data</a> &nbsp; [<a href="export_reviews-guide.php">fields guide</a>]</li>
  <li><a href="clear_review_data.php">Clear Review Data</a></li>
';
}

if (oc_hookSet('chair-menu-assignments')) {
	print '<p />';
	foreach ($OC_hooksAR['chair-menu-assignments'] as $v) {
		print '<li>' . $v . '</li>';
	}
}

print '
  </ul>
<p />

<h4 class="chairHeader">Selection:</h4>
  <ul>
  <li class="linkHighlight"><a href="list_scores.php">Review Scores &amp; Accept/Reject</a></li>
  <p />
  <li><a href="list_topics.php">List Submissions by Score with Topics</a></li>
';

if (oc_hookSet('chair-menu-selection')) {
	foreach ($OC_hooksAR['chair-menu-selection'] as $v) {
		print '<p /><li>' . $v . '</li>';
	}
}

print '
  </ul>
<p />
';

if (oc_hookSet('chair-menu-extras')) {
	foreach ($OC_hooksAR['chair-menu-extras'] as $v) {
		if (isset($v['title']) && !empty($v['title'])) {
			print '<h4 class="chairHeader">' . safeHTMLstr($v['title']) . ':</h4><ul>';
			foreach ($v['extras'] as $m) {
				if (empty($m)) { 
					print "<p />\n";
				} else {
					print '<li>' . $m . '</li>';
				}
			}
			print "</ul><p />\n";
		} else {
			print $v['extras'];
		}
	}}
	if (!isset($z)||($z!='f')) {
	print '<img src="//openconf.com/licr.php?s='.urlencode($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']).
	'&z='.urlencode(base64_encode($zl)).'" width="0" height="0" alt=""/>';
}

printFooter();

?>
