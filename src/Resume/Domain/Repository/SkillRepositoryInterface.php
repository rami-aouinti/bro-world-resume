<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use Bro\WorldCoreBundle\Domain\Repository\Interfaces\BaseRepositoryInterface;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Skill;

/**
 * @package App\Resume\Domain\Repository
 */
interface SkillRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array<int, Skill>
     */
    public function findByUserIdOrdered(UserId $userId): array;
}
