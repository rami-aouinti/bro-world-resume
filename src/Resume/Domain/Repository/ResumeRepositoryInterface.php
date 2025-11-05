<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use Bro\WorldCoreBundle\Domain\Repository\Interfaces\BaseRepositoryInterface;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Resume;

/**
 * @package App\Resume\Domain\Repository
 */
interface ResumeRepositoryInterface extends BaseRepositoryInterface
{
    public function findOneByUserId(UserId $userId): ?Resume;
}
