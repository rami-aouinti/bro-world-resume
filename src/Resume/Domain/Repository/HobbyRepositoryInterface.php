<?php

declare(strict_types=1);

namespace App\Resume\Domain\Repository;

use App\General\Domain\Repository\Interfaces\BaseRepositoryInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Domain\Entity\Hobby;

interface HobbyRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * @return array<int, Hobby>
     */
    public function findByUserIdOrdered(UserId $userId): array;
}
