<?php

namespace Cache\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\Core\Configure;
use Shim\Filesystem\Folder;
use Shim\TestSuite\TestCase;

class PageCacheClearCommandTest extends TestCase {

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
	public function testDelete() {
		$this->exec('page_cache clear', ['y']);

		$expected = 'Done!';
		$this->assertOutputContains($expected);
	}

	/**
     * @return void
     */
	public function testDeleteWithCustomKey(): void {
		Configure::write('CacheConfig.keyGenerator', fn ($url, $prefix): string => 'custom-key');
		$file = CACHE . 'views' . DS . 'custom-key.cache';
		file_put_contents($file, '<!--cachetime:' . time() . '/0;ext:html-->Private body');

		try {
			$this->exec('page_cache clear /public-page');
			$this->assertExitSuccess();
			$this->assertFileDoesNotExist($file);
			$this->assertOutputContains('Cache key custom-key deleted');
			$this->assertOutputNotContains('Private body');
		} finally {
			Configure::delete('CacheConfig.keyGenerator');
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}

}
