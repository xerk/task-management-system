<?php

namespace App\Console\Commands;

use App\Contracts\CacheServiceInterface;
use Illuminate\Console\Command;

class CacheManagementCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'cache:manage
                            {action : The action to perform (stats|warm|clear|clear-tags)}
                            {--tags=* : Cache tags to clear (for clear-tags action)}
                            {--pattern= : Cache pattern to clear (for clear action)}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Manage application cache with advanced operations';

  public function __construct(
    private readonly CacheServiceInterface $cacheService
  ) {
    parent::__construct();
  }

  /**
   * Execute the console command.
   */
  public function handle(): int
  {
    $action = $this->argument('action');

    return match ($action) {
      'stats' => $this->showCacheStats(),
      'warm' => $this->warmUpCache(),
      'clear' => $this->clearCache(),
      'clear-tags' => $this->clearCacheByTags(),
      default => $this->error("Invalid action: {$action}. Available actions: stats, warm, clear, clear-tags")
    };
  }

  /**
   * Show cache statistics
   */
  private function showCacheStats(): int
  {
    $this->info('📊 Cache Statistics');
    $this->line('==================');

    try {
      $stats = $this->cacheService->getCacheStats();

      $this->table(
        ['Metric', 'Value'],
        [
          ['Driver', $stats['driver']],
          ['Memory Usage', $stats['memory_usage'] ?? 'N/A'],
          ['Total Keys', $stats['total_keys'] ?? 'N/A'],
          ['Cache Hits', $stats['hits'] ?? 'N/A'],
          ['Cache Misses', $stats['misses'] ?? 'N/A'],
          ['Hit Ratio', $this->calculateHitRatio($stats)],
        ]
      );

      $this->info('✅ Cache statistics retrieved successfully');
      return Command::SUCCESS;
    } catch (\Exception $e) {
      $this->error("❌ Failed to retrieve cache statistics: {$e->getMessage()}");
      return Command::FAILURE;
    }
  }

  /**
   * Warm up cache with frequently accessed data
   */
  private function warmUpCache(): int
  {
    $this->info('🔥 Warming up cache...');

    try {
      $this->cacheService->warmUpCache();

      $this->info('✅ Cache warmed up successfully');
      return Command::SUCCESS;
    } catch (\Exception $e) {
      $this->error("❌ Failed to warm up cache: {$e->getMessage()}");
      return Command::FAILURE;
    }
  }

  /**
   * Clear cache by pattern or flush all
   */
  private function clearCache(): int
  {
    $pattern = $this->option('pattern');

    if ($pattern) {
      $this->info("🧹 Clearing cache with pattern: {$pattern}");

      try {
        $this->cacheService->forgetByPattern($pattern);
        $this->info('✅ Cache cleared by pattern successfully');
        return Command::SUCCESS;
      } catch (\Exception $e) {
        $this->error("❌ Failed to clear cache by pattern: {$e->getMessage()}");
        return Command::FAILURE;
      }
    }

    if ($this->confirm('Are you sure you want to flush ALL cache?', false)) {
      $this->info('🧹 Flushing all cache...');

      try {
        $this->cacheService->flush();
        $this->info('✅ All cache flushed successfully');
        return Command::SUCCESS;
      } catch (\Exception $e) {
        $this->error("❌ Failed to flush cache: {$e->getMessage()}");
        return Command::FAILURE;
      }
    }

    $this->info('Cache flush cancelled');
    return Command::SUCCESS;
  }

  /**
   * Clear cache by tags
   */
  private function clearCacheByTags(): int
  {
    $tags = $this->option('tags');

    if (empty($tags)) {
      $this->error('❌ No tags specified. Use --tags option to specify cache tags to clear.');
      return Command::FAILURE;
    }

    $this->info('🧹 Clearing cache for tags: ' . implode(', ', $tags));

    try {
      $this->cacheService->forgetByTags($tags);
      $this->info('✅ Cache cleared by tags successfully');
      return Command::SUCCESS;
    } catch (\Exception $e) {
      $this->error("❌ Failed to clear cache by tags: {$e->getMessage()}");
      return Command::FAILURE;
    }
  }

  /**
   * Calculate hit ratio percentage
   */
  private function calculateHitRatio(array $stats): string
  {
    if (!isset($stats['hits']) || !isset($stats['misses'])) {
      return 'N/A';
    }

    $hits = (int) $stats['hits'];
    $misses = (int) $stats['misses'];
    $total = $hits + $misses;

    if ($total === 0) {
      return '0%';
    }

    $ratio = ($hits / $total) * 100;
    return number_format($ratio, 2) . '%';
  }
}
