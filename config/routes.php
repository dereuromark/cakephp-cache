<?php

use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;

Router::plugin('Cache', function (RouteBuilder $routes) {
	$routes->fallbacks();
});
