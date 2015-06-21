# Cache plugin for CakePHP

## What is it for?
It is the successor of the 2.x CacheHelper and allows you to cache your complete views as HTML.
No dynamic parts anymore, just complete static content ready to be delivered.
If you don't want to set up ESI and other third party caching software, this CakePHP only approach
does the job.

It uses a dispatcher and a component.
Why not a helper anymore? Mainly because a helper is too limited and would
not be able to cache serialized views, e.g. JSON, CSV, RSS content which have been build view-less.

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require dereuromark/cakephp-cache
```

Also don't forget to load the plugin in your bootstrap:
```php
Plugin::load('Cache');
// or
Plugin::loadAll();
```

## Usage
You need to add the component to the controllers you want to make cache-able:
```php
public $components = ['Cache.Cache'];
```

And your bootstrap needs to enable the dispatcher filter:
```php
DispatcherFactory::add('Cache.Cache');
```

The component creates the cache file, the dispatcher on the next request will discover it and deliver this static file instead as long
as the file modification date is within the allowed range. Once the file got too old it will be cleaned out.
The next complete dispatching process will make the component create a fresh cache file then.

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

### Filter Configuration
In case you need to run this before other high priority filters to avoid those to be invoked, you can raise the `priority` config.

### Clear the cache
The Cache shell shipped with this plugin should make it easy to clear the cache manually:
```
cake cache clear [optional/url/beginning]
```

### Debugging
In debug mode or with config `debug` enabled, you will see a timestamp added as comment to the beginning of the cache file.

## TODOS
- Limit filename length to 200 (as it includes query strings) and add md5 hashsum instead as suffix
- Allow other caching approaches than just file cache
- Allow usage of subfolders for File cache to avoid the folder to have millions of files as a flat list
- What happens with custom headers set in the original request? How can we pass those to the final cached response?
- Re-implement the removed CacheHelper with its nocache parts?
- Backport to 2.x?