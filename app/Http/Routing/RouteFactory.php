<?php

namespace Core\Http\Routing;

use Core\Http\Domain\GroupModel;
use Core\Http\Domain\RouteModel;
use Core\Http\Attributes\Route as RouteAttribute;
use Core\Http\Interfaces\ActionInterface;
use Core\Http\Routing\Cache\GroupCacheResult;

final class RouteFactory
{
    public function __construct(
        private GroupCollector $groupCollector,
        private GroupCacheResult $cache
    ) {
    }

    public function make(RouteAttribute $routeAttribute, ActionInterface|string $controller): ?RouteModel
    {
        if ($routeAttribute->skip) {
            return null;
        }

        $middlewares = $this->extractMiddlewares($routeAttribute);
        $methods = is_array($routeAttribute->method) ? $routeAttribute->method : [$routeAttribute->method];
        $path = $routeAttribute->path;
        $group = $routeAttribute->group;

        if (!is_null($group)) {
            $groupResult = $this->getGroupResult($group);
            if ($groupResult->skip) {
                return null;
            }
            $path = implode("/", [trim($groupResult->prefix, "/"), trim(trim($path), "/")]);
            
            $result = [];

            foreach ($groupResult->middlewares as $m) {
                $result[] = $m;
            }

            $middlewares = [...$result, ...$middlewares];
        }

        return new RouteModel($methods, $path, $controller, $middlewares);
    }

    private function extractMiddlewares(RouteAttribute $routeAttribute): array
    {
        $middlewares = $routeAttribute->middleware;

        if ($middlewares) {
            if (is_array($routeAttribute->middleware)) {
                return $middlewares;
            }
            return [$middlewares];
        }

        return [];
    }

    private function getGroupResult(object|string $group): GroupModel
    {
        $cached = $this->cache->get($group);

        if (is_null($cached)) {
            $newEntry = $this->groupCollector->getGroup($group);
            $this->cache->setCache($group, $newEntry);

            return $newEntry;
        }

        return $cached;
    }
}
