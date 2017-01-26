# Cache plugin for CakePHP
[![Build Status](https://api.travis-ci.org/dereuromark/cakephp-cache.svg?branch=master)](https://travis-ci.org/dereuromark/cakephp-cache)
[![Coverage Status](https://coveralls.io/repos/dereuromark/cakephp-cache/badge.svg)](https://coveralls.io/r/dereuromark/cakephp-cache)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-cache/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-cache)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%205.5-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-cache/license.svg)](https://packagist.org/packages/dereuromark/cakephp-cache)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

## What is it for?
It is the successor of the 2.x CacheHelper and allows you to cache your complete views as HTML.
No dynamic parts anymore, just complete static content ready to be delivered.
If you don't want to set up ESI and other third party caching software, this CakePHP only approach
does the job.

It uses a Middleware and a Component.
Why not a helper anymore? Mainly because a helper is too limited and would
not be able to cache serialized views, e.g. JSON, CSV, RSS content which have been build view-less.

## Demo
[sandbox.dereuromark.de/sandbox/cache-examples/](http://sandbox.dereuromark.de/sandbox/cache-examples/)

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
This plugin contains a full-page view cache solution as well as a partial cache solution.

For details see [/docs](/docs).
