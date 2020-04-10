# Cache plugin for CakePHP
[![Build Status](https://api.travis-ci.com/dereuromark/cakephp-cache.svg?branch=master)](https://travis-ci.com/dereuromark/cakephp-cache)
[![Coverage Status](https://codecov.io/gh/dereuromark/cakephp-cache/branch/master/graph/badge.svg)](https://codecov.io/gh/dereuromark/cakephp-cache)
[![Latest Stable Version](https://poser.pugx.org/dereuromark/cakephp-cache/v/stable.svg)](https://packagist.org/packages/dereuromark/cakephp-cache)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.2-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/dereuromark/cakephp-cache/license.svg)](https://packagist.org/packages/dereuromark/cakephp-cache)
[![Coding Standards](https://img.shields.io/badge/cs-PSR--2--R-yellow.svg)](https://github.com/php-fig-rectified/fig-rectified-standards)

This branch is for use with **CakePHP 4.0+**.

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
This plugin contains a full-page view cache solution as well as a partial cache solution.

For details see [/docs](/docs).
