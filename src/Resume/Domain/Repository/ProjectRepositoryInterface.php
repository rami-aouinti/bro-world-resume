<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use App\General\Domain\Repository\Interfaces\BaseRepositoryInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Project;

/**
 * @package App\Resume\Domain\Repository
 */
interface ProjectRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array<int, Project>
     */
    public function findByUserIdOrdered(UserId $userId): array;
}
