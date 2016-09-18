<?php
namespace Cache\Test\TestCase\Shell;

use Cache\View\PartialCacheView;
use Tools\TestSuite\TestCase;

/**
 */
class PartialCacheViewTest extends TestCase {

	/**
	 * @var \Cache\View\PartialCacheView|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $PartialCacheView;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->PartialCacheView = $this->getMockBuilder(PartialCacheView::class)
			->setMethods(['_getViewFileName', '_render'])
			->getMock();
		$this->testCacheFile = dirname(dirname(__DIR__)) . DS . 'test_files' . DS . 'partial' . DS . 'view.html';
		$this->tmpDir = CACHE . 'views' . DS;
		if (!is_dir($this->tmpDir)) {
			mkdir($this->tmpDir, 0770, true);
		}
	}

	/**
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Shell);
	}

	/**
	 * @return void
	 */
	public function testRenderCacheMiss() {
		$this->PartialCacheView->autoLayout(false);

		$result = $this->PartialCacheView->render();

		$this->assertSame('', $result);
	}

	/**
	 * @return void
	 */
	public function testRenderCacheHit() {
		$this->PartialCacheView->expects($this->once())->method('_getViewFileName')->willReturn('view');
		$this->PartialCacheView->autoLayout(false);
		copy($this->testCacheFile, $this->tmpDir . 'view');

		$result = $this->PartialCacheView->render();

		$this->assertContains('<!--created:', $result);
		$this->assertContains('<p>Some paragraph.</p>', $result);
		$this->assertContains('<!--end-->', $result);
	}

	/**
	 * @return void
	 */
	public function testRenderCacheHitExpired() {
		$this->PartialCacheView->expects($this->once())->method('_getViewFileName')->willReturn('view');
		$this->PartialCacheView->expects($this->once())->method('_render')->willReturn('<b>Bold<b/>');
		$this->PartialCacheView->autoLayout(false);
		$content = file_get_contents($this->testCacheFile);
		$content = str_replace('cachetime:0', 'cachetime:' . (time() - HOUR), $content);
		file_put_contents($this->tmpDir . 'view', $content);

		$result = $this->PartialCacheView->render();

		$this->assertSame('<b>Bold<b/>', $result);
		$this->assertSame('<!--cachetime:0--><b>Bold<b/>', file_get_contents($this->tmpDir . 'view'));
	}

	/**
	 * @return void
	 */
	public function testRenderCompress() {
		$this->PartialCacheView = $this->getMockBuilder(PartialCacheView::class)
			->setMethods(['_getViewFileName', '_render'])
			->setConstructorArgs([null, null, null, ['compress' => true]])
			->getMock();

		$this->PartialCacheView->expects($this->once())->method('_getViewFileName')->willReturn('view');
		$this->PartialCacheView->expects($this->once())->method('_render')->willReturn(file_get_contents($this->testCacheFile));
		$this->PartialCacheView->autoLayout(false);
		$content = file_get_contents($this->testCacheFile);
		$content = str_replace('cachetime:0', 'cachetime:' . (time() - HOUR), $content);
		file_put_contents($this->tmpDir . 'view', $content);

		$result = $this->PartialCacheView->render();

		$this->assertSame('<h1>Test</h1> <p>Some paragraph.</p>', $result);
		$this->assertSame('<!--cachetime:0--><h1>Test</h1> <p>Some paragraph.</p>', file_get_contents($this->tmpDir . 'view'));
	}

}
