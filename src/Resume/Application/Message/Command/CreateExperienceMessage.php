<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Experience\ExperienceDto;

class CreateExperienceMessage
{
    public function __construct(
        public readonly ExperienceDto $dto,
        public readonly bool $flush,
        public readonly bool $skipValidation,
        public readonly ?string $entityManagerName
    ) {
    }
}
