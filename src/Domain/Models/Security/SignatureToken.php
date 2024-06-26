<?php

declare(strict_types=1);

namespace App\Domain\Models\Security;

use App\Domain\Models\Museum;
use DateTimeInterface;

use DateInterval;
use JsonSerializable;

readonly class SignatureToken implements JsonSerializable
{
    public ?DateTimeInterface $createdAt;

    public ?DateTimeInterface $updated;

    public ?DateTimeInterface $ttl;

    public function __construct(
        public ?int $id,
        public string $signature,
        public string $privateKey,
        public ?Museum $museum,
        ?DateTimeInterface $createdAt,
        ?DateTimeInterface $updated,
        ?DateTimeInterface $ttl
    ) {
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->updated = $updated ?? new \DateTime();
        $this->ttl = $ttl ?? (new \DateTime())->add(new DateInterval('P6M'));
    }

    public function jsonSerialize(): mixed
    {
        return [
            'id' => $this->id,
            'signature' => $this->signature,
            'privateKey' => $this->privateKey,
        ];
    }
}
