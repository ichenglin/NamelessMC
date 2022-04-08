<?php

class InjectDebugBarMiddleware extends Middleware {

    public function handle(Request $request): void {
        if ((defined('DEBUGGING') && DEBUGGING) && Composer\InstalledVersions::isInstalled('maximebf/debugbar')) {
            define('PHPDEBUGBAR', true);
            DebugBarHelper::getInstance()->enable(
                Container::get()->make(Smarty::class)
            );
        }
    }
}
