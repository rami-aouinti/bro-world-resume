<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use Bro\WorldCoreBundle\Domain\Repository\Interfaces\BaseRepositoryInterface;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
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
