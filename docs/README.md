# Cache plugin documentation

## Enabling the Cache lookup

### Middleware
In your `/src/Application.php` add the middleware right after the the assets for example:
```php
	/**
	 * @param \Cake\Http\MiddlewareQueue $middleware The middleware queue to setup.
	 * @return \Cake\Http\MiddlewareQueue The updated middleware.
	 */
	public function middleware($middleware) {
		$middleware
			...
			->add(new AssetMiddleware())

			// Handle cached files
			->add(new CacheMiddleware([
				'when' => function ($request, $response) {
            		return $request->is('get');
            	},
			]))

			...

		return $middleware;
	}
```

### Deprecated filter
Your bootstrap needs to enable the dispatcher filter:
```php
DispatcherFactory::add('Cache.Cache', [
	'when' => function ($request, $response) {
		return $request->is('get');
	}
]);
```
By adding the `'when'` part, we make sure it only get's invoked for GET requests.


## Full-page caching

See [Full-page](Full-page.md) documentation

## Partial view caching

See [Partial](Partial.md) documentation
