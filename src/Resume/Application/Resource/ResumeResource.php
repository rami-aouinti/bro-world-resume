<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource;

use App\General\Application\DTO\Interfaces\RestDtoInterface;
use App\General\Application\Rest\RestResource;
use App\General\Application\Service\AuthenticatorServiceInterface;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\DTO\Resume\ResumeDto;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Class ResumeResource
 */
class ResumeResource extends RestResource
{
    public function __construct(
        private readonly ResumeRepositoryInterface $resumeRepository,
        private readonly AuthenticatorServiceInterface $authenticatorService,
    ) {
        parent::__construct($resumeRepository);
        $this->setDtoClass(ResumeDto::class);
    }

    public function getRepository(): ResumeRepositoryInterface
    {
        /** @var ResumeRepositoryInterface $repository */
        $repository = parent::getRepository();

        return $repository;
    }

    public function findOneByUserId(UserId $userId): ?Resume
    {
        return $this->resumeRepository->findOneByUserId($userId);
    }

    #[Override]
    public function beforeCreate(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->assertDtoBelongsToCurrentUser($restDto);
    }

    #[Override]
    public function beforeUpdate(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->assertResumeOwnership($entity);
        $this->assertDtoBelongsToCurrentUser($restDto);
    }

    #[Override]
    public function beforePatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->assertResumeOwnership($entity);
        $this->assertDtoBelongsToCurrentUser($restDto);
    }

    #[Override]
    public function beforeDelete(string &$id, EntityInterface $entity): void
    {
        $this->assertResumeOwnership($entity);
    }

    #[Override]
    public function afterFindOne(string &$id, ?EntityInterface $entity = null): void
    {
        if ($entity instanceof Resume) {
            $this->assertResumeOwnership($entity);
        }
    }

    public function processCriteria(array &$criteria, Request $request, string $method): void
    {
        $criteria['userId'] = (string)$this->getCurrentUserId();
    }

    private function assertDtoBelongsToCurrentUser(RestDtoInterface $restDto): void
    {
        if (!$restDto instanceof ResumeDto) {
            return;
        }

        $currentUserId = (string)$this->getCurrentUserId();

        if ($restDto->getUserId() === null) {
            $restDto->setUserId($currentUserId);

            return;
        }

        if ($restDto->getUserId() !== $currentUserId) {
            throw new AccessDeniedHttpException('You cannot manage resumes for another user.');
        }
    }

    private function assertResumeOwnership(EntityInterface $entity): void
    {
        if (!$entity instanceof Resume) {
            return;
        }

        if ((string)$entity->getUserId() !== (string)$this->getCurrentUserId()) {
            throw new AccessDeniedHttpException('You cannot manage resumes for another user.');
        }
    }

    private function getCurrentUserId(): UserId
    {
        $symfonyUser = $this->authenticatorService->getSymfonyUser();

        if ($symfonyUser === null) {
            throw new AccessDeniedHttpException('Authentication is required to manage resume data.');
        }

        return new UserId($symfonyUser->getUserIdentifier());
    }
}
