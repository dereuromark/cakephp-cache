<?php

namespace Cache\Test\TestCase\Shell;

use Cache\Shell\CacheShell;
use Cake\Console\ConsoleIo;
use PHPUnit\Framework\MockObject\MockObject;
use Shim\Filesystem\Folder;
use Shim\TestSuite\ConsoleOutput;
use Shim\TestSuite\TestCase;

class CacheShellTest extends TestCase {

	protected ConsoleOutput $out;

	protected ConsoleOutput $err;

	protected string $testCacheFile;

	protected CacheShell|MockObject $Shell;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->out = new ConsoleOutput();
		$this->err = new ConsoleOutput();
		$io = new ConsoleIo($this->out, $this->err);

		$this->Shell = $this->getMockBuilder(CacheShell::class)
			->setMethods(['in', '_stop'])
			->setConstructorArgs([$io])
			->getMock();

		$this->testCacheFile = dirname(dirname(__DIR__)) . DS . 'test_files' . DS . 'test.cache';

		$Folder = new Folder(CACHE . 'views' . DS);
		$Folder->delete();
		if (!is_dir(CACHE . 'views' . DS)) {
			mkdir(CACHE . 'views' . DS, 0770, true);
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
	public function testStatus() {
		$this->Shell->runCommand(['status', '-v']);
		$output = $this->out->output();
		$expected = '0 cache files found';
		$this->assertStringContainsString($expected, $output);

		copy($this->testCacheFile, CACHE . 'views' . DS . 'test.cache');

		$this->Shell->runCommand(['status']);
		$output = $this->out->output();
		$expected = '1 cache files found';
		$this->assertStringContainsString($expected, $output);
	}

	/**
	 * @return void
	 */
	public function testStatusWithUrl() {
		copy($this->testCacheFile, CACHE . 'views' . DS . 'test.cache');

		$this->Shell->runCommand(['status', '/test']);
		$output = $this->out->output();

		$expected = 'Cached until: (unlimited)';
		$this->assertStringContainsString($expected, $output);
	}

	/**
	 * @return void
	 */
	public function testDelete() {
		$this->Shell->expects($this->any())->method('in')
			->will($this->returnValue('y'));

		$this->Shell->runCommand(['delete']);
		$output = $this->out->output();
		$expected = 'Done!';
		$this->assertStringContainsString($expected, $output);
	}

}
