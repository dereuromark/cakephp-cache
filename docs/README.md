# Cache plugin documentation

## Enabling the Cache lookup

### Middleware
In your `/src/Application.php` add the Cache middleware right after the the assets one for example:
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
By adding the `'when'` part, we make sure it only get's invoked for GET requests.

Note: This Middleware requires CakePHP 3.4+

### DispatcherFilter
Your bootstrap needs to enable the Cache dispatcher filter:
```php
DispatcherFactory::add('Cache.Cache', [
	'when' => function ($request, $response) {
		return $request->is('get');
	}
]);
```

Note: This DispatcherFilter is **deprecated** and should only be used prior to CakePHP 3.4.

## Full-page caching

See [Full-page](Full-page.md) documentation

## Partial view caching

See [Partial](Partial.md) documentation
