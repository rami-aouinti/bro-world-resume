<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Education\EducationDto;

/**
 * CreateEducationMessage
 */
readonly class CreateEducationMessage
{
    public function __construct(
        public EducationDto $dto,
        public bool $flush,
        public bool $skipValidation,
        public ?string $entityManagerName
    ) {
    }
}
