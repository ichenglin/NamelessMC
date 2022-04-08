<?php

class TemplateBootstrapper extends Bootstrapper {

    public function run(): void {

        $user = $this->_container->make(User::class);
        $cache = $this->_container->make(Cache::class);
        $queries = $this->_container->make(Queries::class);

        // Template
        if (!$user->isLoggedIn() || !($user->data()->theme_id)) {
            // Default template for guests
            $cache->setCache('templatecache');
            $template = $cache->retrieve('default');

            if (!$template) {
                define('TEMPLATE', 'DefaultRevamp');
            } else {
                define('TEMPLATE', $template);
            }
        } else {
            // User selected template
            $template = $queries->getWhere('templates', ['id', '=', $user->data()->theme_id]);
            if (!count($template)) {
                // Get default template
                $cache->setCache('templatecache');
                $template = $cache->retrieve('default');

                if (!$template) {
                    define('TEMPLATE', 'DefaultRevamp');
                } else {
                    define('TEMPLATE', $template);
                }
            } else {
                // Check permissions
                $template = $template[0];
                $hasPermission = false;

                if ($template->enabled) {
                    $user_templates = $user->getUserTemplates();

                    foreach ($user_templates as $user_template) {
                        if ($user_template->id === $template->id) {
                            $hasPermission = true;
                            define('TEMPLATE', $template->name);
                            break;
                        }
                    }
                }

                if (!$hasPermission) {
                    // Get default template
                    $cache->setCache('templatecache');
                    $template = $cache->retrieve('default');

                    if (!$template) {
                        define('TEMPLATE', 'DefaultRevamp');
                    } else {
                        define('TEMPLATE', $template);
                    }
                }
            }
        }

        // Panel template
        $cache->setCache('templatecache');
        $template = $cache->retrieve('panel_default');

        if (!$template) {
            define('PANEL_TEMPLATE', 'Default');
        } else {
            define('PANEL_TEMPLATE', $template);
        }
    }
}
