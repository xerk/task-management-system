<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Uses
|--------------------------------------------------------------------------
|
| This directive tells Pest to use specific traits or classes in your tests.
| For example, RefreshDatabase is commonly used in Feature tests.
|
*/

uses(
    Illuminate\Foundation\Testing\RefreshDatabase::class
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toBeValidationError', function (string $field) {
    return $this->toHaveKey('errors')
        ->and($this->errors)->toHaveKey($field);
});

expect()->extend('toBeSuccessfulResponse', function () {
    return $this->toHaveKey('success', true)
        ->and($this->toHaveKey('message'))
        ->and($this->toHaveKey('data'));
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createUser(array $attributes = []): App\Models\User
{
    return App\Models\User::factory()->create($attributes);
}

function createTask(array $attributes = []): App\Models\Task
{
    return App\Models\Task::factory()->create($attributes);
}

function createComment(array $attributes = []): App\Models\Comment
{
    return App\Models\Comment::factory()->create($attributes);
}

function actingAsUser(?App\Models\User $user = null): App\Models\User
{
    $user = $user ?: createUser();

    // Use Laravel's built-in actingAs for session-based authentication
    test()->actingAs($user);

    return $user;
}

function mockCache(): Mockery\MockInterface
{
    return Mockery::mock(App\Contracts\CacheServiceInterface::class);
}

function mockTaskRepository(): Mockery\MockInterface
{
    return Mockery::mock(App\Contracts\TaskRepositoryInterface::class);
}

function mockCommentRepository(): Mockery\MockInterface
{
    return Mockery::mock(App\Contracts\CommentRepositoryInterface::class);
}
