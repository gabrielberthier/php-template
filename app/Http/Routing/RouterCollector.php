<?php

declare(strict_types=1);

namespace Core\Http\Routing;

use App\Presentation\RoutingColletor;
use Core\Http\Attributes\Route as RouteAttribute;
use Core\Http\Abstractions\AbstractRouterTemplate;
use Core\Http\Domain\RouteModel;
use Core\Http\Interfaces\RouteCollectorInterface;
use Core\Http\Interfaces\RouterInterface;
use Core\Http\Routing\RouteFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Spatie\StructureDiscoverer\Data\DiscoveredStructure;
use ReflectionClass;

class RouterCollector implements RouterInterface
{
    public function __construct(
        private RouteFactory $routeFactory,
        private RouteIncluder $routeIncluder,

    ) {
    }

    public function run(): void
    {
        $controllers = new ArrayCollection(RoutingColletor::getActions());

        $controllers
            ->map(fn(DiscoveredStructure|string $controller) => new ReflectionClass($controller))
            ->map(fn(ReflectionClass $reflectionClass): ?RouteModel => $this->createRouteModel($reflectionClass))
            ->filter(fn(?RouteModel $route) => $route !== null)
            ->map(fn(RouteModel $route) => $this->routeIncluder->include($route));
    }

    private function createRouteModel(ReflectionClass $reflectionClass): ?RouteModel
    {
        $attributes = $reflectionClass->getAttributes(RouteAttribute::class);
        $attribute = array_pop($attributes);

        if ($attribute !== null) {
            return $this->routeFactory->make(
                $attribute->newInstance(),
                $reflectionClass->getName()
            );
        }

        return null;
    }
}
