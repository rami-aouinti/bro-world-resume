<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Resume\ResumeDto;

class CreateResumeMessage
{
    public function __construct(
        public readonly ResumeDto $dto,
        public readonly bool $flush,
        public readonly bool $skipValidation,
        public readonly ?string $entityManagerName
    ) {
    }
}
