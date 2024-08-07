<?php

namespace App\Infrastructure\Persistence\RepositoryTraits;

use App\Infrastructure\Uuid\UuidEncoder;
use Ramsey\Uuid\Uuid;

trait UuidFinderTrait
{
    public function findOneByEncodedUuid(string $encodedUuid)
    {
        $uuid = Uuid::fromString($encodedUuid);
        $decodedUuid = $uuid->getHex();

        return $this->findOneBy(
            [
            'uuid' => $decodedUuid->toString(),
            ]
        );
    }
}
