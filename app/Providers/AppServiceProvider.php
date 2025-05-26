<?php

namespace App\Providers;

use App\Services\AuthService;
use App\Services\TaskService;
use App\Services\CommentService;
use App\Services\CacheService;
use App\Contracts\AuthServiceInterface;
use App\Contracts\TaskServiceInterface;
use App\Contracts\CommentServiceInterface;
use App\Contracts\CacheServiceInterface;
use App\Repositories\UserRepository;
use App\Repositories\TaskRepository;
use App\Repositories\CommentRepository;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\CommentRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind AuthService to its interface
        $this->app->bind(AuthServiceInterface::class, AuthService::class);

        // Bind TaskService to its interface
        $this->app->bind(TaskServiceInterface::class, TaskService::class);

        // Bind CommentService to its interface
        $this->app->bind(CommentServiceInterface::class, CommentService::class);

        // Bind CacheService to its interface
        $this->app->bind(CacheServiceInterface::class, CacheService::class);

        // Bind UserRepository to its interface
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Bind TaskRepository to its interface
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);

        // Bind CommentRepository to its interface
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
