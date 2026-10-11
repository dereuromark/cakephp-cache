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
			$cacheKey = (string)$keyGenerator($url, $prefix);
			// Validate key length for filesystem compatibility (most filesystems have 255 char limit)
			if (strlen($cacheKey) > 200) {
				$key = mb_strcut($cacheKey, 0, 159, 'UTF-8');
				$cacheKey = $key . '_' . sha1(substr($cacheKey, strlen($key)));
			}

			return $cacheKey;
		}

		$url = $url === '/' ? '_root' : substr($url, 1);

		$cacheKey = $url;

		if ($url !== '_root') {
			$cacheKey = Text::slug($cacheKey);

		}

		if ($prefix) {
			$cacheKey = $prefix . '_' . $cacheKey;
		}

		if (strlen($cacheKey) > 200) {
			$cacheKey = mb_strcut($cacheKey, 0, 159, 'UTF-8') . '_' . sha1($cacheKey);
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
