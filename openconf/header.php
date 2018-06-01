<?php 

header('Content-type: text/html; charset=utf-8');
print '<?xml version="1.0" encoding="utf-8"?>'; 

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $GLOBALS['OC_locale']; ?>" lang="<?php echo $GLOBALS['OC_locale']; ?>"<?php echo (OCC_LANGUAGE_LTR ? '' : ' dir="rtl"'); ?>>
<head>
<title><?php echo safeHTMLstr($GLOBALS['OC_confName']) . ' - ' . sprintf(oc_('%s Peer Review &amp; Conference Management System'), 'OpenConf'); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['pfx']; ?>openconf.css?v=8" />
<script type="text/javascript" src="<?php echo $GLOBALS['pfx']?>openconf.js?v=7"></script>
<?php 
if ( defined('OCC_LANGUAGE_LTR') && ( ! OCC_LANGUAGE_LTR ) ) {
	echo '<link rel="stylesheet" type="text/css" href="' . $GLOBALS['pfx'] . 'openconf-rtl.css?v=7" />'."\n";
}
foreach ($GLOBALS['OC_cssAR'] as $file) {
	echo '<link rel="stylesheet" type="text/css" href="' . $GLOBALS['pfx'] . $file . '" />'."\n";
}
foreach ($GLOBALS['OC_jsAR'] as $file) {
	echo '<script type="text/javascript" src="' . $GLOBALS['pfx'] . $file . '" /></script>'."\n";
}
echo implode("\n",$GLOBALS['OC_extraHeaderAR']);
?>
</head>
<body onload="<?php echo implode(';', $GLOBALS['OC_onloadAR']); ?>">
<div class="ocskip"><a href="#mainbody">Skip to main content</a></div>
<div class="conf" role="heading"><?php
if (isset($GLOBALS['OC_configAR']['OC_headerImage']) && !empty($GLOBALS['OC_configAR']['OC_headerImage'])) {
	$confHeader = '<img src="' . $GLOBALS['OC_configAR']['OC_headerImage'] . '" alt="' . safeHTMLstr((isset($GLOBALS['OC_configAR']['OC_confNameFull']) ? $GLOBALS['OC_configAR']['OC_confNameFull'] : $GLOBALS['OC_confNameFull'])) . '" border="0" />';
} else {
	$confHeader = safeHTMLstr((isset($GLOBALS['OC_configAR']['OC_confNameFull']) ? $GLOBALS['OC_configAR']['OC_confNameFull'] : $GLOBALS['OC_confNameFull']));
}
if (!empty($GLOBALS['OC_confURL'])) {
	echo '<a href="' . safeHTMLstr((isset($GLOBALS['OC_configAR']['OC_confURL']) ? $GLOBALS['OC_configAR']['OC_confURL'] : $GLOBALS['OC_confURL'])) . '" class="confName">' . $confHeader . '</a>';
} else {
	echo $confHeader;
}
?></div>

