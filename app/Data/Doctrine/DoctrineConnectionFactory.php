<?php

namespace Core\Data\Doctrine;

use Core\Data\Domain\ConnectionModel;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\DBAL\Types\Type;

final class DoctrineConnectionFactory
{
    public function createConnectionFromArray(array $doctrine, \Doctrine\ORM\Configuration $config): \Doctrine\DBAL\Connection
    {
        if (!Type::hasType('uuid')) {
            Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');
        }

        $connection = $doctrine['connection'];

        assert($connection instanceof ConnectionModel);

        $connectionParams = $connection->url !== '' ? $this->getFromUrl($connection->url) : $connection->getAsArray();

        return DriverManager::getConnection(
            $connectionParams,
            $config
        );
    }

    private function getFromUrl(string $url)
    {
        $dsnParser = new DsnParser(['mysql' => 'mysqli', 'postgresql' => 'pdo_pgsql']);
        
        return $dsnParser->parse($url);
    }
}
