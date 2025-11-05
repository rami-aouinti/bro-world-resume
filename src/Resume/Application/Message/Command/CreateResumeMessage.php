<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Resume\ResumeDto;

/**
 * Class CreateResumeMessage
 */
readonly class CreateResumeMessage
{
    public function __construct(
        public ResumeDto $dto,
        public bool $flush,
        public bool $skipValidation,
        public ?string $entityManagerName
    ) {
    }
}
