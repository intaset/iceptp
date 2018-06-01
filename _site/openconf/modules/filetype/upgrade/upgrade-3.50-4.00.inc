<?php

// +----------------------------------------------------------------------+
// | OpenConf                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2017 Zakon Group LLC.  All Rights Reserved.       |
// +----------------------------------------------------------------------+
// | This source file is subject to the OpenConf License, available on    |
// | the OpenConf web site: www.OpenConf.com                              |
// +----------------------------------------------------------------------+

ocsql_query("DELETE FROM `" . OCC_TABLE_CONFIG . "` WHERE `module`='filetype' AND `value`='MOD_FILETYPE_allow_officeopenxml' LIMIT 1");

