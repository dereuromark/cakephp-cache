<?php

namespace Cache\Test\TestCase\Utility;

use Cache\Utility\CacheKey;
use Cake\TestSuite\TestCase;

class CacheKeyTest extends TestCase {

	/**
	 * @return void
	 */
	public function testGenerate() {
		$result = CacheKey::generate('/', null);
		$this->assertSame('_root', $result);

		$result = CacheKey::generate('/foo/bar/baz?x=y', null);
		$this->assertSame('foo-bar-baz-x-y', $result);
	}

	/**
	 * @return void
	 */
	public function testGenerateLong() {
		$long = '/foo/bar/baz/dfdkfsldfklsdfksdf-dsfjksdfjksdf-sfdjksdfsohfsdf?';
		for ($i = 97; $i < 117; $i++) {
			$long .= chr($i) . md5($i) . '=' . chr($i) . md5($i);
		}
		$this->assertTrue(mb_strlen($long) > 256);

		$result = CacheKey::generate($long, null);
		$this->assertTrue(mb_strlen($result) < 256);
		$this->assertLessThanOrEqual(255, strlen($result . '.cache'));
		$this->assertSame($result, CacheKey::generate($long, null));
	}

	/**
	 * @return void
	 */
	public function testGeneratePrefixed() {
		$result = CacheKey::generate('/', 'prefix');
		$this->assertSame('prefix__root', $result);

		$result = CacheKey::generate('/foo/bar/baz?x=y', 'prefix');
		$this->assertSame('prefix_foo-bar-baz-x-y', $result);
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

		$result = CacheKey::generate('/foo/bar/baz?x=y', 'prefix', $generator);
		$this->assertSame('prefix/foo/bar/baz?x=y', $result);
	}

	/**
	 * @return void
	 */
	public function testGeneratePrefixedGeneratorLong() {
		$long = '/foo/bar/baz/dfdkfsldfklsdfksdf-dsfjksdfjksdf-sfdjksdfsohfsdf?';
		for ($i = 97; $i < 117; $i++) {
			$long .= chr($i) . md5($i) . '=' . chr($i) . md5($i);
		}
		$this->assertTrue(mb_strlen($long) > 256);

		$result = CacheKey::generate($long, 'prefix');
		$this->assertTrue(mb_strlen($result) < 256);
		$this->assertLessThanOrEqual(255, strlen($result . '.cache'));
		$this->assertStringStartsWith('prefix_', $result);
	}

	/**
     * @return void
     */
	public function testLongPrefixFitsFilesystem(): void {
		$key = CacheKey::generate('/public-page', str_repeat('prefix', 60));
		$file = tempnam(sys_get_temp_dir(), 'cache-key-');
		unlink($file);
		$directory = $file;
		mkdir($directory);
		$file = $directory . DS . $key . '.cache';

		try {
			$this->assertLessThanOrEqual(255, strlen(basename($file)));
			$this->assertSame(4, file_put_contents($file, 'body'));
		} finally {
			if (file_exists($file)) {
				unlink($file);
			}
			rmdir($directory);
		}
	}

}
