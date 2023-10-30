<?php

namespace Cache\Test\TestCase\Command;

use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Shim\TestSuite\ConsoleOutput;
use Shim\TestSuite\TestCase;

class PageCacheClearCommandTest extends TestCase {

	use ConsoleIntegrationTestTrait;

	protected ConsoleOutput $out;

	protected ConsoleOutput $err;

	protected string $testCacheFile;

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		/*
		//FIXME: Use exec()
		$this->Shell = $this->getMockBuilder(PageCacheStatusCommand::class)
			//->onlyMethods(['in', '_stop'])
			//->setConstructorArgs([$io])
			->getMock();

		$this->testCacheFile = dirname(dirname(__DIR__)) . DS . 'test_files' . DS . 'test.cache';

		$Folder = new Folder(CACHE . 'views' . DS);
		$Folder->delete();
		if (!is_dir(CACHE . 'views' . DS)) {
			mkdir(CACHE . 'views' . DS, 0770, true);
		}
		*/
	}

	/**
	 * @return void
	 */
	public function testDelete() {
		$this->exec('page_cache clear', ['y']);

		$expected = 'Done!';
		$this->assertOutputContains($expected);
	}

}
