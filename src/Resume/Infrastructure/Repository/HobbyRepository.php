<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\Repository;

use App\General\Domain\Rest\UuidHelper;
use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\Repository\BaseRepository;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Repository\HobbyRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

class HobbyRepository extends BaseRepository implements HobbyRepositoryInterface
{
    protected static string $entityName = Hobby::class;

    /**
     * @var array<int, string>
     */
    protected static array $searchColumns = [
        'name',
        'category',
        'level',
    ];

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        self::$entityManager = $managerRegistry->getManagerForClass(self::$entityName);
    }

    public function findByUserIdOrdered(UserId $userId): array
    {
        return $this->findBy(
            ['userId' => UuidHelper::fromString((string)$userId)],
            ['position' => 'ASC', 'name' => 'ASC']
        );
    }
}
