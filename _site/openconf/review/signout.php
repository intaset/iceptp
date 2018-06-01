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

unset($_SESSION[OCC_SESSION_VAR_NAME]['acusername']);
unset($_SESSION[OCC_SESSION_VAR_NAME]['acreviewerid']);
session_write_close();

header("Location: ../");

?>
