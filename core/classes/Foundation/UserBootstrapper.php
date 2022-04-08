<?php

class UserBootstrapper extends Bootstrapper {

    public function register(Container $container): void {
        // User initialisation
        $user = new User();
        // Do they need logging in (checked remember me)?
        if (Cookie::exists(Config::get('remember/cookie_name')) && !Session::exists(Config::get('session/session_name'))) {
            $hash = Cookie::get(Config::get('remember/cookie_name'));
            $hashCheck = DB::getInstance()->get('users_session', ['hash', '=', $hash]);

            if ($hashCheck->count()) {
                $user = new User($hashCheck->first()->user_id);
                $user->login();
            }
        }

        $container->bind(User::class, $user);

        $cache = $container->make(Cache::class);

        // Language
        if (!$user->isLoggedIn() || !($user->data()->language_id)) {
            // Default language for guests
            $cache->setCache('languagecache');
            $language = $cache->retrieve('language');

            if (!$language) {
                define('LANGUAGE', 'EnglishUK');
                $language = new Language();
            } else {
                define('LANGUAGE', $language);
                $language = new Language('core', $language);
            }
        } else {
            $queries = $container->make(Queries::class);
            // User selected language
            $language = $queries->getWhere('languages', ['id', '=', $user->data()->language_id]);
            if (!count($language)) {
                // Get default language
                $cache->setCache('languagecache');
                $language = $cache->retrieve('language');

                if (!$language) {
                    define('LANGUAGE', 'EnglishUK');
                    $language = new Language();
                } else {
                    define('LANGUAGE', $language);
                    $language = new Language('core', $language);
                }
            } else {
                define('LANGUAGE', $language[0]->name);
                $language = new Language('core', $language[0]->name);
            }
        }

        $container->bind(Language::class, $language);
    }

    public function run(): void {
        $user = $this->_container->make(User::class);
        $cache = $this->_container->make(Cache::class);

        // Set timezone
        if ($user->isLoggedIn()) {
            define('TIMEZONE', $user->data()->timezone);
        } else {
            $cache->setCache('timezone_cache');
            if ($cache->isCached('timezone')) {
                define('TIMEZONE', $cache->retrieve('timezone'));
            } else {
                define('TIMEZONE', 'Europe/London');
            }
        }

        date_default_timezone_set(TIMEZONE);
    }
}
