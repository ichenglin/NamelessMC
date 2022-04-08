<?php

final class Container {

    private const CONSTRUCTOR = '__construct';

    private static Container $instance;

    private array $instances = [];
    private array $singletons = [];
    private string $lastInjectMethod = '';
    private array $conditionals = [];
    private array $globals = [];

    public static function get(): Container
    {
        return self::$instance ??= new self();
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T
     */
    public function make(string $class)
    {
        if ($this->isSingleton($class)) {
            return $this->instances[$class] ??= new $class(...$this->buildParams($class));
        }

        return new $class(...$this->buildParams($class));
    }

    public function when(string $class, string $method = self::CONSTRUCTOR): ConditionalInjections
    {
        return new ConditionalInjections($this, $class, $method);
    }

    public function registerConditional(string $class, array $conditional): void {
        $this->conditionals[$class] = $conditional;
    }

    public function inject(string $class, string $method = self::CONSTRUCTOR, array $givens = [])
    {
        $this->lastInjectMethod = $method;

        if ($method === self::CONSTRUCTOR) {
            return new $class(...$this->buildParams($class, $method, $givens));
        }

        return $this->make($class)->{$method}(...$this->buildParams($class, $method, $givens));
    }

    public function bind(string $class, $callback): void
    {
        $this->singleton($class);

        if ($callback instanceof $class) {
            $instance = $callback;
        } else {
            $instance = $callback();

            if (!($instance instanceof $class)) {
                $instanceClass = get_class($instance);
                throw new RuntimeException("Cannot bind instance of {$instanceClass} to {$class}.\nInstances returned by callback in bind() method must match class bound to.");
            }
        }

        $this->instances[$class] = $instance;
    }

    public function singleton(string $class): void
    {
        $this->singletons[] = $class;
    }

    public function global($instance, string $name): void
    {
        $this->globals[$name] = $instance;
    }

    public function retrieveGlobal(string $name)
    {
        return $this->globals[$name];
    }

    public function asArray(): array
    {
        return array_merge(
            $this->globals,
            array_change_key_case($this->instances, CASE_LOWER)
        );
    }

    private function isSingleton(string $class): bool
    {
        return in_array($class, $this->singletons);
    }

    private function buildParams(string $class, string $method = self::CONSTRUCTOR, array $givens = []): array
    {
        $params = [];

        $reflectionClass = new ReflectionClass($class);
        if (!$reflectionClass->hasMethod($method)) {
            return $params;
        }

        $reflectionMethod = new ReflectionMethod($class, $method);
        $reflectionParams = $reflectionMethod->getParameters();

        foreach ($reflectionParams as $param) {
            $type = $param->getType();

            if ($type === null) {
                throw new RuntimeException("All method parameters must have a typehint in the '{$class}::{$method}' method.");
            }

            if (array_key_exists($param->getName(), $givens)) {
                $params[] = $givens[$param->getName()];
                continue;
            }

            if (array_key_exists($class, $this->conditionals)) {
                $dependencyMethod = $this->conditionals[$class]['method'];
                if ($dependencyMethod === $method) {
                    $dependency = $this->conditionals[$class]['dependency'];
                    $variableName = $this->conditionals[$class]['variableName'];
                    if (($dependency === $type->getName() || $dependency === $param->getName())
                        && ($variableName === $param->getName() || $variableName === null)
                    ) {
                        $params[] = $this->conditionals[$class]['instance'];
                        continue;
                    }
                }
            }

            if (class_exists($type->getName())) {
                $params[] = $this->make($type->getName());
                continue;
            }

            $exceptionMessage = "'{$type->getName()}' is not a valid class in the '{$class}::{$method}' method. Cannot make instance of primative type or interface.";

            if ($this->lastInjectMethod != $method) {
                $exceptionMessage .= "\nDid you forget to bind an instance of '{$class}' to inject, instead of the '{$class}::{$method}' method?";
            }

            throw new RuntimeException($exceptionMessage);
        }

        return $params;
    }
}
