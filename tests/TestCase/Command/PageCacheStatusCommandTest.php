<?php

namespace Cache\Test\TestCase\Command;

use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Shim\Filesystem\Folder;
use Shim\TestSuite\TestCase;

class PageCacheStatusCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	protected string $testCacheFile;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

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
	public function testStatusEmpty() {
		$this->exec('page_cache status -v');

		$this->assertExitCode(0);
		$expected = '0 cache files found';
		$this->assertOutputContains($expected);
	}

	/**
	 * @return void
	 */
	public function testStatus() {
		copy($this->testCacheFile, CACHE . 'views' . DS . 'test.cache');

		$this->exec('page_cache status -v');

		$output = $this->output();
		$this->assertExitCode(0);
		$expected = '1 cache files found';
		$this->assertOutputContains($expected, $output);
	}

	/**
	 * @return void
	 */
	public function _testStatusWithUrl() {
		copy($this->testCacheFile, CACHE . 'views' . DS . 'test.cache');

		$io = new ConsoleIo($this->out, $this->err);
		$this->Shell->run(['status', '/test'], $io);
		$output = $this->out->output();

		$expected = 'Cached until: (unlimited)';
		$this->assertStringContainsString($expected, $output);
	}

}
