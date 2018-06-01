## +----------------------------------------------------------------------+
## | OpenConf                                                             |
## +----------------------------------------------------------------------+
## | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
## +----------------------------------------------------------------------+
## | This source file is subject to the OpenConf License, available on    |
## | the OpenConf web site: www.OpenConf.com                              |
## +----------------------------------------------------------------------+

## NOTE: This file cannot contain a semi-colon (;) except at the end of a
## SQL statement.

# --------------------------------------------------------

INSERT INTO `config` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('captcha', 'MOD_CAPTCHA_private_key', '', 'Private Key', 'reCAPTCHA private key', 0);

INSERT INTO `config` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('captcha', 'MOD_CAPTCHA_public_key', '', 'Public Key', 'reCAPTCHA public key', 0);

INSERT INTO `config` (`module`, `setting`, `value`, `name`, `description`, `parse`) VALUES ('captcha', 'MOD_CAPTCHA_version', '2.0', 'Version Number', 'reCAPTCHA API version number', 0);
