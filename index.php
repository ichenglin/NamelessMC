<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr9
 *
 *  License: MIT
 *
 *  Main index file
 */

// Uncomment to enable debugging
define('DEBUGGING', 1);

header('X-Frame-Options: SAMEORIGIN');

if ((!defined('DEBUGGING') || !DEBUGGING) && getenv('NAMELESS_DEBUGGING')) {
    define('DEBUGGING', 1);
}

if (defined('DEBUGGING') && DEBUGGING) {
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(-1);
}

// Ensure PHP version >= 7.4
if (PHP_VERSION_ID < 70400) {
    die('NamelessMC is not compatible with PHP versions older than 7.4');
}

// Start page load timer
$start = microtime(true);

// Definitions
const PATH = '/';
const ROOT_PATH = __DIR__;
$page = 'Home';

if (!ini_get('upload_tmp_dir')) {
    $tmp_dir = sys_get_temp_dir();
} else {
    $tmp_dir = ini_get('upload_tmp_dir');
}

if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) {
    ini_set('session.cookie_secure', 'On');
}

ini_set('session.cookie_httponly', 1);
ini_set('open_basedir', ROOT_PATH . PATH_SEPARATOR . $tmp_dir . PATH_SEPARATOR . '/proc/stat');

// Get the directory the user is trying to access
$directory = $_SERVER['REQUEST_URI'];
$directories = explode('/', $directory);
$lim = count($directories);

if (isset($_GET['route']) && $_GET['route'] == '/rewrite_test') {
    require_once('rewrite_test.php');
    die();
}

// Start initialising the page
require(ROOT_PATH . '/core/init.php');
