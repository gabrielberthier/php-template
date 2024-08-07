<?php

declare(strict_types=1);

namespace Core\Providers;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use DI\ContainerBuilder;
use Core\Providers\AppProviderInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function Core\functions\isProd;

class LoggerProvider implements AppProviderInterface
{
    public function provide(ContainerBuilder $container)
    {
        $currentWorkingDir = getcwd();
        $container->addDefinitions([
            // Should be set to false in production
            'logger' => [
                'name' => 'kitsune',
                'path' => (getenv('docker') || !getenv('log-file')) ? 'php://stdout' : "$currentWorkingDir/temp/logs/app.log",
                'level' => isProd() ? Logger::INFO : Logger::DEBUG,
            ],
            LoggerInterface::class => static function (ContainerInterface $c) {
                $loggerSettings = $c->get('logger');
                $logger = new Logger($loggerSettings['name']);

                $processor = new UidProcessor();
                $logger->pushProcessor($processor);

                $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
                $handler->setFormatter(new ColoredLineFormatter());
                $logger->pushHandler($handler);

                return $logger;
            }
        ]);
    }
}
