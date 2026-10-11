# Getting started

Start with an existing public controller action, such as `PagesController::display()`.
Complete [Installation](installation.md) first.

## Configure a short duration

Add this to `config/bootstrap.php`. Reuse its existing Configure import:

```php
Configure::write('CacheConfig', [
    'duration' => '+5 minutes',
    'prefix' => 'public_pages',
]);
```

For the default file storage, create `tmp/cache/views` and make it writable by
the PHP process. The plugin writes under `CACHE . 'views' . DS`; changing `CACHE`
affects other application caches too.

## Add the middleware

Import the middleware in `src/Application.php`:

```php
use Cache\Routing\Middleware\CacheMiddleware;
```

In your existing `middleware()` method, add this immediately after AssetMiddleware,
before RoutingMiddleware:

```php
$middlewareQueue->insertAfter(\Cake\Http\Middleware\AssetMiddleware::class, new CacheMiddleware());
```

Keep the application's other middleware. A cache hit skips everything after
CacheMiddleware, so this placement is for public pages only.

## Add the component

In the controller's existing `initialize()` method, retain `parent::initialize()`
and add:

```php
$this->loadComponent('Cache.Cache', [
    'actions' => ['display'],
]);
```

Only enable actions that serve the same public body for every caller. An empty
`actions` list caches all GET actions on that controller.

## Try it

In production mode, request the page twice. The first request runs the controller
and writes a cache entry; the second can use the cached body. In debug mode the
component does not write entries unless you temporarily set `'force' => true`.
Remove that override after local testing.

```bash
bin/cake page_cache status
bin/cake page_cache clear
```

See [Configuration](../reference/configuration.md) for per-action durations,
engine storage, and matching conditions for reads and writes.
