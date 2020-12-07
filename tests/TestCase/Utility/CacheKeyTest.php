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
		$expected = 'foo-bar-baz-dfdkfsldfklsdfksdf-dsfjksdfjksdf-sfdjksdfsohfsdf-ae2ef524fbf3d9fe611d5a8e90fefdc9c-ae2ef524fbf3d9fe611d5a8e90fefdc9cbed3d2c21991e3bef5e069713af9fa6ca-bed3d2c21991e3bef5e069713af9fa6cacac627ab1ccbdb62ec9_378527ad671b2f449aa820a336296355ab81320d';
		$this->assertSame($expected, $result);
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
		$expected = 'prefix_foo-bar-baz-dfdkfsldfklsdfksdf-dsfjksdfjksdf-sfdjksdfsohfsdf-ae2ef524fbf3d9fe611d5a8e90fefdc9c-ae2ef524fbf3d9fe611d5a8e90fefdc9cbed3d2c21991e3bef5e069713af9fa6ca-bed3d2c21991e3bef5e069713af9fa6cacac627ab1ccb_e6ac853975251be634e12d7c8c9bc32b21afb8b0';
		$this->assertSame($expected, $result);
	}

}
