<?php

abstract class Bootstrapper {

    protected Container $_container;

    public function __construct() {
        $this->_container = Container::get();
    }

    /**
     * Register instances required for this bootstrapper into the container.
     */
    public function register(Container $container): void {
        // ...
    }

    abstract public function run(): void;

}
