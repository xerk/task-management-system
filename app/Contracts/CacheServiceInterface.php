<?php

namespace App\Contracts;

interface CacheServiceInterface
{
    /**
     * Get cached data or execute callback and cache the result
     */
    public function remember(string $key, int $ttl, callable $callback): mixed;

    /**
     * Store data in cache
     */
    public function put(string $key, mixed $value, int $ttl): bool;

    /**
     * Get data from cache
     */
    public function get(string $key): mixed;

    /**
     * Remove specific cache key
     */
    public function forget(string $key): bool;

    /**
     * Remove multiple cache keys by pattern
     */
    public function forgetByPattern(string $pattern): bool;

    /**
     * Remove cache keys by tags
     */
    public function forgetByTags(array $tags): bool;

    /**
     * Flush all cache
     */
    public function flush(): bool;

    /**
     * Generate cache key with prefix
     */
    public function generateKey(string $key, array $params = []): string;

    /**
     * Cache tasks with appropriate TTL and tags
     */
    public function cacheTaskList(string $key, callable $callback, array $filters = []): mixed;

    /**
     * Cache single task
     */
    public function cacheTask(int $taskId, callable $callback): mixed;

    /**
     * Cache comments for a task
     */
    public function cacheTaskComments(int $taskId, callable $callback): mixed;

    /**
     * Cache user comments
     */
    public function cacheUserComments(int $userId, callable $callback): mixed;

    /**
     * Invalidate task-related caches
     */
    public function invalidateTaskCaches(int $taskId, ?int $userId = null, ?string $status = null): void;

    /**
     * Invalidate comment-related caches
     */
    public function invalidateCommentCaches(int $taskId, int $userId): void;

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array;
}
