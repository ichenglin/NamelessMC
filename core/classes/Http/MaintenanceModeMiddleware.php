<?php

class MaintenanceModeMiddleware extends Middleware {

    private static array $_bypass_pages = [
        'login',
        'forgot_password',
        'api',
    ];

    public function handle(Request $request, Container $container): void {
        $cache = $container->make(Cache::class);
        $smarty = $container->make(Smarty::class);
        $language = $container->make(Language::class);

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
