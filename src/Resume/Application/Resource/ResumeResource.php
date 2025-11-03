<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource;

use App\General\Application\DTO\Interfaces\RestDtoInterface;
use App\General\Application\Rest\RestResource;
use App\General\Application\Service\AuthenticatorServiceInterface;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\DTO\Resume\ResumeDto;
use App\Resume\Application\Message\Command\CreateResumeMessage;
use App\Resume\Application\Resource\Traits\UserScopedResourceCacheTrait;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\ResumeRepositoryInterface;
use Override;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use UnexpectedValueException;

use function sprintf;

/**
 * Class ResumeResource
 */
class ResumeResource extends RestResource
{
    use UserScopedResourceCacheTrait;

    public function __construct(
        private readonly ResumeRepositoryInterface $resumeRepository,
        private readonly AuthenticatorServiceInterface $authenticatorService,
        private readonly MessageBusInterface $commandBus,
        private readonly TagAwareCacheInterface $cache,
    ) {
        parent::__construct($resumeRepository);
        $this->setDtoClass(ResumeDto::class);
    }

    protected function getCache(): TagAwareCacheInterface
    {
        return $this->cache;
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
    public function create(
        RestDtoInterface $dto,
        ?bool $flush = null,
        ?bool $skipValidation = null,
        ?string $entityManagerName = null
    ): EntityInterface {
        $message = new CreateResumeMessage(
            $this->ensureResumeDto($dto),
            $flush ?? true,
            $skipValidation ?? false,
            $entityManagerName
        );

        $envelope = $this->commandBus->dispatch($message);
        $handled = $envelope->last(HandledStamp::class);

        if (!$handled instanceof HandledStamp) {
            throw new RuntimeException('Resume creation message was not handled.');
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

    #[Override]
    public function afterCreate(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateResumeCache($entity);
    }

    #[Override]
    public function afterUpdate(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateResumeCache($entity);
    }

    #[Override]
    public function afterPatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateResumeCache($entity);
    }

    #[Override]
    public function afterDelete(string &$id, EntityInterface $entity): void
    {
        $this->invalidateResumeCache($entity);
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

    protected function getCurrentUserId(): UserId
    {
        $symfonyUser = $this->authenticatorService->getSymfonyUser();

        if ($symfonyUser === null) {
            throw new AccessDeniedHttpException('Authentication is required to manage resume data.');
        }

        return new UserId($symfonyUser->getUserIdentifier());
    }

    private function invalidateResumeCache(EntityInterface $entity): void
    {
        if (!$entity instanceof Resume) {
            return;
        }

        $this->invalidateCacheForUser($entity->getUserId());
    }

    private function ensureResumeDto(RestDtoInterface $restDto): ResumeDto
    {
        if (!$restDto instanceof ResumeDto) {
            throw new UnexpectedValueException(
                sprintf('Expected instance of %s, got %s', ResumeDto::class, $restDto::class)
            );
        }

        return $restDto;
    }
}
