<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

if ($GLOBALS['OC_configAR']['MOD_CAPTCHA_version'] == '1.0') {
	require_once('recaptchalib.php');
}

// displays CAPTCHA
function mod_captcha_displayCAPTCHA() {
	if ($GLOBALS['OC_configAR']['MOD_CAPTCHA_version'] == '1.0') {
	
		print "
<script>
<!--
var RecaptchaOptions = {
    theme : 'clean'
 };
// -->
</script>
 ";
		echo recaptcha_get_html(
			OC_CAPTCHA_public_key, 
			NULL, 
			((isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) ? 'TRUE' : 'FALSE')
		);
		
	} else { // 2.0
	
		print '
<script src="https://www.google.com/recaptcha/api.js?hl=' . $GLOBALS['OC_locale'] . '" async defer></script>
<div class="g-recaptcha" data-sitekey="' . safeHTMLstr(OC_CAPTCHA_public_key) . '"></div>
<noscript>
  <div style="width: 302px; height: 352px;">
    <div style="width: 302px; height: 352px; position: relative;">
      <div style="width: 302px; height: 352px; position: absolute;">
        <iframe src="https://www.google.com/recaptcha/api/fallback?k=' . safeHTMLstr(OC_CAPTCHA_public_key) . '" frameborder="0" scrolling="no" style="width: 302px; height:352px; border-style: none;" title="CAPTCHA">
        </iframe>
      </div>
      <div style="width: 250px; height: 80px; position: absolute; border-style: none; bottom: 21px; left: 25px; margin: 0px; padding: 0px; right: 25px;">
        <textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response" style="width: 250px; height: 80px; border: 1px solid #c1c1c1; margin: 0px; padding: 0px; resize: none;" value="">
        </textarea>
      </div>
    </div>
  </div>
</noscript>
';

	}
}

// returns false if verifies OK, otherwise error string
function mod_captcha_verifyCAPTCHA() {
	$mod_captcha_err = '';

	if ($GLOBALS['OC_configAR']['MOD_CAPTCHA_version'] == '1.0') {
	
		if (!isset($_POST["recaptcha_challenge_field"]) || !isset($_POST["recaptcha_response_field"])) {
		  $mod_captcha_err = '1';
		} else {
			$recaptch_r = recaptcha_check_answer (OC_CAPTCHA_private_key,
											$_SERVER["REMOTE_ADDR"],
											$_POST["recaptcha_challenge_field"],
											$_POST["recaptcha_response_field"]);
			if ($recaptch_r->is_valid) {
				return(false);  // OK
			} else {
				$mod_captcha_err = $recaptch_r->error;
			}
		}
		
	} else { // 2.0
		if (!isset($_POST["g-recaptcha-response"]) || !isset($_POST["g-recaptcha-response"])) {
			$mod_captcha_err = 1;
		} else {
			$recaptchaURL = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode(OC_CAPTCHA_private_key) . '&response=' . urlencode($_POST['g-recaptcha-response']) . '&remoteip=' . urlencode($_SERVER['REMOTE_ADDR']);
			if ($response = file_get_contents($recaptchaURL)) {
				if ($responseAR = json_decode($response, true)) {
					if (trim($responseAR['success']) == true) {
						return(false);  // OK
					} elseif (isset($responseAR['error-codes'])) {
						$mod_captcha_err = (is_array($responseAR['error-codes']) ? implode(',', $responseAR['error-codes']) : $responseAR['error-codes']);
					} else {
						$mod_captcha_err = 3;
					}
				} else {
					$mod_captcha_err = 4;
				}
			} else {
				$mod_captcha_err = 5;
			}
		}
	}
	
	return(
		oc_('CAPTCHA verification failed') . 
		(empty($mod_captcha_err) ? '' : (' (' . $mod_captcha_err . ')'))
	);
}
