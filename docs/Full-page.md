# Full-page caching

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

## Usage
Once the Middleware is loaded, you need to add the component to the controllers you want to make cache-able:
```php
public $components = ['Cache.Cache'];
```

If you want to provide some configuration, it is adviced to use the `initialize` callback instead, though:
```php
/**
 * @return void
 */
public function initialize() {
	$this->loadComponent('Cache.Cache', [
		'actions' => [
			...
		],
		...
	]);
}
```

The component creates the cache file, the dispatcher on the next request will discover it and deliver this static file instead as long
as the file modification date is within the allowed range.
Once the file gets too old it will be cleaned out, a real action will be called and a fresh cache file will be created.

### Global Configuration
The `CACHE` constant can be adjusted in your bootstrap and defaults to `tmp/cache/views/`.

If you need a prefix in order to allow multiple (sub)domains to deliver content in multiple languages for example, use
 `Configure::write('Cache.prefix', 'myprefix')` to separate them.

### Component Configuration
If you only want certain actions to be cached, provide them as `actions` array.
The default `cacheTime` can be set as global value, but if you want certain actions to be cached differently, use the `actions` array:
```php
'actions' => ['report', 'view' => DAY, 'index' => HOUR]
```

In case you want to further compress the output, you can either use the basic built in compressor:
```php
'compress' => true
```
or you can use any custom compressor using a callable:
```php
'compress' => function ($content, $ext) { ... }
```
The latter is useful if you want to control the compression per extension.


### Filter Configuration
In case you need to run this before other high priority filters to avoid those to be invoked, you can raise the `priority` config.
You can also adjust the `cacheTime` value for how long the browser should cache the unlimited cache files, defaults to `+1 day`.

### Clear the Cache
The Cache shell shipped with this plugin should make it easy to clear the cache manually:
```
cake cache clear [optional/url]
```

#### Further Cache Shell Goodies
Using
```
cake cache status [optional/url/]
```
you get the amount of currently cached files.

Using
```
cake cache status /some-controller/some-action/?maybe=querystrings
```
You can get information on the cache of this particular URL, e.g. how long it is still cached.


### Debugging
In debug mode or with config `debug` enabled, you will see a timestamp added as comment to the beginning of the cache file.

## Limitations
- It cannot provide partially dynamic parts as the 2.x CacheHelper could. The pages need to be completely cached.
So this is most likely a useful caching strategy for static full-page HTML or non-HTML requests like JSON, XML, CSV, ...
- Make sure you only cache public and non-personalized actions.
The dispatcher cannot know if cache files of some non-public actions are requested by an authorized user.

## TODOS
- Limit filename length to around 200 (as it includes query strings) and add md5 hashsum instead as suffix.
- Extract the common file name part into a trait for both component and filter to use.
- Allow other caching approaches than just file cache?
- Allow usage of subfolders for File cache to avoid the folder to have millions of files as a flat list?
- What happens with custom headers set in the original request? How can we pass those to the final cached response?
- Re-implement the removed CacheHelper with its nocache parts?
