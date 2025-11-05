<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use Bro\WorldCoreBundle\Domain\Repository\Interfaces\BaseRepositoryInterface;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Experience;

/**
 * @package App\Resume\Domain\Repository
 */
interface ExperienceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array<int, Experience>
     */
    public function findByUserIdOrdered(UserId $userId): array;
}
