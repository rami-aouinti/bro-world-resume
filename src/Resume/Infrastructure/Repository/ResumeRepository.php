<?php

declare(strict_types=1);

namespace App\Resume\Infrastructure\Repository;

use Bro\WorldCoreBundle\Domain\Rest\UuidHelper;
use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\Repository\BaseRepository;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Class ResumeRepository
 */
class ResumeRepository extends BaseRepository implements ResumeRepositoryInterface
{
    protected static string $entityName = Resume::class;

    /**
     * @var array<int, string>
     */
    protected static array $searchColumns = [
        'fullName',
        'headline',
        'summary',
    ];

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
        self::$entityManager = $managerRegistry->getManagerForClass(self::$entityName);
    }

    /**
     * @throws NotSupported
     */
    public function findOneByUserId(UserId $userId): ?Resume
    {
        return $this->findOneBy([
            'userId' => UuidHelper::fromString((string)$userId),
        ]);
    }
}
