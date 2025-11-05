<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\Repository;

use Bro\WorldCoreBundle\Domain\Rest\UuidHelper;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\Repository\BaseRepository;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Repository\HobbyRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class HobbyRepository
 */
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
            [
                'userId' => UuidHelper::fromString((string)$userId),
            ],
            [
                'position' => 'ASC',
                'name' => 'ASC',
            ]
        );
    }
}
