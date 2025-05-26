<?php

use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->cacheService = app(CacheService::class);
});

test('it puts and gets data from cache', function () {
    $key = 'test_key';
    $value = 'test_value';

    $result = $this->cacheService->put($key, $value, 60);

    expect($result)->toBeTrue();

    $cached = $this->cacheService->get($key);
    expect($cached)->toBe($value);
});

test('it remembers data with callback', function () {
    $key = 'test_remember';
    $value = 'remembered_value';

    $result = $this->cacheService->remember($key, 60, function () use ($value) {
        return $value;
    });

    expect($result)->toBe($value);
});

test('it forgets cache key', function () {
    $key = 'test_forget';
    $value = 'test_value';

    $this->cacheService->put($key, $value, 60);
    $this->cacheService->forget($key);

    $cached = $this->cacheService->get($key);
    expect($cached)->toBeNull();
});

test('it generates cache key', function () {
    $key = $this->cacheService->generateKey('test', ['param' => 'value']);

    expect($key)->toContain('test');
    expect($key)->toBeString();
});
