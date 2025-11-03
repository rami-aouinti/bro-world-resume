<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource;

use App\General\Application\DTO\Interfaces\RestDtoInterface;
use App\General\Application\Rest\RestResource;
use App\General\Application\Service\AuthenticatorServiceInterface;
use App\General\Domain\Entity\Interfaces\EntityInterface;
use App\General\Domain\ValueObject\UserId;
use App\Resume\Application\DTO\Language\LanguageDto;
use App\Resume\Application\Message\Command\CreateLanguageMessage;
use App\Resume\Application\Resource\Traits\UserScopedResourceCacheTrait;
use App\Resume\Domain\Entity\Language;
use App\Resume\Domain\Entity\Resume;
use App\Resume\Domain\Repository\LanguageRepositoryInterface;
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
 * @extends RestResource<Language>
 */
class LanguageResource extends RestResource
{
    use UserScopedResourceCacheTrait;

    public function __construct(
        private readonly LanguageRepositoryInterface $languageRepository,
        private readonly ResumeRepositoryInterface $resumeRepository,
        private readonly AuthenticatorServiceInterface $authenticatorService,
        private readonly MessageBusInterface $commandBus,
        private readonly TagAwareCacheInterface $cache,
    ) {
        parent::__construct($languageRepository);
        $this->setDtoClass(LanguageDto::class);
    }

    protected function getCache(): TagAwareCacheInterface
    {
        return $this->cache;
    }

    public function getRepository(): LanguageRepositoryInterface
    {
        /** @var LanguageRepositoryInterface $repository */
        $repository = parent::getRepository();

        return $repository;
    }

    /**
     * @return array<int, Language>
     */
    public function findByUserId(UserId $userId): array
    {
        return $this->languageRepository->findByUserIdOrdered($userId);
    }

    #[Override]
    public function create(
        RestDtoInterface $dto,
        ?bool $flush = null,
        ?bool $skipValidation = null,
        ?string $entityManagerName = null
    ): EntityInterface {
        $message = new CreateLanguageMessage(
            $this->ensureLanguageDto($dto),
            $flush ?? true,
            $skipValidation ?? false,
            $entityManagerName
        );

        $envelope = $this->commandBus->dispatch($message);
        $handled = $envelope->last(HandledStamp::class);

        if (!$handled instanceof HandledStamp) {
            throw new RuntimeException('Language creation message was not handled.');
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
        $this->assertLanguageOwnership($entity);
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforePatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->assertLanguageOwnership($entity);
        $this->ensureResumeAssociation($restDto, $entity);
    }

    #[Override]
    public function beforeDelete(string &$id, EntityInterface $entity): void
    {
        $this->assertLanguageOwnership($entity);
    }

    #[Override]
    public function afterFindOne(string &$id, ?EntityInterface $entity = null): void
    {
        if ($entity instanceof Language) {
            $this->assertLanguageOwnership($entity);
        }
    }

    #[Override]
    public function afterCreate(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateLanguageCache($entity);
    }

    #[Override]
    public function afterUpdate(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateLanguageCache($entity);
    }

    #[Override]
    public function afterPatch(string &$id, RestDtoInterface $restDto, EntityInterface $entity): void
    {
        $this->invalidateLanguageCache($entity);
    }

    #[Override]
    public function afterDelete(string &$id, EntityInterface $entity): void
    {
        $this->invalidateLanguageCache($entity);
    }

    public function processCriteria(array &$criteria, Request $request, string $method): void
    {
        $criteria['userId'] = (string)$this->getCurrentUserId();
    }

    private function ensureResumeAssociation(RestDtoInterface $restDto, EntityInterface $entity): void
    {
        if (!$restDto instanceof LanguageDto || !$entity instanceof Language) {
            return;
        }

        $resumeId = $restDto->getResumeId();

        if ($resumeId === null) {
            throw new BadRequestHttpException('Resume identifier is required for languages.');
        }

        $resume = $this->resumeRepository->find($resumeId);

        if (!$resume instanceof Resume) {
            throw new NotFoundHttpException(sprintf('Resume "%s" not found.', $resumeId));
        }

        $currentUserId = (string)$this->getCurrentUserId();

        if ((string)$resume->getUserId() !== $currentUserId) {
            throw new AccessDeniedHttpException('You cannot attach languages to another user\'s resume.');
        }

        if ($restDto->getUserId() !== null && $restDto->getUserId() !== $currentUserId) {
            throw new AccessDeniedHttpException('You cannot manage languages for another user.');
        }

        $restDto->setUserId($currentUserId);
        $restDto->applyResumeRelationship($entity, $resume);
    }

    private function assertLanguageOwnership(EntityInterface $entity): void
    {
        if (!$entity instanceof Language) {
            return;
        }

        if ((string)$entity->getUserId() !== (string)$this->getCurrentUserId()) {
            throw new AccessDeniedHttpException('You cannot manage languages for another user.');
        }
    }

    protected function getCurrentUserId(): UserId
    {
        $symfonyUser = $this->authenticatorService->getSymfonyUser();

        if ($symfonyUser === null) {
            throw new AccessDeniedHttpException('Authentication is required to manage languages.');
        }

        return new UserId($symfonyUser->getUserIdentifier());
    }

    private function invalidateLanguageCache(EntityInterface $entity): void
    {
        if (!$entity instanceof Language) {
            return;
        }

        $this->invalidateCacheForUser($entity->getUserId());
    }

    private function ensureLanguageDto(RestDtoInterface $restDto): LanguageDto
    {
        if (!$restDto instanceof LanguageDto) {
            throw new UnexpectedValueException(
                sprintf('Expected instance of %s, got %s', LanguageDto::class, $restDto::class)
            );
        }

        return $restDto;
    }
}
