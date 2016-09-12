<?php
namespace Cache\Test\TestCase\Shell;

use Cache\Shell\CacheShell;
use Cake\Console\ConsoleIo;
use Tools\TestSuite\ConsoleOutput;
use Tools\TestSuite\TestCase;

/**
 */
class CacheShellTest extends TestCase {

	/**
	 * @var \Cache\Shell\CacheShell|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected $Shell;

	/**
	 * @return void
	 */
	public function setUp() {
		parent::setUp();

		$this->out = new ConsoleOutput();
		$this->err = new ConsoleOutput();
		$io = new ConsoleIo($this->out, $this->err);

		$this->Shell = $this->getMockBuilder(CacheShell::class)
			->setMethods(['in', '_stop'])
			->setConstructorArgs([$io])
			->getMock();
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
	 * @return void
	 */
	public function testStatus() {
		$this->Shell->runCommand(['status']);
		$output = $this->out->output();
		$expected = '0 cache files found';
		$this->assertContains($expected, $output);
	}

	/**
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
