<?php

declare(strict_types=1);

namespace App\Resume\Application\Resource\Traits;

use App\General\Domain\ValueObject\UserId;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

use function serialize;
use function sha1;
use function sprintf;

/**
 * Helper trait to share user scoped cache utilities across resume resources.
 */
trait UserScopedResourceCacheTrait
{
    private int $cacheTtlInSeconds = 300;

    /**
     * @throws InvalidArgumentException
     */
    protected function rememberForCurrentUser(string $suffix, callable $callback): mixed
    {
        $userId = (string)$this->getCurrentUserId();
        $cacheKey = sprintf('%s.%s.%s', static::class, $userId, $suffix);

        return $this->getCache()->get($cacheKey, function (ItemInterface $item) use ($callback, $userId) {
            $item->tag($this->createUserCacheTag($userId));
            $item->expiresAfter($this->cacheTtlInSeconds);

            return $callback();
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function invalidateCacheForUser(UserId|string $userId): void
    {
        $id = $userId instanceof UserId ? (string)$userId : $userId;
        $this->getCache()->invalidateTags([$this->createUserCacheTag($id)]);
    }

    protected function buildCacheKeySuffix(mixed ...$parts): string
    {
        return sha1(serialize($parts));
    }

    abstract protected function getCurrentUserId(): UserId;

    abstract protected function getCache(): TagAwareCacheInterface;

    private function createUserCacheTag(string $userId): string
    {
        return sprintf('%s.user.%s', static::class, $userId);
    }
}
