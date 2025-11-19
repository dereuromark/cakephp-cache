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
			$cacheKey = $keyGenerator($url, $prefix);
			// Validate key length for filesystem compatibility (most filesystems have 255 char limit)
			if (mb_strlen($cacheKey) > 200) {
				$key = mb_substr($cacheKey, 0, 159);
				$cacheKey = $key . '_' . sha1(mb_substr($cacheKey, 159));
			}

			return $cacheKey;
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

		if ($prefix) {
			$cacheKey = $prefix . '_' . $cacheKey;
		}

		return $cacheKey;
	}

	/**
	 * @param string|int $duration
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
			if ($cacheTime === false) {
				$cacheTime = 0;
			}
		}

		return $now . '/' . $cacheTime;
	}

}
