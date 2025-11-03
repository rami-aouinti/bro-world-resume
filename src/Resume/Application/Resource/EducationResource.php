<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource;

use App\General\Application\DTO\Interfaces\RestDtoInterface;
use App\General\Application\Rest\RestResource;
use App\General\Application\Service\AuthenticatorServiceInterface;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\DTO\Education\EducationDto;
use App\Resume\Application\Message\Command\CreateEducationMessage;
use App\Resume\Application\Resource\Traits\UserScopedResourceCacheTrait;
use App\Resume\Domain\Entity\Education;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\EducationRepositoryInterface;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use Override;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use UnexpectedValueException;

use function sprintf;

class EducationResource extends RestResource
{
    use UserScopedResourceCacheTrait;

    public function __construct(
        private readonly EducationRepositoryInterface $educationRepository,
        private readonly ResumeRepositoryInterface $resumeRepository,
        private readonly AuthenticatorServiceInterface $authenticatorService,
        #[Autowire(service: 'messenger.bus.command_bus')]
        private readonly MessageBusInterface $commandBus,
        private readonly TagAwareCacheInterface $cache,
    ) {
        parent::__construct($educationRepository);
        $this->setDtoClass(EducationDto::class);
    }

    protected function getCache(): TagAwareCacheInterface
    {
        return $this->cache;
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
    public function create(
        RestDtoInterface $dto,
        ?bool $flush = null,
        ?bool $skipValidation = null,
        ?string $entityManagerName = null
    ): EntityInterface {
        $message = new CreateEducationMessage(
            $this->ensureEducationDto($dto),
            $flush ?? true,
            $skipValidation ?? false,
            $entityManagerName
        );

        $envelope = $this->commandBus->dispatch($message);
        $handled = $envelope->last(HandledStamp::class);

        if (!$handled instanceof HandledStamp) {
            throw new RuntimeException('Education creation message was not handled.');
        }

        /** @var EntityInterface $entity */
        $entity = $handled->getResult();

        return $entity;
    }

    #[Override]
    public function find(
        ?array $criteria = null,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        ?array $search = null,
        ?string $entityManagerName = null
    ): array {
        $criteria ??= [];
        $orderBy ??= [];
        $search ??= [];

        $cacheKey = $this->buildCacheKeySuffix($criteria, $orderBy, $limit, $offset, $search, $entityManagerName);

        return $this->rememberForCurrentUser(
            'list.' . $cacheKey,
            fn (): array => $this->handleFind($criteria, $orderBy, $limit, $offset, $search, $entityManagerName)
        );
    }

    #[Override]
    public function findOne(
        string $id,
        ?bool $throwExceptionIfNotFound = null,
        ?string $entityManagerName = null
    ): ?EntityInterface {
        $cacheKey = $this->buildCacheKeySuffix($id, $throwExceptionIfNotFound, $entityManagerName);

        return $this->rememberForCurrentUser(
            'item.' . $cacheKey,
            fn (): ?EntityInterface => $this->handleFindOne($id, $throwExceptionIfNotFound, $entityManagerName)
        );
    }

    #[Override]
    public function beforeCreate(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforeUpdate(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->assertEducationOwnership($entity);
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforePatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->assertEducationOwnership($entity);
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforeDelete(string &$id, EntityInterface $entity): void
    {
        $this->assertEducationOwnership($entity);
    }

    #[Override]
    public function afterFindOne(string &$id, ?EntityInterface $entity = null): void
    {
        if ($entity instanceof Education) {
            $this->assertEducationOwnership($entity);
        }
    }

    #[Override]
    public function afterCreate(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateEducationCache($entity);
    }

    #[Override]
    public function afterUpdate(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateEducationCache($entity);
    }

    #[Override]
    public function afterPatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateEducationCache($entity);
    }

    #[Override]
    public function afterDelete(string &$id, EntityInterface $entity): void
    {
        $this->invalidateEducationCache($entity);
    }

    public function processCriteria(array &$criteria, Request $request, string $method): void
    {
        $criteria['userId'] = (string)$this->getCurrentUserId();
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

        $currentUserId = (string)$this->getCurrentUserId();

        if ((string)$resume->getUserId() !== $currentUserId) {
            throw new AccessDeniedHttpException('You cannot attach education entries to another user\'s resume.');
        }

        if ($restDto->getUserId() !== null && $restDto->getUserId() !== $currentUserId) {
            throw new AccessDeniedHttpException('You cannot manage education entries for another user.');
        }

        $restDto->setUserId($currentUserId);
        $restDto->applyResumeRelationship($entity, $resume);
    }

    private function assertEducationOwnership(EntityInterface $entity): void
    {
        if (!$entity instanceof Education) {
            return;
        }

        if ((string)$entity->getUserId() !== (string)$this->getCurrentUserId()) {
            throw new AccessDeniedHttpException('You cannot manage education entries for another user.');
        }
    }

    protected function getCurrentUserId(): UserId
    {
        $symfonyUser = $this->authenticatorService->getSymfonyUser();

        if ($symfonyUser === null) {
            throw new AccessDeniedHttpException('Authentication is required to manage education entries.');
        }

        return new UserId($symfonyUser->getUserIdentifier());
    }

    private function invalidateEducationCache(EntityInterface $entity): void
    {
        if (!$entity instanceof Education) {
            return;
        }

        $this->invalidateCacheForUser($entity->getUserId());
    }

    private function ensureEducationDto(RestDtoInterface $restDto): EducationDto
    {
        if (!$restDto instanceof EducationDto) {
            throw new UnexpectedValueException(
                sprintf('Expected instance of %s, got %s', EducationDto::class, $restDto::class)
            );
        }

        return $restDto;
    }
}
