<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Language\LanguageDto;

class CreateLanguageMessage
{
    public function __construct(
        public readonly LanguageDto $dto,
        public readonly bool $flush,
        public readonly bool $skipValidation,
        public readonly ?string $entityManagerName
    ) {
    }
}
