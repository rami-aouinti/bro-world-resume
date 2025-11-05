<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Experience\ExperienceDto;

/**
 * Class CreateExperienceMessage
 */
readonly class CreateExperienceMessage
{
    public function __construct(
        public ExperienceDto $dto,
        public bool $flush,
        public bool $skipValidation,
        public ?string $entityManagerName
    ) {
    }
}
