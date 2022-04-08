<?php

class SmartyBootstrapper extends Bootstrapper {

    public function register(Container $container): void {
        $container->singleton(Smarty::class);
    }

    public function run(): void {
        $smarty = $this->_container->make(Smarty::class);
        $language = $this->_container->make(Language::class);

        // Smarty
        $securityPolicy = new Smarty_Security($smarty);
        $securityPolicy->php_modifiers = [
            'escape',
            'count',
            'key',
            'round',
            'ucfirst',
            'defined',
            'date',
            'explode',
            'implode',
            'strtolower',
            'strtoupper'
        ];
        $securityPolicy->php_functions = [
            'isset',
            'empty',
            'count',
            'sizeof',
            'in_array',
            'is_array',
            'time',
            'nl2br',
            'is_numeric',
            'file_exists',
            'array_key_exists'
        ];
        $securityPolicy->secure_dir = [ROOT_PATH . '/custom/templates', ROOT_PATH . '/custom/panel_templates'];
        $smarty->enableSecurity($securityPolicy);

        // Basic Smarty variables
        $smarty->assign([
            'CONFIG_PATH' => defined('CONFIG_PATH') ? CONFIG_PATH . '/' : '/',
            'OG_URL' => Output::getClean(rtrim(Util::getSelfURL(), '/') . $_SERVER['REQUEST_URI']),
            'OG_IMAGE' => Output::getClean(rtrim(Util::getSelfURL(), '/') . '/core/assets/img/site_image.png'),
            'SITE_NAME' => SITE_NAME,
            'SITE_HOME' => URL::build('/'),
            'USER_INFO_URL' => URL::build('/queries/user/', 'id='),
            'GUEST' => $language->get('user', 'guest')
        ]);
    }
}
