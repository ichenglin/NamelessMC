<?php

class ModuleBootstrapper extends Bootstrapper {

    public function run(): void {
        $cache = $this->_container->make(Cache::class);

        // Modules
        $cache->setCache('modulescache');
        if (!$cache->isCached('enabled_modules')) {
            $cache->store('enabled_modules', [
                ['name' => 'Core', 'priority' => 1]
            ]);
            $cache->store('module_core', true);
        }
        $enabled_modules = $cache->retrieve('enabled_modules');

        foreach ($enabled_modules as $module) {
            if ($module['name'] == 'Core') {
                $core_exists = true;
                break;
            }
        }

        if (!isset($core_exists)) {
            $enabled_modules[] = [
                'name' => 'Core',
                'priority' => 1
            ];
        }

        // Sort by priority
        usort($enabled_modules, static function ($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        foreach ($enabled_modules as $module) {
            if (file_exists(ROOT_PATH . '/modules/' . $module['name'] . '/init.php')) {
                require(ROOT_PATH . '/modules/' . $module['name'] . '/init.php');
            }
        }
    }
}
