<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

// This program will generate the zone.php file used by OpenConf from
// the IANA Time Zone Database.  You should only need to run this program
// if you update the zoneinfo file.

// zoneinfo file; available from:
// http://www.iana.org/time-zones   (file name: tzdata###.tar.gz)
// NOTE: You will need to unarchive and extract the zone.tab from above.
// Note2: You may also use full path to a local system copy
$zoneinfoFile = 'zone.tab';

// OpenConf countries file
$oczoneFile = 'zones.php'; // output

if (is_file($zoneinfoFile)) {
	$file = file_get_contents($zoneinfoFile) or die('ERROR: Unable to read zoneinfo file');
}

if (preg_match_all("/\b[A-Z]{2}\s+[\d\+\-]+\s+([\w\/\-]+)\b/", $file, $matches)) {
	$zoneAR = array();
	foreach ($matches[1] as $m) {
		$zoneAR[] = $m;
	}
	sort($zoneAR);
	$newfile = '<?php

// OpenConf Zone List
//
// Update manually or use zones-update.php with (Olson) timezone (zone.tab) file
// NOTE: These time zones need to match the internal PHP ones

$OC_zoneAR = array(
	\'UTC\',
	\'' . implode("',\n\t'", $zoneAR) . '\'
);

';
	$fp = fopen($oczoneFile, 'w') or die('ERROR: Unable to open output file');
	fwrite($fp, $newfile);
	fclose($fp);
	print "Zone file updated\n";
} else {
	die('ERROR: zone.tab file format unknown');
}

?>