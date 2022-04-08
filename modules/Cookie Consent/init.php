<?php
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr12
 *
 *  License: MIT
 *
 *  Cookie Consent initialisation file
 */

require_once ROOT_PATH . '/modules/Cookie Consent/module.php';

$cookie_language = new Language(ROOT_PATH . '/modules/Cookie Consent/language', LANGUAGE);

Container::get()->global($cookie_language, 'cookie_language');

$module = Container::get()->inject(CookieConsent_Module::class, '__construct', [
    'cookie_language' => $cookie_language
]);
