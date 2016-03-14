<?php

namespace Cache\Test\TestCase\Routing\Filter;

use Cache\Routing\Filter\CacheFilter;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;

/**
 * Routing filter test.
 */
class CacheFilterTest extends TestCase {

	/**
	 * test setting parameters in beforeDispatch method
	 *
	 * @return void
	 */
	public function testBasicUrlWithExt() {
		$folder = CACHE . 'views' . DS;
		$file = $folder . 'testcontroller-testaction-params1-params2-json.html';
		$content = '<!--cachetime:0;ext:json-->Foo bar';
		file_put_contents($file, $content);

		$filter = new CacheFilter();

		$request = new Request('/testcontroller/testaction/params1/params2.json');
		$response = new Response();
		$event = new Event(__CLASS__, $this, compact('request', 'response'));
		$filter->beforeDispatch($event);

		$result = $response->body();
		$expected = 'Foo bar';
		$this->assertEquals($expected, $result);

		$result = $response->type();
		$expected = 'application/json';
		$this->assertEquals($expected, $result);

		$result = $response->header();
		$this->assertNotEmpty($result['Expires']); // + 1 day

		unlink($file);
	}

	/**
	 * test setting parameters in beforeDispatch method
	 *
	 * @return void
	 */
	public function testQueryStringAndCustomTime() {
		$folder = CACHE . 'views' . DS;
		$file = $folder . 'posts-home-coffee-life-sleep-sissies-coffee-life-sleep-sissies.html';
		$content = '<!--cachetime:' . (time() + WEEK) . ';ext:html-->Foo bar';
		file_put_contents($file, $content);

		Router::reload();
		Router::connect('/', ['controller' => 'Pages', 'action' => 'display', 'home']);
		Router::connect('/pages/*', ['controller' => 'Pages', 'action' => 'display']);
		Router::connect('/:controller/:action/*');

		$_GET = ['coffee' => 'life', 'sleep' => 'sissies'];
		$filter = new CacheFilter();
		$request = new Request('posts/home/?coffee=life&sleep=sissies');
		$response = new Response();
		$event = new Event(__CLASS__, $this, compact('request', 'response'));
		$filter->beforeDispatch($event);

		$result = $response->body();
		$expected = '<!--created:';
		$this->assertTextStartsWith($expected, $result);
		$expected = '-->Foo bar';
		$this->assertTextEndsWith($expected, $result);

		$result = $response->type();
		$expected = 'text/html';
		$this->assertEquals($expected, $result);

		$result = $response->header();
		$this->assertNotEmpty($result['Expires']); // + 1 week

		unlink($file);
	}

}
