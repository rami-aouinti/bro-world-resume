<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Hobby\HobbyDto;

/**
 * Class CreateHobbyMessage
 */
readonly class CreateHobbyMessage
{
    public function __construct(
        public HobbyDto $dto,
        public bool $flush,
        public bool $skipValidation,
        public ?string $entityManagerName
    ) {
    }
}
