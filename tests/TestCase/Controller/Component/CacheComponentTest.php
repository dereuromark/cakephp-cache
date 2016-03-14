<?php
namespace Cache\Test\TestCase\Controller\Component;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\TestSuite\TestCase;

/**
 */
class CacheComponentTest extends TestCase {

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		Configure::write('App.namespace', 'TestApp');

		$this->Controller = new CacheComponentTestController();
		$this->Controller->startupProcess();

		$this->Controller->request->session()->delete('CacheMessage');

		$this->Controller->Cache->config('debug', true);
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();

		unset($this->Controller->Cache);
		unset($this->Controller);
	}

	/**
	 * @return void
	 */
	public function testAction() {
		$this->Controller->response = $this->getMock('Cake\Network\Response', ['body']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'home.html';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:0;ext:html-->Foo bar';
		$this->assertEquals($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithCacheTime() {
		$this->Controller->Cache->config('duration', DAY);
		$this->Controller->response = $this->getMock('Cake\Network\Response', ['body']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'home.html';
		$result = file_get_contents($file);
		$expectedTime = time() + DAY;
		$expected = '<!--cachetime:' . substr($expectedTime, 0, -1);
		$this->assertTextStartsWith($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithExt() {
		//$this->Controller->request->params['action'] = 'bar';
		$this->Controller->request->here = '/foo/bar/baz.json?x=y';

		$this->Controller->response = $this->getMock('Cake\Network\Response', ['body', 'type']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));
		$this->Controller->response->expects($this->once())
			->method('type')
			->will($this->returnValue('application/json'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'foo-bar-baz-json-x-y.html';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:0;ext:json-->Foo bar';
		$this->assertEquals($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithWhitelist() {
		$this->Controller->Cache->config('actions', ['baz']);

		$this->Controller->request->params['action'] = 'bar';
		$this->Controller->request->here = '/foo/bar';
		$this->Controller->response = $this->getMock('Cake\Network\Response', ['body']);
		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'foo-bar.html';
		$this->assertFalse(file_exists($file));

		$this->Controller->request->params['action'] = 'baz';
		$this->Controller->request->here = '/foo/baz';
		$this->Controller->response = $this->getMock('Cake\Network\Response', ['body']);
		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'foo-baz.html';
		$this->assertTrue(file_exists($file));

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithCompress() {
		$this->Controller->Cache->config('compress', true);

		$this->Controller->response = $this->getMock('Cake\Network\Response', ['body']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar <!-- Some comment --> and

			more text.'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'home.html';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:0;ext:html-->Foo bar  andmore text.';
		$this->assertEquals($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithCompressCallback() {
		$this->Controller->Cache->config('compress', function ($content) {
			$content = str_replace('bar', 'b', $content);
			return $content;
		});

		$this->Controller->response = $this->getMock('Cake\Network\Response', ['body']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar.'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'home.html';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:0;ext:html-->Foo b.';
		$this->assertEquals($expected, $result);

		unlink($file);
	}

}

/**
 * Use Controller instead of AppController to avoid conflicts
 */
class CacheComponentTestController extends Controller {

	/**
	 * @var array
	 */
	public $components = ['Cache.Cache'];

	/**
	 * @var bool
	 */
	public $failed = false;

	/**
	 * @var array
	 */
	public $testHeaders = [];

	public function fail() {
		$this->failed = true;
	}

	/**
	 * @param array|string $url
	 * @param int|null $status
	 *
	 * @return null
	 */
	public function redirect($url, $status = null) {
		return $status;
	}

	/**
	 * @param int $status
	 * @return void
	 */
	public function header($status) {
		$this->testHeaders[] = $status;
	}

}
