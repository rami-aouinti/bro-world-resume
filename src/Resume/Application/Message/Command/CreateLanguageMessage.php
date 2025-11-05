<?php

declare(strict_types=1);

namespace App\Resume\Application\Message\Command;

use App\Resume\Application\DTO\Language\LanguageDto;

/**
 * Class CreateLanguageMessage
 */
readonly class CreateLanguageMessage
{
    public function __construct(
        public LanguageDto $dto,
        public bool $flush,
        public bool $skipValidation,
        public ?string $entityManagerName
    ) {
    }
}
