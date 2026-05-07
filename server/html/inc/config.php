<?php

if (!defined('FG_DIR')) die();

define('FG_SECKEY', '');

define('FG_DEVPASS', '');

require_once(FG_DIR . 'inc/Func.php');
require_once(FG_DIR . 'inc/Str.php');
require_once(FG_DIR . 'inc/DB.php');

$DB = new DB(array(
	'host' => 'localhost',
	'user' => 'root',
	'pass' => '',
	'name' => 'figaroo_home',
	'pref' => '',
));

require_once(FG_DIR . 'inc/Realplexor.php');

define('FG_CO2_LIM1', 800);
define('FG_CO2_LIM2', 1000);
