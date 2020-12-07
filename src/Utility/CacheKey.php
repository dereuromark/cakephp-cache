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
        $urlParams = parse_url($url);
		if ($urlParams['path'] === '/') {
			$url = '_root';
        } elseif (substr($url, 0, 1)=='/') {
            $url = substr($url, 1);
		}
        
        // Implement Prefix if Needed
		$folder = CACHE . 'views' . DS;
        if (!empty($prefix)){
            $folder .= $prefix . DS;
            $url = $prefix . DS . $url;
        }
		if (Configure::read('debug') && !is_dir($folder)) {
			mkdir($folder, 0770, true);
		}
        
        if (substr($url, -1)=='/') {
            $url = substr($url, 0, (strlen($url)-1));
        }
		if (!empty($urlParams['query'])) {
			$url .= '-v' . substr(hash('sha256', $urlParams['query']), 0, 8);
		}
		$cacheKey = $url;
        
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
