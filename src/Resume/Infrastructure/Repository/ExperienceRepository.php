<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\Repository;

use Bro\WorldCoreBundle\Domain\Rest\UuidHelper;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\Repository\BaseRepository;
use App\Resume\Domain\Entity\Experience;
use App\Resume\Domain\Repository\ExperienceRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ExperienceRepository
 */
class ExperienceRepository extends BaseRepository implements ExperienceRepositoryInterface
{
    protected static string $entityName = Experience::class;

    /**
     * @var array<int, string>
     */
    protected static array $searchColumns = [
        'company',
        'role',
        'description',
        'location',
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
                'startDate' => 'DESC',
            ]
        );
    }
}
