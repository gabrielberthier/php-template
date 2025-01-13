<?php

declare(strict_types=1);

namespace Brash\Framework\Providers;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use DI\ContainerBuilder;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Amp\File;
use Amp\ByteStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;

use function Brash\Framework\functions\isProd;

class LoggerProvider implements AppProviderInterface
{
    public function provide(ContainerBuilder $container): void
    {
        $currentWorkingDir = getcwd();
        $container->addDefinitions([
            // Should be set to false in production
            'logger' => [
                'name' => 'kitsune',
                'path' => (getenv('docker') || ! getenv('log-file')) ? 'php://stdout' : $currentWorkingDir.'/temp/logs/app.log',
                'level' => isProd() ? Logger::INFO : Logger::DEBUG,
            ],
            LoggerInterface::class => static function (ContainerInterface $c) {
                $loggerSettings = $c->get('logger');
                $logger = new Logger($loggerSettings['name']);

                $processor = new UidProcessor;
                $logger->pushProcessor($processor);

                $handler = new StreamHandler(ByteStream\getStdout(), $loggerSettings['level']);
                $handler->setFormatter(new ConsoleFormatter());

                $logger->pushHandler($handler);

                return $logger;
            },
        ]);
    }
}
