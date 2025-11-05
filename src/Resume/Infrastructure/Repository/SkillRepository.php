<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\Repository;

use Bro\WorldCoreBundle\Domain\Rest\UuidHelper;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\Repository\BaseRepository;
use App\Resume\Domain\Entity\Skill;
use App\Resume\Domain\Repository\SkillRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class SkillRepository
 */
class SkillRepository extends BaseRepository implements SkillRepositoryInterface
{
    protected static string $entityName = Skill::class;

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
