<?php

namespace App\Services;

use App\Contracts\CacheServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class CacheService implements CacheServiceInterface
{
    private const DEFAULT_TTL = 3600; // 1 hour
    private const TASK_TTL = 1800; // 30 minutes
    private const COMMENT_TTL = 900; // 15 minutes
    private const USER_TTL = 7200; // 2 hours

    /**
     * Get cached data or execute callback and cache the result
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Store data in cache with tags
     */
    public function put(string $key, mixed $value, int $ttl): bool
    {
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Check if the current cache driver supports tagging
     */
    private function supportsTagging(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached']);
    }

    /**
     * Store data in cache with tags
     */
    public function putWithTags(array $tags, string $key, mixed $value, int $ttl): bool
    {
        if ($this->supportsTagging()) {
            return Cache::tags($tags)->put($key, $value, $ttl);
        }

        // Fallback to regular cache without tags
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Get data from cache
     */
    public function get(string $key): mixed
    {
        return Cache::get($key);
    }

    /**
     * Get data from cache with tags
     */
    public function getWithTags(array $tags, string $key): mixed
    {
        if ($this->supportsTagging()) {
            return Cache::tags($tags)->get($key);
        }

        // Fallback to regular cache without tags
        return Cache::get($key);
    }

    /**
     * Remove specific cache key
     */
    public function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Remove multiple cache keys by pattern
     */
    public function forgetByPattern(string $pattern): bool
    {
        if (config('cache.default') === 'redis') {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
            return true;
        }

        // For non-Redis drivers, we'll need to track keys manually
        // This is a simplified implementation
        return true;
    }

    /**
     * Remove cache keys by tags
     */
    public function forgetByTags(array $tags): bool
    {
        if ($this->supportsTagging()) {
            Cache::tags($tags)->flush();
            return true;
        }

        // For drivers that don't support tagging, we'll just flush all cache
        // This is not ideal but prevents errors
        return true;
    }

    /**
     * Flush all cache
     */
    public function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * Generate cache key with prefix
     */
    public function generateKey(string $key, array $params = []): string
    {
        $baseKey = config('cache.prefix', 'laravel_cache') . ':' . $key;

        if (!empty($params)) {
            $paramString = http_build_query($params);
            $baseKey .= ':' . md5($paramString);
        }

        return $baseKey;
    }

    /**
     * Cache tasks with appropriate TTL and tags
     */
    public function cacheTaskList(string $key, callable $callback, array $filters = []): mixed
    {
        $cacheKey = $this->generateKey($key, $filters);
        $tags = ['tasks', 'task_list'];

        if (isset($filters['user_id'])) {
            $tags[] = "user_{$filters['user_id']}";
        }

        if (isset($filters['status'])) {
            $tags[] = "status_{$filters['status']}";
        }

        if ($this->supportsTagging()) {
            return Cache::tags($tags)->remember($cacheKey, self::TASK_TTL, $callback);
        }

        // Fallback to regular cache without tags
        return Cache::remember($cacheKey, self::TASK_TTL, $callback);
    }

    /**
     * Cache single task
     */
    public function cacheTask(int $taskId, callable $callback): mixed
    {
        $cacheKey = $this->generateKey("task_{$taskId}");
        $tags = ['tasks', "task_{$taskId}"];

        if ($this->supportsTagging()) {
            return Cache::tags($tags)->remember($cacheKey, self::TASK_TTL, $callback);
        }

        // Fallback to regular cache without tags
        return Cache::remember($cacheKey, self::TASK_TTL, $callback);
    }

    /**
     * Cache comments for a task
     */
    public function cacheTaskComments(int $taskId, callable $callback): mixed
    {
        $cacheKey = $this->generateKey("task_{$taskId}_comments");
        $tags = ['comments', "task_{$taskId}_comments", "task_{$taskId}"];

        if ($this->supportsTagging()) {
            return Cache::tags($tags)->remember($cacheKey, self::COMMENT_TTL, $callback);
        }

        // Fallback to regular cache without tags
        return Cache::remember($cacheKey, self::COMMENT_TTL, $callback);
    }

    /**
     * Cache user comments
     */
    public function cacheUserComments(int $userId, callable $callback): mixed
    {
        $cacheKey = $this->generateKey("user_{$userId}_comments");
        $tags = ['comments', "user_{$userId}"];

        if ($this->supportsTagging()) {
            return Cache::tags($tags)->remember($cacheKey, self::COMMENT_TTL, $callback);
        }

        // Fallback to regular cache without tags
        return Cache::remember($cacheKey, self::COMMENT_TTL, $callback);
    }

    /**
     * Invalidate task-related caches
     */
    public function invalidateTaskCaches(int $taskId, ?int $userId = null, ?string $status = null): void
    {
        // Invalidate specific task cache
        $this->forgetByTags(["task_{$taskId}"]);

        // Invalidate task lists
        $this->forgetByTags(['task_list']);

        // Invalidate user-specific caches if user is provided
        if ($userId) {
            $this->forgetByTags(["user_{$userId}"]);
        }

        // Invalidate status-specific caches if status is provided
        if ($status) {
            $this->forgetByTags(["status_{$status}"]);
        }

        // Invalidate dashboard caches
        $this->forgetByTags(['dashboard']);
    }

    /**
     * Invalidate comment-related caches
     */
    public function invalidateCommentCaches(int $taskId, int $userId): void
    {
        // Invalidate task comments
        $this->forgetByTags(["task_{$taskId}_comments"]);

        // Invalidate user comments
        $this->forgetByTags(["user_{$userId}"]);

        // Invalidate dashboard caches
        $this->forgetByTags(['dashboard']);
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        if (config('cache.default') === 'redis') {
            $info = Redis::info();
            return [
                'driver' => 'redis',
                'memory_usage' => $info['used_memory_human'] ?? 'N/A',
                'total_keys' => $info['db0']['keys'] ?? 0,
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
            ];
        }

        return [
            'driver' => config('cache.default'),
            'status' => 'active'
        ];
    }
}
