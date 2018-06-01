<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

print '
<fieldset>
<legend>' . oc_('CAPTCHA') . '</legend>
';

if ($GLOBALS['OC_configAR']['MOD_CAPTCHA_version'] == '1.0') {
	print '
<div class="fieldsetnote note">' . oc_('Enter the words or numbers within the image in the field beneath it.  If you cannot read them, click on the circular arrow (top) icon to see new ones, or on the speaker icon to have numbers read to you which you should then enter into the field.') . '</div>
<div class="field"><label for="recaptcha_response_field">' . oc_('CAPTCHA Check') . ':</label>
';
}

print '
<p /><span class="err">';

require_once('captcha-include.inc');

mod_captcha_displayCAPTCHA();

print '</span>
</fieldset>';