# Configuration

Both the component and middleware merge global `CacheConfig` into their local
options. Local options take precedence. Put shared engine, prefix, conditions,
and key generation settings in `config/bootstrap.php`.

## Durations and actions

```php
use Cake\Core\Configure;

Configure::write('CacheConfig.duration', '+5 minutes');

$this->loadComponent('Cache.Cache', [
    'actions' => ['report', 'view' => DAY, 'index' => HOUR],
]);
```

An indexed action name uses `duration`. A named action value overrides it:
integers are seconds from now; strings are parsed by `strtotime()`. An empty
`actions` list enables all GET actions. `false` disables a named action.

The component's `duration` defaults to null, which writes no embedded expiry.
The middleware's `cacheTime` fallback sets response cache headers for such an
entry; it does not impose an expiry on an otherwise indefinite stored body.
Use an explicit `duration` to limit how long the body can be served.

## File storage

With no engine configured, entries are written under `CACHE . 'views' . DS`.
Create this directory before production use and give PHP write access. In debug
mode the component attempts to create it itself.

`prefix` separates entries, for example by deployment or language. The default
key uses the request target, including query strings, and converts it to a slug.
Different URLs can produce the same slug; use a hash generator if that matters.

## Engine storage

Define a dedicated engine configuration in the `Cache` section of `config/app.php`:

```php
'Cache' => [
    'page_views' => [
        'className' => 'File',
        'path' => CACHE . 'page_views' . DS,
        'prefix' => 'page_views_',
        'duration' => '+1 hour',
    ],
],
```

Keep the application's existing Cache configurations alongside this entry.
Then select it for both reads and writes:

```php
Configure::write('CacheConfig.engine', 'page_views');
Configure::write('CacheConfig.duration', '+5 minutes');
```

Engine expiry and embedded expiry both apply; whichever happens first removes
or bypasses the entry. The clear-all command clears the configured engine.

## Request conditions

A condition must return literal true to enable caching. Configure it globally
so the component and middleware agree:

```php
use Cake\Http\ServerRequest;

Configure::write('CacheConfig.when', function (ServerRequest $request): bool {
    return !$request->is('ajax');
});
```

Both reads and writes are limited to GET. Setting middleware `check` to false
bypasses cache lookup entirely; it does not enable caching for other methods.

## Custom keys

```php
Configure::write('CacheConfig.keyGenerator', function (string $url, ?string $prefix): string {
    return hash('sha256', ($prefix ?? '') . ':' . $url);
});
```

Use the same generator on reader and writer. Return a safe filename for file
storage, without directory separators. The plugin does not create nested folders
for custom keys. Configure a string prefix when the generator declares a non-nullable
string parameter.

## Compression and timestamps

`compress => true` runs the built-in HTML compressor when the content type maps
to HTML. It removes HTML comments and collapses whitespace, so check output that
uses whitespace-sensitive markup. Other types are left unchanged. A callable
receives the body and mapped extension and must return the resulting body:

```php
$this->loadComponent('Cache.Cache', [
    'actions' => ['index'],
    'compress' => function (string $content, string $ext): string {
        return $ext === 'html' ? trim($content) : $content;
    },
]);
```

The component appends a creation timestamp comment to HTML unless `timestamp`
is false. The middleware prepends a creation comment to cached HTML when global
debug or middleware `debug` is enabled. `force => true` enables component writes
in debug mode; it does not change middleware lookup behavior.

See [Limitations](limitations.md) for response headers and invalidation.
