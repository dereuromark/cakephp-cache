<?php

namespace Cache\Utility;

use Cake\Utility\Text;

class CacheKey {

	/**
	 * @param string $url
	 * @param string|null $prefix
	 *
	 * @return string
	 */
	public static function generate(string $url, ?string $prefix) {
		if ($url === '/') {
			$url = '_root';
		}

		$cacheKey = $url;
		if ($prefix) {
			$cacheKey = $prefix . '_' . $url;
		}
		if ($url !== '_root') {
			$cacheKey = Text::slug($cacheKey);
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
