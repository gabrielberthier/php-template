<?php

namespace Core\Http\Domain;

use Core\Http\Interfaces\ActionInterface;

readonly class RouteModel
{
    public function __construct(
        public array $methods,
        public string $path,
        public ActionInterface|string $controller,
        /** @param \SplStack<string|object> $middlewares */
        public \SplStack $middlewares = new \SplStack(),
    ) {
    }
}
