<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

// NOTE: This file should use mysqli_query vs. ocsql_query

require_once "install-include.php";

$e = '';
if (isset($_POST['submit']) && ($_POST['submit'] == "Save Info")) {
	// Attempt to connect with DB
	if (!preg_match("/^\d+$/", $_POST['dbport']) || !($dbtest = mysqli_connect($_POST['dbhost'], $_POST['dbuser'], stripslashes($_POST['dbpw']), '', (int)$_POST['dbport']))) {
		$e = 'Unable to connect with database using information below:<br />' . safeHTMLstr(mysqli_connect_error());
	} else {
		// Specify UTF-8 use for connection
		mysqli_query($dbtest, "SET NAMES 'utf8'");
		// Create DB?
		if (isset($_POST['dboptions']) && ($_POST['dboptions'] == 'createNload')) {
			if (preg_match("/^[\w-]+$/", $_POST['dbname'])) {
				$q = "CREATE DATABASE `" . $_POST['dbname'] . "` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci";
				if (!mysqli_query($dbtest, $q) && (mysqli_errno($dbtest) != 1007)) {  // 1007=exists
					$e = 'Unable to create database ' . safeHTMLstr($_POST['dbname']) . ". DB Error:<br />" . safeHTMLstr(mysqli_error($dbtest) . " (" . mysqli_errno($dbtest) . ")");
				}
			} else {
				$e = 'Database name limited to letters, numbers, hyphen, and underscore';
			}
		}
		if (empty($e)) {
		// Check prefix
			if (!empty($_POST['dbprefix']) && (!preg_match("/^[\w-]{0,40}$/",$_POST['dbprefix']))) {
				$e = 'Invalid table prefix; use up to 40 letters, numbers, and the underscore.';
			}
			// Attempt to access DB
			elseif (!mysqli_select_db($dbtest, $_POST['dbname'])) {
				$e = 'Unable to access database ' . safeHTMLstr($_POST['dbname']);
			}
			else {	// Save db info
				if (! $fp = fopen(OCC_LIB_DIR . 'config-sample.php', 'r')) {
					err('Config template file (lib/config-sample.php) does not exist.  Check that you have a full OpenConf distribution and click the Restart Install link above.', $hdr, $hdrfn, false);
				}
				if (!$optionFile = fread($fp, filesize(OCC_LIB_DIR . 'config-sample.php'))) {
					fclose($fp);
					err("Unable to read from config file", $hdr, $hdrfn, false);
				}
				fclose($fp);
				replaceConstantValue('OCC_SESSION_VAR_NAME', 'OPENCONF' . substr(oc_idGen(),3,4), $optionFile); // assign a (hopefully) unique session name in case of multiple installations
				replaceConstantValue('OCC_DB_USER', $_POST['dbuser'], $optionFile);
				replaceConstantValue('OCC_DB_PASSWORD', $_POST['dbpw'], $optionFile);
				replaceConstantValue('OCC_DB_HOST', $_POST['dbhost'], $optionFile);
				replaceConstantValue('OCC_DB_PORT', $_POST['dbport'], $optionFile);
				replaceConstantValue('OCC_DB_NAME', $_POST['dbname'], $optionFile);
				replaceConstantValue('OCC_DB_PREFIX', $_POST['dbprefix'], $optionFile);
				if (! $fp = fopen(OCC_CONFIG_FILE,'w')) {
					err('Config file (config.php) cannot be created or is not writeable.  Try creating a blank config.php file manually and ensure file permissions allow config.php to be written to by the server; then click the Restart Install link above.', $hdr, $hdrfn, false);
				}
				if (!fwrite($fp, $optionFile)) {
					fclose($fp);
					err("Unable to write to config file", $hdr, $hdrfn, false);
				}
				fclose($fp);
				
				// Load schema
				if ($dbfile = file_get_contents(OCC_LIB_DIR . "DB.sql")) {
					// create tables
					if (preg_match_all("/(CREATE [^;]+);/", $dbfile, $matches)) {
						foreach ($matches[1] as $m) {
							// add table prefix
							$m = preg_replace("/(CREATE TABLE `?)/", "$1" . slashQuote(stripslashes($_POST['dbprefix'])), $m);
							if (!mysqli_query($dbtest, $m)) {
								$e = "Error on loading schema -- " . safeHTMLstr(mysqli_error($dbtest)) . ".<br />Database may need to be reset";
								break;
							}
						}
					} else {
						err("No schema found in DB.sql file", $hdr, $hdrfn, false);
					}
					// insert data
					if (empty($e) && preg_match_all("/(INSERT [^;]+);/", $dbfile, $matches)) {
						foreach ($matches[1] as $m) {
							// add table prefix
							$m = preg_replace("/(INSERT INTO `?)/", "$1" . slashQuote(stripslashes($_POST['dbprefix'])), $m);
							if (!mysqli_query($dbtest, $m)) {
								$e = "Error on loading schema -- " . safeHTMLstr(mysqli_error($dbtest)) . ".<br />Database may need to be reset";
								break;
							}
						}
					}
				} else {
					$e = 'Unable to load schema from DB.sql.  Try loading it manually (see INSTALL instructions).';
				}
				if (empty($e)) {
					mysqli_close($dbtest);
					header("Location: install-account.php");
					exit;
				}
			}
		}
		mysqli_close($dbtest);
	}
	$dbuser = varValue('dbuser', $_POST);
	$dbname = varValue('dbname', $_POST);
	$dbhost = varValue('dbhost', $_POST);
	$dbport = varValue('dbport', $_POST, 3306);
	$dbprefix = varValue('dbprefix', $_POST);
} else {
	$dbuser = (defined('OCC_DB_USER') ? OCC_DB_USER : '');
	$dbname = (defined('OCC_DB_NAME') ? OCC_DB_NAME : '');
	$dbhost = (defined('OCC_DB_HOST') ? OCC_DB_HOST : '');
	$dbport = (defined('OCC_DB_PORT') ? OCC_DB_PORT : 3306);
	$dbprefix = (defined('OCC_DB_PREFIX') ? OCC_DB_PREFIX : '');
}

