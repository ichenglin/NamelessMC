<?php

class Request {

    private User $_user;
    private string $_route;

    private function __construct(User $user, string $route) {
        $this->_user = $user;
        $this->_route = $route;
    }

    public static function capture(User $user, string $route): Request {
        return new Request($user, $route);
    }

    public function user(): User {
        return $this->_user;
    }

    public function route(): string {
        return $this->_route;
    }

    public function input(): array {
        return $_POST ?: $_GET;
    }
}
