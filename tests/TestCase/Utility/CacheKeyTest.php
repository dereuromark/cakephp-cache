<?php

namespace Cache\Test\TestCase\Utility;

use Cache\Utility\CacheKey;
use Cake\TestSuite\TestCase;

class CacheKeyTest extends TestCase {

	/**
	 * @return void
	 */
	public function testGenerate() {
		$result = CacheKey::generate('/');
		$this->assertSame('_root', $result);

		$result = CacheKey::generate('/foo/bar/baz?x=y');
		$this->assertSame('foo-bar-baz-x-y_2122bac1f4247c9a9ffe5dc55e67976b637e11b4', $result);
	}

	/**
	 * @return void
	 */
	public function testGeneratePrefixed() {
		$result = CacheKey::generate('/', 'prefix');
		$this->assertSame('prefix__root', $result);

		$result = CacheKey::generate('/foo/bar/baz?x=y', 'prefix');
		$this->assertSame('prefix_foo-bar-baz-x-y_2122bac1f4247c9a9ffe5dc55e67976b637e11b4', $result);
	}

	/**
	 * @return void
	 */
	public function testGenerateGenerator() {
		$generator = function ($url, $prefix) {
			return $prefix . $url;
		};
		$result = CacheKey::generate('/', 'prefix', $generator);
		$this->assertSame('prefix/', $result);

		$result = CacheKey::generate('/foo/bar/baz?x=y', 'prefix');
		$this->assertSame('prefix/foo/bar/baz?x=y', $result);
	}

}
