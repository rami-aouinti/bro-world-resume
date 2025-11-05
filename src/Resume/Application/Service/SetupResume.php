<?php

declare(strict_types=1);

namespace App\Resume\Application\Service;

use Bro\WorldCoreBundle\Domain\ValueObject\UserId;
use Bro\WorldCoreBundle\Infrastructure\ValueObject\SymfonyUser;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * Class SetupResume
 */
readonly class SetupResume
{
    public function __construct(
        private ResumeRepositoryInterface $resumeRepository,
    )
    {
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function initResume(SymfonyUser $symfonyUser): string
    {
        $resume = new Resume();
        $userId = new UserId($symfonyUser->getUserIdentifier());

        $resume
            ->setUserId($userId)
            ->setFullName($symfonyUser->getFullName() ?? 'Full Name')
            ->setHeadline("Headline Resume")
            ->setSummary("Summary resume")
            ->setLocation("Location")
            ->setEmail("Email")
            ->setPhone("Phone")
            ->setWebsite("Website")
            ->setAvatarUrl(null);

        $this->resumeRepository->save($resume);

        return $resume->getId();
    }
}
