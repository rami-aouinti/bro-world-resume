<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource;

use App\General\Application\DTO\Interfaces\RestDtoInterface;
use App\General\Application\Rest\RestResource;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\DTO\Skill\SkillDto;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Entity\Skill;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use App\Resume\Domain\Repository\SkillRepositoryInterface;
use Override;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SkillResource extends RestResource
{
    public function __construct(
        private readonly SkillRepositoryInterface $skillRepository,
        private readonly ResumeRepositoryInterface $resumeRepository,
    ) {
        parent::__construct($skillRepository);
        $this->setDtoClass(SkillDto::class);
    }

    public function getRepository(): SkillRepositoryInterface
    {
        /** @var SkillRepositoryInterface $repository */
        $repository = parent::getRepository();

        return $repository;
    }

    /**
     * @return array<int, Skill>
     */
    public function findByUserId(UserId $userId): array
    {
        return $this->skillRepository->findByUserIdOrdered($userId);
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
        if (!$restDto instanceof SkillDto || !$entity instanceof Skill) {
            return;
        }

        $resumeId = $restDto->getResumeId();

        if ($resumeId === null) {
            throw new BadRequestHttpException('Resume identifier is required for skills.');
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
