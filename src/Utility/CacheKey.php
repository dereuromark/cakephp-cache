<?php

namespace Cache\Utility;

use Cake\Utility\Text;
use RuntimeException;

class CacheKey {

	/**
	 * @param string $url
	 * @param string|null $prefix
	 * @param callable|null $keyGenerator
	 *
	 * @return string
	 */
	public static function generate(string $url, ?string $prefix, $keyGenerator = null) {
		if ($keyGenerator) {
			return $keyGenerator($url, $prefix);
		}

		$urlParams = parse_url($url);
		if (!$urlParams) {
			throw new RuntimeException('Invalid URL');
		}

		if ($urlParams['path'] === '/') {
			$url = '_root';
		}

		$cacheKey = $url;

		if ($url !== '_root') {
			$cacheKey = Text::slug($urlParams['path']);
		}

		if (!empty($urlParams['query'])) {
			$cacheKey .= '_' . hash('sha1', $urlParams['query']);
		}

		if (!empty($prefix)) {
			$cacheKey = $prefix . '_' . $cacheKey;
		}

		return $cacheKey;
	}

	/**
	 * @param int|string $duration
	 * @return string now/until as int UNIX timestamps.
	 */
	public static function cacheTime($duration): string {
		$now = time();
		if (!$duration) {
			$cacheTime = 0;
		} elseif (is_numeric($duration)) {
			$cacheTime = $now + $duration;
		} else {
			$cacheTime = strtotime($duration, $now);
		}

		return $now . '/' . $cacheTime;
	}

}
