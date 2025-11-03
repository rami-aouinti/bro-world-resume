<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource;

use App\General\Application\DTO\Interfaces\RestDtoInterface;
use App\General\Application\Rest\RestResource;
use App\General\Application\Service\AuthenticatorServiceInterface;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\DTO\Hobby\HobbyDto;
use App\Resume\Application\Message\Command\CreateHobbyMessage;
use App\Resume\Application\Resource\Traits\UserScopedResourceCacheTrait;
use App\Resume\Domain\Entity\Hobby;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\HobbyRepositoryInterface;
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

/**
 * @extends RestResource<Hobby>
 */
class HobbyResource extends RestResource
{
    use UserScopedResourceCacheTrait;

    public function __construct(
        private readonly HobbyRepositoryInterface $hobbyRepository,
        private readonly ResumeRepositoryInterface $resumeRepository,
        private readonly AuthenticatorServiceInterface $authenticatorService,
        private readonly MessageBusInterface $commandBus,
        private readonly TagAwareCacheInterface $cache,
    ) {
        parent::__construct($hobbyRepository);
        $this->setDtoClass(HobbyDto::class);
    }

    protected function getCache(): TagAwareCacheInterface
    {
        return $this->cache;
    }

    public function getRepository(): HobbyRepositoryInterface
    {
        /** @var HobbyRepositoryInterface $repository */
        $repository = parent::getRepository();

        return $repository;
    }

    /**
     * @return array<int, Hobby>
     */
    public function findByUserId(UserId $userId): array
    {
        return $this->hobbyRepository->findByUserIdOrdered($userId);
    }

    #[Override]
    public function create(
        RestDtoInterface $dto,
        ?bool $flush = null,
        ?bool $skipValidation = null,
        ?string $entityManagerName = null
    ): EntityInterface {
        $message = new CreateHobbyMessage(
            $this->ensureHobbyDto($dto),
            $flush ?? true,
            $skipValidation ?? false,
            $entityManagerName
        );

        $envelope = $this->commandBus->dispatch($message);
        $handled = $envelope->last(HandledStamp::class);

        if (!$handled instanceof HandledStamp) {
            throw new RuntimeException('Hobby creation message was not handled.');
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
        $this->assertHobbyOwnership($entity);
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforePatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->assertHobbyOwnership($entity);
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforeDelete(string &$id, EntityInterface $entity): void
    {
        $this->assertHobbyOwnership($entity);
    }

    #[Override]
    public function afterFindOne(string &$id, ?EntityInterface $entity = null): void
    {
        if ($entity instanceof Hobby) {
            $this->assertHobbyOwnership($entity);
        }
    }

    #[Override]
    public function afterCreate(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateHobbyCache($entity);
    }

    #[Override]
    public function afterUpdate(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateHobbyCache($entity);
    }

    #[Override]
    public function afterPatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateHobbyCache($entity);
    }

    #[Override]
    public function afterDelete(string &$id, EntityInterface $entity): void
    {
        $this->invalidateHobbyCache($entity);
    }

    public function processCriteria(array &$criteria, Request $request, string $method): void
    {
        $criteria['userId'] = (string)$this->getCurrentUserId();
    }

    private function ensureResumeAssociation(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        if (!$restDto instanceof HobbyDto || !$entity instanceof Hobby) {
            return;
        }

        $resumeId = $restDto->getResumeId();

        if ($resumeId === null) {
            throw new BadRequestHttpException('Resume identifier is required for hobbies.');
        }

        $resume = $this->resumeRepository->find($resumeId);

        if (!$resume instanceof Resume) {
            throw new NotFoundHttpException(sprintf('Resume "%s" not found.', $resumeId));
        }

        $currentUserId = (string)$this->getCurrentUserId();

        if ((string)$resume->getUserId() !== $currentUserId) {
            throw new AccessDeniedHttpException('You cannot attach hobbies to another user\'s resume.');
        }

        if ($restDto->getUserId() !== null && $restDto->getUserId() !== $currentUserId) {
            throw new AccessDeniedHttpException('You cannot manage hobbies for another user.');
        }

        $restDto->setUserId($currentUserId);
        $restDto->applyResumeRelationship($entity, $resume);
    }

    private function assertHobbyOwnership(EntityInterface $entity): void
    {
        if (!$entity instanceof Hobby) {
            return;
        }

        if ((string)$entity->getUserId() !== (string)$this->getCurrentUserId()) {
            throw new AccessDeniedHttpException('You cannot manage hobbies for another user.');
        }
    }

    protected function getCurrentUserId(): UserId
    {
        $symfonyUser = $this->authenticatorService->getSymfonyUser();

        if ($symfonyUser === null) {
            throw new AccessDeniedHttpException('Authentication is required to manage hobbies.');
        }

        return new UserId($symfonyUser->getUserIdentifier());
    }

    private function invalidateHobbyCache(EntityInterface $entity): void
    {
        if (!$entity instanceof Hobby) {
            return;
        }

        $this->invalidateCacheForUser($entity->getUserId());
    }

    private function ensureHobbyDto(RestDtoInterface $restDto): HobbyDto
    {
        if (!$restDto instanceof HobbyDto) {
            throw new UnexpectedValueException(
                sprintf('Expected instance of %s, got %s', HobbyDto::class, $restDto::class)
            );
        }

        return $restDto;
    }
}
