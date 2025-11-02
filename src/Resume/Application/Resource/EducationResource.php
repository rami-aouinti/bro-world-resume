<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource;

use App\General\Application\DTO\Interfaces\RestDtoInterface;
use App\General\Application\Rest\RestResource;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\DTO\Education\EducationDto;
use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\EducationRepositoryInterface;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use Override;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EducationResource extends RestResource
{
    public function __construct(
        private readonly EducationRepositoryInterface $educationRepository,
        private readonly ResumeRepositoryInterface $resumeRepository,
    ) {
        parent::__construct($educationRepository);
        $this->setDtoClass(EducationDto::class);
    }

    public function getRepository(): EducationRepositoryInterface
    {
        /** @var EducationRepositoryInterface $repository */
        $repository = parent::getRepository();

        return $repository;
    }

    /**
     * @return array<int, Education>
     */
    public function findByUserId(UserId $userId): array
    {
        return $this->educationRepository->findByUserIdOrdered($userId);
    }

    #[Override]
    public function beforeCreate(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforeUpdate(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforePatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->ensureResumeAssociation($restDto, $entity);
    }

    private function ensureResumeAssociation(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        if (!$restDto instanceof EducationDto || !$entity instanceof Education) {
            return;
        }

        $resumeId = $restDto->getResumeId();

        if ($resumeId === null) {
            throw new BadRequestHttpException('Resume identifier is required for education entries.');
        }

        $resume = $this->resumeRepository->find($resumeId);

        if (!$resume instanceof Resume) {
            throw new NotFoundHttpException(sprintf('Resume "%s" not found.', $resumeId));
        }

        $userId = $restDto->getUserId();

        if ($userId !== null && $userId !== (string)$resume->getUserId()) {
            throw new BadRequestHttpException('The provided userId does not match the resume owner.');
        }

        $restDto->applyResumeRelationship($entity, $resume);
    }
}
