<?php

namespace Cache\Test\TestCase\Routing\Middleware;

use Cache\Routing\Middleware\CacheMiddleware;
use Cake\Core\Configure;
use Cake\Http\Response;
use Cake\Http\ServerRequestFactory;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 */
class CacheMiddlewareTest extends TestCase {

	/**
	 * setup
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->skipIf(version_compare(Configure::version(), '3.4') < 0);
	}

	/**
	 * Teardown
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	public function testBasicRequest() {
		$request = ServerRequestFactory::fromGlobals();
		$response = new Response();

		$middleware = new CacheMiddleware();
		$next = function ($req, $res) {
			return $res;
		};
		$newResponse = $middleware($request, $response, $next);
		$this->assertSame($response, $newResponse);
		$this->assertSame('text/html', $newResponse->type());
	}

	/**
	 * Tests setting parameters
	 *
	 * @return void
	 */
	public function testBasicUrlWithExt() {
		$folder = CACHE . 'views' . DS;
		$file = $folder . 'testcontroller-testaction-params1-params2-json.html';
		$content = '<!--cachetime:0;ext:json-->Foo bar';
		file_put_contents($file, $content);

		$request = ServerRequestFactory::fromGlobals([
			'REQUEST_URI' => '/testcontroller/testaction/params1/params2.json',
		]);
		$response = new Response();

		$middleware = new CacheMiddleware();
		$next = function ($req, $res) {
			return $res;
		};
		/* @var \Cake\Http\Response $newResponse */
		$newResponse = $middleware($request, $response, $next);

		$result = $newResponse->body();
		$expected = 'Foo bar';
		$this->assertEquals($expected, $result);

		$result = $newResponse->type();
		$expected = 'application/json';
		$this->assertEquals($expected, $result);

		$result = $newResponse->header();
		$this->assertNotEmpty($result['Expires']); // + 1 day

		unlink($file);
	}

	/**
	 * Tests that post skips
	 *
	 * @return void
	 */
	public function testBasicUrlWithExtPost() {
		$folder = CACHE . 'views' . DS;
		$file = $folder . 'testcontroller-testaction-params1-params2-json.html';
		$content = '<!--cachetime:0;ext:json-->Foo bar';
		file_put_contents($file, $content);

		$request = ServerRequestFactory::fromGlobals([
			'REQUEST_URI' => '/testcontroller/testaction/params1/params2.json',
			'REQUEST_METHOD' => 'POST',
		]);
		$this->assertTrue($request->is('post'));

		$response = new Response();

		$middleware = new CacheMiddleware([
			'when' => function ($request, $response) {
				return $request->is('get');
			},
		]);

		$next = function ($req, $res) {
			return $res;
		};
		/* @var \Cake\Http\Response $newResponse */
		$newResponse = $middleware($request, $response, $next);

		$this->assertSame('text/html', $newResponse->type());
	}

	/**
	 * Test query strings
	 *
	 * @return void
	 */
	public function testQueryStringAndCustomTime() {
		$folder = CACHE . 'views' . DS;
		$file = $folder . 'posts-home-coffee-life-sleep-sissies.html';
		$content = '<!--cachetime:' . (time() + WEEK) . ';ext:html-->Foo bar';
		file_put_contents($file, $content);

		Router::reload();
		Router::connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
		Router::connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);
		Router::connect('/:controller/:action/*');

		$_GET = ['coffee' => 'life', 'sleep' => 'sissies'];

		$request = ServerRequestFactory::fromGlobals([
			'REQUEST_URI' => '/posts/home',
		]);
		$response = new Response();

		$middleware = new CacheMiddleware();
		$next = function ($req, $res) {
			return $res;
		};
		/* @var \Cake\Http\Response $newResponse */
		$newResponse = $middleware($request, $response, $next);

		$result = $newResponse->body();
		$expected = '<!--created:';
		$this->assertTextStartsWith($expected, $result);
		$expected = '-->Foo bar';
		$this->assertTextEndsWith($expected, $result);

		$result = $newResponse->type();
		$expected = 'text/html';
		$this->assertEquals($expected, $result);

		$result = $newResponse->header();
		$this->assertNotEmpty($result['Expires']); // + 1 week

		unlink($file);
	}

}
