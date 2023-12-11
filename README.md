# Cache plugin for CakePHP
[![CI](https://github.com/dereuromark/cakephp-cache/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/dereuromark/cakephp-cache/actions/workflows/ci.yml?query=branch%3Amaster)
[![Coverage Status](https://img.shields.io/codecov/c/github/dereuromark/cakephp-cache/master.svg)](https://codecov.io/github/dereuromark/cakephp-cache/branch/master)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-cache/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-cache)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-cache/license.svg)](LICENSE)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

This branch is for use with **CakePHP 5.0+**. For details see [version map](https://github.com/dereuromark/cakephp-cache/wiki#cakephp-version-map).

## What is it for?
It is the successor of the 2.x CacheHelper and allows you to cache your complete views as HTML.
No dynamic parts anymore, just complete static content ready to be delivered.
If you don't want to set up ESI and other third party caching software, this CakePHP only approach
does the job.

It uses a Middleware and a Component.
Why not a helper anymore? Mainly because a helper is too limited and would
not be able to cache serialized views, e.g. JSON, CSV, RSS content which have been build view-less.

## Demo
[sandbox.dereuromark.de/sandbox/cache-examples/](https://sandbox.dereuromark.de/sandbox/cache-examples/)

## Installation

You can install this plugin into your CakePHP application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:
```
composer require dereuromark/cakephp-cache
```

Also don't forget to load the plugin in your `Application` class or by running:
```
bin/cake plugin load Cache
```

## Usage
This plugin contains a full-page view cache solution for your controller actions.
You can use file-based (default) or any engine based (e.g. Memcache, Redis, ...) cache strategy.

For details see [/docs](/docs).
