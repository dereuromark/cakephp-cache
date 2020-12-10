<?php

namespace Cache\Utility;

use Cake\Utility\Text;

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

		if ($url === '/') {
			$url = '_root';
		} else {
			$url = substr($url, 1);
		}

		$cacheKey = $url;

		if ($url !== '_root') {
			$cacheKey = Text::slug($cacheKey);

			$maxLength = 255 - ($prefix ? mb_strlen($prefix) + 1 : 0);
			if (mb_strlen($cacheKey) > $maxLength) {
				$key = mb_substr($cacheKey, 0, $maxLength - 41);
				$cacheKey = $key . '_' . sha1(mb_substr($cacheKey, $maxLength - 41));
			}
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
