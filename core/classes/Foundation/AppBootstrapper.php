<?php

class AppBootstrapper extends Bootstrapper {

    public function register(Container $container): void {
        $container->bind(Cache::class, static function() {
            return new Cache(['name' => 'nameless', 'extension' => '.cache', 'path' => ROOT_PATH . '/cache/']);
        });

        $container->bind(Queries::class, fn () => new Queries());

        $container->bind(Configuration::class, function() use ($container) {
            return new Configuration(
                $container->make(Cache::class),
            );
        });

        $container->bind(Widgets::class, function() use ($container) {
            return new Widgets(
                $container->make(Cache::class),
            );
        });

        $container->bind(Endpoints::class, fn () => new Endpoints());

        $container->bind(Announcements::class, function() use ($container) {
            return new Announcements(
                $container->make(Cache::class),
            );
        });
    }

    public function run(): void {
        // Friendly URLs?
        define('FRIENDLY_URLS', Config::get('core/friendly') == 'true');

        // Force https/www?
        if (Config::get('core/force_https')) {
            define('FORCE_SSL', true);
        }
        if (Config::get('core/force_www')) {
            define('FORCE_WWW', true);
        }

        if (defined('FORCE_SSL') && !Util::isConnectionSSL()) {
            if (defined('FORCE_WWW') && !str_contains($_SERVER['HTTP_HOST'], 'www.')) {
                header('Location: https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                die();
            }

            header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            die();
        }

        if (defined('FORCE_WWW') && !str_contains($_SERVER['HTTP_HOST'], 'www.')) {
            if (!Util::isConnectionSSL()) {
                header('Location: http://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            } else {
                header('Location: https://www.' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
            }
        }

        $cache = $this->_container->make(Cache::class);

        $cache->setCache('page_load_cache');
        $page_loading = $cache->retrieve('page_load');
        define('PAGE_LOADING', $page_loading);

        // Error reporting
        if (!defined('DEBUGGING')) {
            $cache->setCache('error_cache');
            if ($cache->isCached('error_reporting')) {
                if ($cache->retrieve('error_reporting') == 1) {
                    // Enabled
                    ini_set('display_startup_errors', 1);
                    ini_set('display_errors', 1);
                    error_reporting(-1);

                    define('DEBUGGING', 1);
                } else {
                    // Disabled
                    error_reporting(0);
                    ini_set('display_errors', 0);
                }
            } else {
                // Disable by default
                error_reporting(0);
                ini_set('display_errors', 0);
            }
        }

        $queries = $this->_container->make(Queries::class);

        // Get the Nameless version
        $nameless_version = $queries->getWhere('settings', ['name', '=', 'nameless_version']);
        $nameless_version = $nameless_version[0]->value;
        define('NAMELESS_VERSION', $nameless_version);

        // Set the date format
        define('DATE_FORMAT', Config::get('core/date_format') ?: 'd M Y, H:i');

        // Site name
        $cache->setCache('sitenamecache');
        $sitename = $cache->retrieve('sitename');

        if (!$sitename) {
            define('SITE_NAME', 'NamelessMC');
        } else {
            define('SITE_NAME', $sitename);
        }

        // Minecraft integration?
        $mc_integration = $queries->getWhere('settings', ['name', '=', 'mc_integration']);
        if (count($mc_integration) && $mc_integration[0]->value == '1') {
            define('MINECRAFT', true);
        } else {
            define('MINECRAFT', false);
        }
    }

}
