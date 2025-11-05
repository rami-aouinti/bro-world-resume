<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use App\General\Domain\Repository\Interfaces\BaseRepositoryInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Language;

/**
 * @package App\Resume\Domain\Repository
 */
interface LanguageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array<int, Language>
     */
    public function findByUserIdOrdered(UserId $userId): array;
}
