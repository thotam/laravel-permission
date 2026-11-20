<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Tests\TestSupport\TestModels\User;

beforeEach(function () {
    $this->registrar = app(PermissionRegistrar::class);
    $this->registrar->forgetCachedPermissions();
    $this->scope = 'site-a';
});

afterEach(function () {
    PermissionRegistrar::resolveCacheScopeUsing(null);
});

// Creates a permission without the model events that would flush the cache.
function createPermissionQuietly(string $name): void
{
    $class = app(Permission::class)::class;
    $class::withoutEvents(fn () => $class::create(['name' => $name]));
}

function useScopeOf(object $test): void
{
    PermissionRegistrar::resolveCacheScopeUsing(fn () => $test->scope);
}

it('uses the configured cache key when no scope resolver is set', function () {
    $this->registrar->getPermissions();

    expect($this->registrar->getCacheKey())->toBe('spatie.permission.cache')
        ->and($this->registrar->getCacheRepository()->has('spatie.permission.cache'))->toBeTrue();
});

it('suffixes the cache key with the resolved scope', function () {
    useScopeOf($this);

    $this->registrar->getPermissions();

    expect($this->registrar->getCacheKey())->toBe('spatie.permission.cache.site-a')
        ->and($this->registrar->getCacheRepository()->has('spatie.permission.cache.site-a'))->toBeTrue()
        ->and($this->registrar->getCacheRepository()->has('spatie.permission.cache'))->toBeFalse();
});

it('falls back to the configured key when the resolver returns null', function () {
    PermissionRegistrar::resolveCacheScopeUsing(fn () => null);

    expect($this->registrar->getCacheKey())->toBe('spatie.permission.cache');
});

it('reloads permissions when the scope changes', function () {
    useScopeOf($this);
    expect($this->registrar->getPermissions(['name' => 'scoped-only']))->toHaveCount(0);

    createPermissionQuietly('scoped-only');

    $this->scope = 'site-b';
    expect($this->registrar->getPermissions(['name' => 'scoped-only']))->toHaveCount(1);

    // site-a still serves its own cached list
    $this->scope = 'site-a';
    expect($this->registrar->getPermissions(['name' => 'scoped-only']))->toHaveCount(0);

    $cache = $this->registrar->getCacheRepository();
    expect($cache->has('spatie.permission.cache.site-a'))->toBeTrue()
        ->and($cache->has('spatie.permission.cache.site-b'))->toBeTrue();
});

it('checks user permissions against the current scope', function () {
    useScopeOf($this);
    $user = User::create(['email' => 'scope@user.com']);
    $this->registrar->getPermissions();

    createPermissionQuietly('scoped-only');

    $this->scope = 'site-b';
    $user->givePermissionTo('scoped-only');

    expect($user->fresh()->checkPermissionTo('scoped-only'))->toBeTrue();
});

it('finds permissions by name and id against the current scope', function () {
    useScopeOf($this);
    $this->registrar->getPermissions();

    createPermissionQuietly('scoped-only');
    $class = app(Permission::class)::class;
    $id = $class::query()->where('name', 'scoped-only')->value('id');

    $this->scope = 'site-b';
    expect($class::findByName('scoped-only')->getKey())->toBe($id)
        ->and($class::findById($id)->name)->toBe('scoped-only');

    $this->scope = 'site-a';
    expect(fn () => $class::findByName('scoped-only'))->toThrow(PermissionDoesNotExist::class)
        ->and(fn () => $class::findById($id))->toThrow(PermissionDoesNotExist::class);
});

it('drops the lookup index and returned models when the scope changes', function () {
    useScopeOf($this);
    $this->registrar->getPermissions();

    $index = new ReflectionProperty($this->registrar, 'permissionsLookupIndex');
    $returned = new ReflectionProperty($this->registrar, 'returnedPermissions');
    $index->setValue($this->registrar, ['name' => ['web' => ['stale' => 0]]]);
    $returned->setValue($this->registrar, [1 => ['stale']]);

    $this->scope = 'site-b';
    (new ReflectionMethod($this->registrar, 'syncCacheScope'))->invoke($this->registrar);

    expect($index->getValue($this->registrar))->toBe([])
        ->and($returned->getValue($this->registrar))->toBe([]);
});

it('forgets only the cache key of the current scope', function () {
    useScopeOf($this);
    $this->registrar->getPermissions();
    $this->scope = 'site-b';
    $this->registrar->getPermissions();

    $this->registrar->forgetCachedPermissions();

    $cache = $this->registrar->getCacheRepository();
    expect($cache->has('spatie.permission.cache.site-b'))->toBeFalse()
        ->and($cache->has('spatie.permission.cache.site-a'))->toBeTrue();
});

it('resets only the current scope cache key from the artisan command', function () {
    useScopeOf($this);
    $cache = $this->registrar->getCacheRepository();
    $cache->put('spatie.permission.cache.site-a', 'cached-a');
    $cache->put('spatie.permission.cache.site-b', 'cached-b');

    Artisan::call('permission:cache-reset');

    expect(Artisan::output())->toContain('Permission cache flushed.')
        ->and($cache->has('spatie.permission.cache.site-a'))->toBeFalse()
        ->and($cache->get('spatie.permission.cache.site-b'))->toBe('cached-b');
});
