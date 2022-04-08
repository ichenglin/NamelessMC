<?php
/*
 *  Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr9
 *
 *  License: MIT
 *
 *  Initialisation file
 */

require_once ROOT_PATH . '/vendor/autoload.php';

require_once ROOT_PATH . '/core/autoload.php';

// Nameless error handling
set_exception_handler([ErrorHandler::class, 'catchException']);
// catchError() used for throw_error or any exceptions which may be missed by catchException()
set_error_handler([ErrorHandler::class, 'catchError']);
register_shutdown_function([ErrorHandler::class, 'catchShutdownError']);

session_start();

// Page variable must be set
if (!isset($page)) {
    die('$page variable is unset. Cannot continue.');
}

if (!file_exists(ROOT_PATH . '/core/config.php')) {
    if (is_writable(ROOT_PATH . '/core')) {
        fopen(ROOT_PATH . '/core/config.php', 'w');
    } else {
        die('Your <strong>/core</strong> directory is not writable, please check your file permissions.');
    }
}

if (!file_exists(ROOT_PATH . '/cache/templates_c')) {
    try {
        mkdir(ROOT_PATH . '/cache/templates_c', 0777, true);
    } catch (Exception $e) {
        die('Unable to create <strong>/cache</strong> directories, please check your file permissions.');
    }
}

// Require config
require(ROOT_PATH . '/core/config.php');

if (isset($conf) && is_array($conf)) {
    $GLOBALS['config'] = $conf;
} else {
    if (!isset($GLOBALS['config'])) {
        $page = 'install';
    }
}

// If we're accessing the upgrade script don't initialise further
if (isset($_GET['route']) && rtrim($_GET['route'], '/') == '/panel/upgrade') {
    $pages = new Pages();
    $pages->add('Core', '/panel/upgrade', 'pages/panel/upgrade.php');
    return;
}

if ($page != 'install') {

    // Check if we're in a subdirectory
    if (isset($directories)) {
        if (empty($directories[0])) {
            unset($directories[0]);
        }

        $directories = array_values($directories);

        $config_path = Config::get('core/path');

        if (!empty($config_path)) {
            $config_path = explode('/', Config::get('core/path'));

            for ($i = 0, $iMax = count($config_path); $i < $iMax; $i++) {
                unset($directories[$i]);
            }

            define('CONFIG_PATH', '/' . Config::get('core/path'));

            $directories = array_values($directories);
        }

        $directory = implode('/', $directories);

        $directory = '/' . $directory;

        // Remove the trailing /
        if (strlen($directory) > 1) {
            $directory = rtrim($directory, '/');
        }
    }

    Application::run();
}
