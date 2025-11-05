<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use App\General\Domain\Repository\Interfaces\BaseRepositoryInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Education;

/**
 * @package App\Resume\Domain\Repository
 */
interface EducationRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array<int, Education>
     */
    public function findByUserIdOrdered(UserId $userId): array;
}
