<?php

class NavigationBootstrapper extends Bootstrapper {

    private Navigation $navigation;
    private Navigation $cc_nav;

    public function register(Container $container): void {
        // Navbar links
        $navigation = new Navigation();
        $cc_nav = new Navigation();
        $staffcp_nav = new Navigation(true); // $staffcp_nav = panel nav

        $this->navigation = $navigation;
        $this->cc_nav = $cc_nav;

        $container->when(Core_Module::class)
            ->needs(Navigation::class)
            ->give($navigation);

        $container->global($cc_nav, 'cc_nav');
        $container->global($staffcp_nav, 'staffcp_nav');
        $container->global($navigation, 'navigation');

        $container->bind(Pages::class, fn () => new Pages());
    }

    public function run(): void {
        $language = $this->_container->make(Language::class);
        $configuration = $this->_container->make(Configuration::class);
        $cache = $this->_container->make(Cache::class);

        // Add links to cc_nav
        $this->cc_nav->add('cc_overview', $language->get('user', 'overview'), URL::build('/user'));
        $this->cc_nav->add('cc_alerts', $language->get('user', 'alerts'), URL::build('/user/alerts'));
        $this->cc_nav->add('cc_messaging', $language->get('user', 'messaging'), URL::build('/user/messaging'));
        $this->cc_nav->add('cc_settings', $language->get('user', 'profile_settings'), URL::build('/user/settings'));
        $this->cc_nav->add('cc_oauth', $language->get('admin', 'oauth'), URL::build('/user/oauth'));

        // Placeholders enabled?
        $placeholders_enabled = $configuration->get('Core', 'placeholders');
        if ($placeholders_enabled == 1) {
            $this->cc_nav->add('cc_placeholders', $language->get('user', 'placeholders'), URL::build('/user/placeholders'));
        }

        // Add homepage to navbar
        // Check navbar order + icon in cache
        $cache->setCache('navbar_order');
        if (!$cache->isCached('index_order')) {
            // Create cache entry now
            $home_order = 1;
            $cache->store('index_order', 1);
        } else {
            $home_order = $cache->retrieve('index_order');
        }

        $cache->setCache('navbar_icons');
        if ($cache->isCached('index_icon')) {
            $home_icon = $cache->retrieve('index_icon');
        } else {
            $home_icon = '';
        }

        $this->navigation->add('index', $language->get('general', 'home'), URL::build('/'), 'top', null, $home_order, $home_icon);
    }
}
