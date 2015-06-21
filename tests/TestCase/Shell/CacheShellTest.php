<?php
namespace Cache\Test\TestCase\Shell;

use Tools\Shell\CacheShell;
use Tools\TestSuite\ConsoleOutput;
use Cake\Console\ConsoleIo;
use Tools\TestSuite\TestCase;

/**
 */
class CacheShellTest extends TestCase {

	/**
	 * setUp method
	 *
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->out = new ConsoleOutput();
		$this->err = new ConsoleOutput();
		$io = new ConsoleIo($this->out, $this->err);

		$this->Shell = $this->getMock(
			'Cache\Shell\CacheShell',
			['in', '_stop'],
			[$io]
		);
	}

	/**
	 * tearDown
	 *
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Shell);
	}

	/**
	 * test that the startup method supresses the shell header
	 *
	 * @return void
	 */
	public function testMain() {
		$this->Shell->runCommand(['info']);
		$output = $this->out->output();
		$expected = '0 cache files found';
		$this->assertContains($expected, $output);
	}

	/**
	 * test that the startup method supresses the shell header
	 *
	 * @return void
	 */
	public function testClear() {
		$this->Shell->expects($this->any())->method('in')
			->will($this->returnValue('y'));

		$this->Shell->runCommand(['clear']);
		$output = $this->out->output();
		$expected = 'Done!';
		$this->assertContains($expected, $output);
	}

}
