<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Skill\SkillDto;

class CreateSkillMessage
{
    public function __construct(
        public readonly SkillDto $dto,
        public readonly bool $flush,
        public readonly bool $skipValidation,
        public readonly ?string $entityManagerName
    ) {
    }
}
