<?php
use Cake\Routing\Router;

Router::plugin('Cache', function ($routes) {
	$routes->fallbacks('DashedRoute');
});
