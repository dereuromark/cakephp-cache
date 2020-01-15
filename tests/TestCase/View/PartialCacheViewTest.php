<?php

namespace Cache\Test\TestCase\Shell;

use Cache\View\PartialCacheView;
use Shim\TestSuite\TestCase;

class PartialCacheViewTest extends TestCase {

	/**
	 * @var \Cache\View\PartialCacheView|\PHPUnit\Framework\MockObject\MockObject
	 */
	protected $PartialCacheView;

	/**
	 * @var string
	 */
	protected $testCacheFile;

	/**
	 * @var string
	 */
	protected $tmpDir;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->PartialCacheView = $this->getMockBuilder(PartialCacheView::class)
			->setMethods(['_getTemplateFileName', '_render'])
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
	public function tearDown(): void {
		parent::tearDown();
		unset($this->Shell);
	}

	/**
	 * @return void
	 */
	public function testRenderCacheMiss() {
		$this->PartialCacheView->enableAutoLayout(false);

		$result = $this->PartialCacheView->render();

		$this->assertSame('', $result);
	}

	/**
	 * @return void
	 */
	public function testRenderCacheHit() {
		$this->PartialCacheView->expects($this->once())->method('_getTemplateFileName')->willReturn('view');
		$this->PartialCacheView->enableAutoLayout(false);
		copy($this->testCacheFile, $this->tmpDir . 'view');

		$result = $this->PartialCacheView->render();

		$this->assertStringContainsString('<!--created:', $result);
		$this->assertStringContainsString('<p>Some paragraph.</p>', $result);
		$this->assertStringContainsString('<!--end-->', $result);
	}

	/**
	 * @return void
	 */
	public function testRenderCacheHitExpired() {
		$this->PartialCacheView->expects($this->once())->method('_getTemplateFileName')->willReturn('view');
		$this->PartialCacheView->expects($this->once())->method('_render')->willReturn('<b>Bold<b/>');
		$this->PartialCacheView->enableAutoLayout(false);
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
			->setMethods(['_getTemplateFileName', '_render'])
			->setConstructorArgs([null, null, null, ['compress' => true]])
			->getMock();

		$this->PartialCacheView->expects($this->once())->method('_getTemplateFileName')->willReturn('view');
		$this->PartialCacheView->expects($this->once())->method('_render')->willReturn(file_get_contents($this->testCacheFile));
		$this->PartialCacheView->enableAutoLayout(false);
		$content = file_get_contents($this->testCacheFile);
		$content = str_replace('cachetime:0', 'cachetime:' . (time() - HOUR), $content);
		file_put_contents($this->tmpDir . 'view', $content);

		$result = $this->PartialCacheView->render();

		$this->assertSame('<h1>Test</h1> <p>Some paragraph.</p>', $result);
		$this->assertSame('<!--cachetime:0--><h1>Test</h1> <p>Some paragraph.</p>', file_get_contents($this->tmpDir . 'view'));
	}

}
