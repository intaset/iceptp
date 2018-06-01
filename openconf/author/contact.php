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

printHeader(oc_('Email Chair'), 3);

if (isset($_POST['ocaction']) && ($_POST['ocaction'] == "Send Email")) {
	$err = '';
	if (isset($_POST['email']) && !empty($_POST['email'])) {
		$err .= '<li>' . oc_('Fields not correctly filled out') . '</li>';
	}
	if (!isset($_POST['name']) || !preg_match("/\p{L}/u", $_POST['name']) || preg_match("/[\r\n]/", $_POST['name'])) {
		$err .= '<li>' . oc_('Name field empty or invalid') . '</li>';
	}
	if (!isset($_POST['liame']) || !validEmail(($_POST['liame'] = trim($_POST['liame']))) || preg_match("/[\r\n]/", $_POST['liame'])) {
		$err .= '<li>' . oc_('Email field empty or invalid') . '</li>';
	}
	if (!isset($_POST['subject']) || !preg_match("/\p{L}/u", $_POST['subject']) || preg_match("/[\r\n]/", $_POST['subject'])) {
		$err .= '<li>' . oc_('Subject field empty or invalid') . '</li>';
	}
	if (!isset($_POST['message']) || !preg_match("/\p{L}/u", $_POST['message'])) {
		$err .= '<li>' . oc_('Message field empty or invalid') . '</li>';
	}

	if (oc_hookSet('author-contact-validate')) {
		foreach ($GLOBALS['OC_hooksAR']['author-contact-validate'] as $hook) {
			require_once $hook;
		}
	}
	
	if (empty($err)) {
		$hdr = 'From: "' . $_POST['name'] . '" <' . $_POST['liame'] . ">\r\nReply-To: " . $_POST['liame'];
		if (! oc_mail($OC_configAR['OC_pcemail'], $_POST['subject'], $_POST['message'], $hdr)) {
			err(oc_('An error occurred sending out the email.'));
		} else {
			print '<div class="note2">' . oc_('Your message has been sent.') . '</div>';
			printFooter();
			exit;
		}
	}
}

print '
<style type="text/css">
<!--
#recaptcha_response_field { background-color: #eee; }
-->
</style>
<form method="post" action="' . $_SERVER['PHP_SELF'] . '">
<input type="hidden" name="ocaction" value="Send Email" />
<input type="hidden" name="email" value="" />
<table border="0" style="width: 500px; margin: 0 auto" cellspacing="4">
';

if (!empty($err)) {
	print '<tr><td>&nbsp;</td><td class="warn">' . oc_('Please correct the following:') . '<ul>' . $err . '</ul></br>';
}

print '
<tr><td><strong><label for="name">' . oc_('Name') . ':</label></strong></td><td><input size="60" name="name" id="name" class="ocinput" style="width: 400px" value="' . safeHTMLstr(varValue('name', $_POST)) . '"></td></tr>
<tr><td><strong><label for="liame">' . 
//T: Email Address
oc_('Email') . ':</label></strong></td><td><input size="60" name="liame" id="liame" class="ocinput" style="width: 400px" value="' . safeHTMLstr(varValue('liame', $_POST)) . '"></td></tr>
<tr><td><strong><label for="subject">' . oc_('Subject') . ':</label></strong></td><td><input size="60" name="subject" id="subject" class="ocinput" style="width: 400px" value="' . 
//T: Email Subject Line
safeHTMLstr(varValue('subject', $_POST)) . '"></td></tr>
<tr><td valign="top"><strong><label for="message">' . oc_('Message') . ':</label></strong></td><td><textarea rows="5" cols="60" class="ocinput" style="width: 400px" name="message" id="message">' . 
//T: Email Message
safeHTMLstr(varValue('message', $_POST)) . '</textarea></td></tr>
';

if (oc_hookSet('author-contact-fields')) {
	foreach ($GLOBALS['OC_hooksAR']['author-contact-fields'] as $hook) {
		require_once $hook;
	}
}

print '
<tr><th align="center" colspan=2><br><input type="submit" name="submit" value="' . oc_('Send Email') . '"></th></tr>
</table>
</form>
<p>
';

printFooter();

?>
