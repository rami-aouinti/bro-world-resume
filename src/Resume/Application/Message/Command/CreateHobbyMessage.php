<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Hobby\HobbyDto;

class CreateHobbyMessage
{
    public function __construct(
        public readonly HobbyDto $dto,
        public readonly bool $flush,
        public readonly bool $skipValidation,
        public readonly ?string $entityManagerName
    ) {
    }
}
