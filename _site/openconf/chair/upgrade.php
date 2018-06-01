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

// Upgrade paths (e.g., 3.00 => 3.10 -- requires a file openconf/upgrade/upgrade-3.0-3.1.inc)
$OC_upgradeAR = array(
	'3.0'	=> '3.10',
	'3.10'	=> '3.20',
	'3.20'	=> '3.21',
	'3.21'	=> '3.30',
	'3.30'	=> '3.40',
	'3.40'	=> '3.41',
	'3.41'	=> '3.42',
	'3.42'	=> '3.50',
	'3.50'	=> '4.00',
	'4.00'	=> '4.01',
	'4.01'	=> '4.02',
	'4.02'	=> '4.10',
	'4.10'	=> '4.11',
	'4.11'	=> '4.12',
	'4.12'	=> '5.00',
	'5.00'	=> '5.10',
	'5.10'	=> '5.20',
	'5.20'	=> '5.30',
	'5.30'	=> '5.31',
	'5.31'	=> '6.00',
	'6.00'	=> '6.01',
	'6.01'	=> '6.10',
	'6.10'	=> '6.20',
	'6.20'	=> '6.30',
	'6.30'	=> '6.40',
	'6.40'	=> '6.50',
	'6.50'	=> '6.60',
	'6.60'	=> '6.70',
	'6.70'	=> '6.71',
	'6.71'	=> 'done'
);

beginChairSession();

printHeader("OpenConf Upgrade", 1);

if (!isset($OC_upgradeAR[$OC_configAR['OC_version']])) {	// valid current version?
	warn('Current version unknown or no upgrade available.');
} elseif ($OC_upgradeAR[$OC_configAR['OC_version']] == 'done') {	// done?
	print '<p>The upgrade process appears to have been previously completed.</p>';
} elseif (isset($_POST['a']) && ($_POST['a'] == 'u')) {	// ready to upgrade?
	// Check for valid submission
	if (!validToken('chair')) {
		warn('Invalid submission');
	}
	// Upgrade
	print '
<p>Upgrading from ' . $OC_configAR['OC_version'] . ' to ' . $OC_upgradeAR[$OC_configAR['OC_version']] . ' ...</p>
';
	$upgradeFile = '../upgrade/upgrade-' . $OC_configAR['OC_version'] . '-' . $OC_upgradeAR[$OC_configAR['OC_version']] . '.inc';
	if (is_file($upgradeFile)) {
		require_once($upgradeFile);
		// any modules to upgrade?
		if (isset($upgradeModulesAR)) {
			foreach ($upgradeModulesAR as $module) {
				// only upgrade if module installed
				if (oc_module_installed($module)) {
					$moduleUpgradeFile = '../modules/' . $module . '/upgrade/' . $upgradeFile;
					if (is_file($moduleUpgradeFile)) {
						require_once($moduleUpgradeFile);
					}
				}
			}
		}
		print '<p>Done</p>';
		$OC_configAR['OC_version'] = $OC_upgradeAR[$OC_configAR['OC_version']];
		if ($OC_upgradeAR[$OC_configAR['OC_version']] == 'done') {	// done?
			print '
<p>The upgrade process has completed.  You may delete the <em>upgrade</em> directory.</p>
<p><a href="./">Proceed to the main ' . OCC_WORD_CHAIR . ' Page</a></p>
<p style="text-align: center"><img src="//www.openconf.com/images/openconf-install.gif?u=1" alt="OpenConf logo" title="OpenConf" /></p>
';
		} else {	// keep going ...
			print '
<form method="post" action="upgrade.php">
<input type="hidden" name="a" value="u" />
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />
<p><input type="submit" name="submit" value="Continue with Upgrade" /></p>
</form>
';
		}
	} else {
		warn('The upgrade file ' . $upgradeFile . ' appears to be missing');
	}
} else {	// first script call
	print '
<p>This upgrade will take you through updating your OpenConf installation.  Depending on whether you have kept up with OpenConf upgrades, you may have to go through multiple upgrade steps.  Click (once) on the <em>Upgrade</em> buttons that are displayed until you are informed that the upgrade has been completed.</p>

<form method="post" action="upgrade.php">
<input type="hidden" name="a" value="u" />
<input type="hidden" name="token" value="' . $_SESSION[OCC_SESSION_VAR_NAME]['chairtoken'] . '" />
<p><input type="submit" name="submit" value="Begin Upgrade" /></p>
</form>
';
}

printFooter();

?>
