<?php

class ConditionalInjections {

    private Container $container;
    private string $class;
    private string $method;

    private array $condition;

    public function __construct(Container $container, string $class, string $method) {
        $this->container = $container;
        $this->class = $class;
        $this->method = $method;
    }

    public function needs(string $dependency, string $variableName = null): self {
        $this->condition = [
            'method' => $this->method,
            'dependency' => $dependency,
            'variableName' => $variableName,
            'instance' => null,
        ];

        return $this;
    }

    public function give($instance): void {
        $this->condition['instance'] = $instance;

        $this->container->registerConditional(
            $this->class,
            $this->condition
        );
    }
}
