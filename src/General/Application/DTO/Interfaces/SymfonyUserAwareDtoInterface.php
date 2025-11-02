<?php

declare(strict_types=1);

namespace App\General\Application\DTO\Interfaces;

use App\General\Infrastructure\ValueObject\SymfonyUser;

/**
 * @package App\General\Application\DTO\Interfaces
 * @author  Rami Aouinti <rami.aouinti@tkdeutschland.de>
 */
interface SymfonyUserAwareDtoInterface
{
    public function applySymfonyUser(SymfonyUser $symfonyUser): void;
}
