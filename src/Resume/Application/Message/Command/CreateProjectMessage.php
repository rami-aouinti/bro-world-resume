<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Project\ProjectDto;

/**
 * CreateProjectMessage
 */
readonly class CreateProjectMessage
{
    public function __construct(
        public ProjectDto $dto,
        public bool $flush,
        public bool $skipValidation,
        public ?string $entityManagerName
    ) {
    }
}
