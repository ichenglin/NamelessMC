<?php

class InjectDebugBarMiddleware extends Middleware {

    public function handle(Request $request, Container $container): void {
        if ((defined('DEBUGGING') && DEBUGGING) && Composer\InstalledVersions::isInstalled('maximebf/debugbar')) {
            define('PHPDEBUGBAR', true);
            DebugBarHelper::getInstance()->enable(
                $container->make(Smarty::class)
            );
        }
    }
}
