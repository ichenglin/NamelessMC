<?php

class CsrfCheckMiddleware extends Middleware {

    public function handle(Request $request, Container $container): void {
        if (Input::exists() && !Token::check()) {
            // TODO: handle nicely with session error messages
            throw new InvalidArgumentException('Token mismatch');
        }
    }
}
