<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use Bro\WorldCoreBundle\Domain\Repository\Interfaces\BaseRepositoryInterface;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
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
