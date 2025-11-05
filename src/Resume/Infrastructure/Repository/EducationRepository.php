<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\Repository;

use App\General\Domain\Rest\UuidHelper;
use App\General\Domain\ValueObject\UserId;
use App\General\Infrastructure\Repository\BaseRepository;
use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Repository\EducationRepositoryInterface;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class EducationRepository
 */
class EducationRepository extends BaseRepository implements EducationRepositoryInterface
{
    protected static string $entityName = Education::class;

    /**
     * @var array<int, string>
     */
    protected static array $searchColumns = [
        'school',
        'degree',
        'field',
        'description',
    ];

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        self::$entityManager = $managerRegistry->getManagerForClass(self::$entityName);
    }

    /**
     * @throws NotSupported
     */
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
