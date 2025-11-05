<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Skill\SkillDto;

/**
 * CreateSkillMessage
 */
readonly class CreateSkillMessage
{
    public function __construct(
        public SkillDto $dto,
        public bool $flush,
        public bool $skipValidation,
        public ?string $entityManagerName
    ) {
    }
}