printHeader($hdr,$hdrfn);

print '<p style="text-align: center; font-weight: bold">Step 1 of 5: Enter Database Settings</p>';

if (!empty($e)) {
	print '<p style="text-align: center" class="warn">' . $e . '</p>';
}

print '
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<table border="0" cellspacing="0" cellpadding="5" style="margin: 0 auto">
<tr>
<td><strong><label for="dbuser">Database User:</label></strong></td>
<td><input name="dbuser" id="dbuser" size="30" maxlength="30" value="' . safeHTMLstr($dbuser) . '"></td>
</tr>
<tr>
<td><strong><label for="dbpwd">Database Password:</label></strong></td>
<td><input type="password" name="dbpw" id="dbpw" size="30" maxlength="100" value=""></td>
</tr>
<tr>
<td><strong><label for="dbhost">Database Hostname:</label></strong></td>
<td><input name="dbhost" id="dbhost" size="30" maxlength="100" value="' . safeHTMLstr($dbhost) . '"></td>
</tr>
<tr>
<td><strong><label for="dbport">Database Port:</label></strong></td>
<td><input name="dbport" id="dbport" size="30" maxlength="100" value="' . safeHTMLstr($dbport) . '"></td>
</tr>
<tr>
<td valign="top"><strong><label for="dbname">Database Name:</label></strong></td>
<td><input name="dbname" id="dbname" size="30" maxlength="64" value="' . safeHTMLstr($dbname) . '">
<br /><span class="note">valid characters: &nbsp; a-z &nbsp; 0-9 &nbsp; _ &nbsp; -</span>
</td>
</tr>
<tr>
<td valign="top"><strong><label for="dbprefix">Table Prefix:</label></strong></td>
<td><input name="dbprefix" id="dbprefix" size="30" maxlength="64" value="' . safeHTMLstr($dbprefix) . '">
<br /><span class="note">may be left blank</span>
</td>
</tr>
<tr><td>&nbsp;</td><td style="padding-top: 1.5em;">

<label><input type="radio" name="dboptions" value="createNload"';

if (!isset($_POST['dboptions']) || empty($_POST['dboptions']) || ($_POST['dboptions'] == 'createNload')) {
	print ' checked';
}

print '> Create Database and load schema</label>
<p />

<label><input type="radio" name="dboptions" value="load"';

if (isset($_POST['dboptions']) && ($_POST['dboptions'] == 'load')) {
	print ' checked';
}

print '> Load Schema only</label>
</td></tr>

<tr><td>&nbsp;</td><td style="padding-top: 1.5em"><input type="submit" name="submit" value="Save Info"></td></tr>
</table>

</form>

<p style="text-align: center" class="note">The above information is stored in config.php';

if (defined('OCC_DB_NAME') && (OCC_DB_NAME != '')) {
	print '.<br />If you already configured config.php and loaded the schema, you may <a href="install-account.php">skip to the next step</a>.';
}

print '</p>';

printFooter();
?>
