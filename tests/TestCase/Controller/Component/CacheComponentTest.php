<?php

namespace Cache\Test\TestCase\Controller\Component;

use Cake\Event\Event;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\StreamFactory;
use TestApp\Controller\CacheComponentTestController;

class CacheComponentTest extends TestCase {

	/**
	 * @var \Cake\Controller\Controller
	 */
	protected $Controller;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->Controller = new CacheComponentTestController();
		$this->Controller->startupProcess();

		$this->Controller->getRequest()->getSession()->delete('CacheMessage');

		$this->Controller->Cache->setConfig('debug', true);
		$this->Controller->Cache->setConfig('force', true);
	}

	/**
	 * @return void
	 */
	public function tearDown(): void {
		parent::tearDown();

		unset($this->Controller->Cache);
		unset($this->Controller);
	}

	/**
	 * @return void
	 */
	public function testAction() {
		$this->Controller->setResponse($this->getResponseMock(['getBody']));

		$this->Controller->getResponse()->expects($this->once())
			->method('getBody')
			->will($this->returnValue((new StreamFactory())->createStream('Foo bar.')));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . '_root.cache';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:';
		$this->assertTextContains($expected, $result);
		$expected = '/0;ext:html-->Foo bar.';
		$this->assertTextContains($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithCacheTime() {
		$this->Controller->Cache->setConfig('duration', DAY);
		$this->Controller->setResponse($this->getResponseMock(['getBody']));

		$this->Controller->getResponse()->expects($this->once())
			->method('getBody')
			->will($this->returnValue((new StreamFactory())->createStream('Foo bar.')));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . '_root.cache';
		$result = file_get_contents($file);
		$time = time();
		$expectedTime = $time + DAY;
		$expected = '/' . substr($expectedTime, 0, -1);
		$this->assertTextContains($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithExt() {
		$request = new ServerRequest([
			'url' => '/foo/bar/baz.json?x=y',
		]);
		$this->Controller->setRequest($request);
		$this->Controller->setResponse($this->getResponseMock(['getBody', 'getType']));

		$this->Controller->getResponse()->expects($this->once())
			->method('getBody')
			->will($this->returnValue((new StreamFactory())->createStream('Foo bar.')));
		$this->Controller->getResponse()->expects($this->once())
			->method('getType')
			->will($this->returnValue('application/json'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'foo-bar-baz-json-x-y.cache';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:';
		$this->assertTextContains($expected, $result);
		$expected = '/0;ext:json-->Foo bar.';
		$this->assertTextContains($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithWhitelist() {
		$this->Controller->Cache->setConfig('actions', ['baz']);

		$request = new ServerRequest([
			'url' => '/foo/bar',
			'params' => [
				'action' => 'bar',
			],
			'environment' => [
				'REQUEST_METHOD' => 'GET',
			],
		]);

		$this->Controller->setRequest($request);
		$this->Controller->setResponse($this->getResponseMock(['getBody']));
		$this->Controller->getResponse()->expects($this->once())
			->method('getBody')
			->will($this->returnValue((new StreamFactory())->createStream('Foo bar.')));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'foo-bar.cache';
		$this->assertFalse(file_exists($file));

		$request = new ServerRequest([
			'url' => '/foo/baz',
			'params' => [
				'action' => 'baz',
			],
			'environment' => [
				'REQUEST_METHOD' => 'GET',
			],
		]);
		$this->Controller->setRequest($request);
		$this->Controller->setResponse($this->getResponseMock(['getBody']));
		$this->Controller->getResponse()->expects($this->once())
			->method('getBody')
			->will($this->returnValue((new StreamFactory())->createStream('Foo bar.')));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . 'foo-baz.cache';
		$this->assertFileExists($file);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithCompress() {
		$this->Controller->Cache->setConfig('compress', true);

		$this->Controller->setResponse($this->getResponseMock(['getBody']));

		$text = (new StreamFactory())->createStream('Foo bar <!-- Some comment --> and

			more text.');
		$this->Controller->getResponse()->expects($this->once())
			->method('getBody')
			->will($this->returnValue($text));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . '_root.cache';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:';
		$this->assertTextContains($expected, $result);
		$expected = '/0;ext:html-->Foo bar and more text.';
		$this->assertTextContains($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testActionWithCompressCallback() {
		$this->Controller->Cache->setConfig('compress', function ($content) {
			$content = str_replace('bar', 'b', $content);
			return $content;
		});

		$this->Controller->setResponse($this->getResponseMock(['getBody']));

		$this->Controller->getResponse()->expects($this->once())
			->method('getBody')
			->will($this->returnValue((new StreamFactory())->createStream('Foo bar.')));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);

		$file = CACHE . 'views' . DS . '_root.cache';
		$result = file_get_contents($file);
		$expected = '<!--cachetime:';
		$this->assertTextContains($expected, $result);
		$expected = '/0;ext:html-->Foo b.';
		$this->assertTextContains($expected, $result);

		unlink($file);
	}

	/**
	 * @return void
	 */
	public function testFileWithBasePath() {
		$request = new ServerRequest([
			'url' => '/myapp/pages/view/1',
			'base' => '/myapp',
			'environment' => [
				'REQUEST_METHOD' => 'GET',
			],
		]);

		$this->Controller->setRequest($request);
		$this->Controller->setResponse($this->getResponseMock(['getBody', 'getType']));
		$this->Controller->getResponse()->expects($this->once())
			->method('getBody')
			->will($this->returnValue((new StreamFactory())->createStream('Foo bar')));
		$this->Controller->getResponse()->expects($this->once())
			->method('getType')
			->will($this->returnValue('text/html'));

		$event = new Event('Controller.shutdown', $this->Controller);
		$this->Controller->Cache->shutdown($event);
		$file = CACHE . 'views' . DS . 'pages-view-1.cache';
		$this->assertFileExists($file);
		unlink($file);
	}

	/**
	 * @param array $methods
	 *
	 * @return \Cake\Http\Response|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected function getResponseMock(array $methods) {
		return $this->getMockBuilder(Response::class)->setMethods($methods)->getMock();
	}

}
