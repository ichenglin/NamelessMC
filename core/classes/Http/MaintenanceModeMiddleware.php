<?php

class MaintenanceModeMiddleware extends Middleware {

    private static array $_bypass_pages = [
        'login',
        'forgot_password',
        'api',
    ];

    public function handle(Request $request): void {
        $cache = Container::get()->make(Cache::class);
        $smarty = Container::get()->make(Smarty::class);
        $language = Container::get()->make(Language::class);

        // Maintenance mode?
        $cache->setCache('maintenance_cache');
        $maintenance = $cache->retrieve('maintenance');

        if (!isset($maintenance['maintenance']) || $maintenance['maintenance'] == 'false') {
            return;
        }

        // Enabled
        // Admins only beyond this point
        if (!$request->user()->isLoggedIn() || !$request->user()->canViewStaffCP()) {
            if (!in_array($request->route(), self::$_bypass_pages)) {
                require(ROOT_PATH . '/maintenance.php');
                die();
            }
        } else {
            // Display notice to admin stating maintenance mode is enabled
            $smarty->assign('MAINTENANCE_ENABLED', $language->get('admin', 'maintenance_enabled'));
        }
    }
}
