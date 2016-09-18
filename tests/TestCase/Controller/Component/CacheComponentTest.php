<?php
namespace Cache\Test\TestCase\Controller\Component;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use TestApp\Controller\CacheComponentTestController;

/**
 */
class CacheComponentTest extends TestCase {

	/**
	 * @var \Cake\Controller\Controller
	 */
	protected $Controller;

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
		$this->Controller->response = $this->getResponseMock(['body']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . '_root.html';
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
		$this->Controller->response = $this->getResponseMock(['body']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . '_root.html';
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

		$this->Controller->response = $this->getResponseMock(['body', 'type']);

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
		$this->Controller->response = $this->getResponseMock(['body']);
		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'foo-bar.html';
		$this->assertFalse(file_exists($file));

		$this->Controller->request->params['action'] = 'baz';
		$this->Controller->request->here = '/foo/baz';
		$this->Controller->response = $this->getResponseMock(['body']);
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

		$this->Controller->response = $this->getResponseMock(['body']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar <!-- Some comment --> and

			more text.'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . '_root.html';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:0;ext:html-->Foo bar and more text.';
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

		$this->Controller->response = $this->getResponseMock(['body']);

		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar.'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . '_root.html';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:0;ext:html-->Foo b.';
		$this->assertEquals($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testFileWithBasePath() {
		$this->Controller->request->url = 'pages/view/1';
		$this->Controller->request->base = '/myapp';
		$this->Controller->request->webroot = '/myapp/';
		$this->Controller->request->here = '/myapp/pages/view/1';
		$this->Controller->response = $this->getResponseMock(['body', 'type']);
		$this->Controller->response->expects($this->once())
			->method('body')
			->will($this->returnValue('Foo bar'));
		$this->Controller->response->expects($this->once())
			->method('type')
			->will($this->returnValue('application/json'));
		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);
		$file = CACHE . 'views' . DS . 'pages-view-1.html';
		$this->assertEquals(is_file($file), true);
		unlink($file);
	}

	/**
	 * @param array $methods
	 *
	 * @return \Cake\Http\Client\Response|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getResponseMock(array $methods) {
		return $this->getMockBuilder(Response::class)->setMethods($methods)->getMock();
	}

}
