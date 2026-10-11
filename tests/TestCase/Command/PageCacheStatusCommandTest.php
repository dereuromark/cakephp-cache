<?php

namespace Cache\Test\TestCase\Command;

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

		$this->testCacheFile = dirname(__DIR__, 2) . DS . 'test_files' . DS . 'test.cache';

		$Folder = new Folder(CACHE . 'views' . DS);
		$Folder->delete();
		if (!is_dir(CACHE . 'views' . DS)) {
			mkdir(CACHE . 'views' . DS, 0770, true);
		}

		$this->loadPlugins(['Cache']);
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
	public function testStatusWithUrl(): void {
		copy($this->testCacheFile, CACHE . 'views' . DS . 'test.cache');

		$this->exec('page_cache status /test');

		$this->assertExitSuccess();
		$this->assertOutputContains('Cached until: (unlimited)');
	}

}
