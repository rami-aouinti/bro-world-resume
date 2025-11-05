<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\Repository;

use Bro\WorldCoreBundle\Domain\Rest\UuidHelper;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\Repository\BaseRepository;
use App\Resume\Domain\Entity\Project;
use App\Resume\Domain\Repository\ProjectRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ProjectRepository
 */
class ProjectRepository extends BaseRepository implements ProjectRepositoryInterface
{
    protected static string $entityName = Project::class;

    /**
     * @var array<int, string>
     */
    protected static array $searchColumns = [
        'title',
        'description',
        'status',
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
                'title' => 'ASC',
            ]
        );
    }
}
