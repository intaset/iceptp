<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

$hdr = 'Export Settings';
$hdrfn = 1;

$excludeSettingsAR = array('OC_chair_pwd', 'OC_chair_uname', 'OC_chairChangePassword', 'OC_chairFailedSignIn', 'OC_confirmmail', 'OC_confName', 'OC_confNameFull', 'OC_confURL', 'OC_pcemail', 'OC_version', 'OC_versionLatest');

require_once '../include.php';

beginChairSession();

// Module pre hook
if (oc_hookSet('settings-export-pre')) {
	foreach ($OC_hooksAR['settings-export-pre'] as $f) {
		require_once $f;
	}
}

if (isset($_POST['submit']) && ($_POST['submit'] == 'Export Settings')) {
	// Check for valid submission
	if (!validToken('chair')) {
			warn('Invalid submission');
	}

	$settings = array(
		'options' => array(),
		'configuration' => array(),
		'license' => ((OCC_LICENSE == 'Public') ? 'Public' : 'PlusPro'),
		'version' => $GLOBALS['OC_configAR']['OC_version'],
		'modules' => array()
	);
	
	// Module prep
	if (oc_hookSet('settings-export-prep')) {
		foreach ($OC_hooksAR['settings-export-prep'] as $f) {
			require_once $f;
		}
	}
	
	// Check for selected settings
	if (!isset($_POST['settings']) || (!is_array($_POST['settings'])) || (count($_POST['settings']) == 0)) {
		warn('No settings selected', $hdr, $hdrfn);
		exit;
	}

	// Configuration settings
	$settings['options'][] = 'configuration';
	$r = ocsql_query("SELECT `module`, `setting`, `value` FROM `" . OCC_TABLE_CONFIG . "` ORDER BY `module`, `setting`");
	while ($l = ocsql_fetch_assoc($r)) {
		if (((in_array('configuration', $_POST['settings']) && ($l['module'] == 'OC')) || isset($settings['modules'][$l['module']]))
			&& !in_array($l['setting'], $excludeSettingsAR)
		) {
			$settings['configuration'][$l['module'] . ':' . $l['setting']] = $l['value'];
		}
	}

	// Topics
	if (in_array('topics', $_POST['settings'])) {
		$settings['options'][] = 'topics';
		$r = ocsql_query("SELECT * FROM `" . OCC_TABLE_TOPIC . "` ORDER BY `topicid`");
		$settings['topics'] = array();
		while ($l = ocsql_fetch_assoc($r)) {
			$settings['topics'][$l['topicid']] = array(
				'topicname' => $l['topicname'],
				'short' => $l['short']
			);
		}
	}

	// Reviewers
	if (in_array('reviewers', $_POST['settings'])) {
		$settings['options'][] = 'reviewers';
		$r = ocsql_query("SELECT * FROM `" . OCC_TABLE_REVIEWER . "` ORDER BY `reviewerid`");
		$settings['reviewers'] = array();
		while ($l = ocsql_fetch_assoc($r)) {
			foreach ($l as $k => $v) {
				$settings['reviewers'][$l['reviewerid']][$k] = $v;
			}
		}
		
		// Reviewer Topics
		if (in_array('topics', $_POST['settings'])) {
			$settings['options'][] = 'reviewertopics';
			$r = ocsql_query("SELECT * FROM `" . OCC_TABLE_REVIEWERTOPIC . "` ORDER BY `reviewerid`, `topicid`");
			$settings['reviewertopics'] = array();
			while ($l = ocsql_fetch_assoc($r)) {
				if (!isset($settings['reviewertopics'][$l['reviewerid']])) {
					$settings['reviewertopics'][$l['reviewerid']] = array($l['topicid']);
				} else {
					$settings['reviewertopics'][$l['reviewerid']][] = $l['topicid'];
				}
			}
		}
	}

	// Templates
	if (in_array('templates', $_POST['settings'])) {
		$settings['options'][] = 'templates';
		$r = ocsql_query("SELECT * FROM `" . OCC_TABLE_TEMPLATE . "`");
		$settings['templates'] = array();
		while ($l = ocsql_fetch_assoc($r)) {
			foreach ($l as $k => $v) {
				$settings['templates'][$l['templateid']][$k] = $v;
			}
		}
	}
	
	// Module settings
	if (oc_hookSet('settings-export-process')) {
		foreach ($OC_hooksAR['settings-export-process'] as $f) {
			require_once $f;
		}
	}

	// Output file
	oc_sendNoCacheHeaders();
	$fileName = 'openconf-settings';
	if (preg_match("/^\w+$/", $GLOBALS['OC_configAR']['OC_confName'])) {
        $fileName .= '-' . $GLOBALS['OC_configAR']['OC_confName'];
	}
	$fileName .= '.oc';
	header('Content-Type: application/binary');
	header('Content-Disposition: attachment; filename="' . $fileName . '"');
	print json_encode($settings);
	exit;	
}

printHeader($hdr, $hdrfn);

print '
<p>In order to save your settings for use in another OpenConf installation, select what you would like exported below, then click the <i>Export Settings</i> button.  A file dialog box will open up for you to save the settings file on your computer.  When importing the settings, the same modules for which settings are exported must be pre-installed.</p>

<script language="javascript" type="text/javascript">
<!--
function checkAllBoxes() {
	var boxObj = document.getElementsByName(\'settings[]\');
	for (var i=0; i<boxObj.length; i++) {
			boxObj[i].checked = true;
	}
}
document.write(\'<p><a href="#" onclick="checkAllBoxes(); return false;" style="margin-left: 25px; cursor: pointer; padding: 1px 3px; background-color: #eee; color: #00f; text-decoration: underline;">check all</a></p>\');
// -->
</script>

<form method="post" action="settings-export.php">
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />

<label><input type="checkbox" name="settings[]" value="configuration" checked /> Configuration</label><br />
<p />

<label><input type="checkbox" name="settings[]" value="topics" /> Topics</label><br />
<label><input type="checkbox" name="settings[]" value="reviewers" /> Reviewers</label><br />
<label title="All templates will be included in the export; however for module-specific templates, only those of installed modules will be imported."><input type="checkbox" name="settings[]" value="templates" /> Templates*</label><br />
';

// Module settings
if (oc_hookSet('settings-export-options')) {
        foreach ($OC_hooksAR['settings-export-options'] as $f) {
                require_once $f;
        }
}

print '
<p />

<input type="submit" name="submit" value="Export Settings" />
</form>
';


printFooter();
?>
