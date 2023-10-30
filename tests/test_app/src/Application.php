<?php

namespace TestApp;

use Cake\Http\BaseApplication;
use Cake\Http\MiddlewareQueue;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\RouteBuilder;

class Application extends BaseApplication
{

	/**
	 * @return void
	 */
	public function bootstrap(): void
	{
	}

	/**
	 * @param \Cake\Routing\RouteBuilder $routes
	 *
	 * @return void
	 */
	public function routes(RouteBuilder $routes): void
	{
	}

	/**
	 * @param \Cake\Http\MiddlewareQueue $middlewareQueue
	 *
	 * @return \Cake\Http\MiddlewareQueue
	 */
	public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
	{
		return $middlewareQueue;
	}
}
