<?php

class Application {

    private static array $_bootstrappers = [
        AppBootstrapper::class,
        UserBootstrapper::class,
        SmartyBootstrapper::class,
        TemplateBootstrapper::class,
        AvatarBootstrapper::class,
        NavigationBootstrapper::class,
        ModuleBootstrapper::class,
    ];

    /**
     * @var Bootstrapper[]
     */
    private static array $_registered_bootstrappers = [];

    private static array $_middlewares = [
        CsrfCheckMiddleware::class,
        MaintenanceModeMiddleware::class,
        InjectDebugBarMiddleware::class,
        ProcessUserMiddleware::class,
    ];

    /**
     * Start the application!
     * Registers and runs all bootstrappers.
     */
    public static function run(): void {
        self::registerBootstrappers();

        self::runBootstrappers();

        $request = Request::capture(
            Container::get()->make(User::class),
            $_GET['route']
        );

        self::runMiddlewares($request);

        Response::make($request)->send();
    }

    private static function registerBootstrappers(): void {
        foreach (self::$_bootstrappers as $bootstrapper) {
            /** @var Bootstrapper $bootstrapper */
            $bootstrapper = new $bootstrapper();

            $bootstrapper->register(Container::get());

            self::$_registered_bootstrappers[] = $bootstrapper;
        }
    }

    private static function runBootstrappers(): void {
        foreach (self::$_registered_bootstrappers as $bootstrapper) {
            $bootstrapper->run();
        }
    }

    private static function runMiddlewares(Request $request): void {
        foreach (self::$_middlewares as $middleware) {
            /** @var Middleware $middleware */
            $middleware = new $middleware();

            $middleware->handle($request);
        }
    }
}
