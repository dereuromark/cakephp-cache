<?php

namespace Cache\Utility;

use Cake\Cache\Cache;
use Cake\Core\Configure;

class FileCache {

	/**
	 * The amount of time to browser cache files (which are unlimited).
	 *
	 * @var string
	 */
	protected $_cacheTime = '';

	/**
	 * @var string|null
	 */
	protected $_cacheContent;

	/**
	 * @var array|null
	 */
	protected $_cacheInfo;

	/**
	 * @param array $config Array of config.
	 */
	public function __construct($config = []) {
		if (!empty($config['cacheTime'])) {
			$this->_cacheTime = $config['cacheTime'];
		}
		if ($this->_cacheTime === '' && !Configure::read('CacheConfig.engine')) {
			$this->_cacheTime = '+1 hour';
		}
	}

	/**
	 * @param string $url
	 *
	 * @return string|null
	 */
	public function getContent($url) {
		$cacheKey = CacheKey::generate($url, Configure::read('CacheConfig.prefix'));

		$engine = Configure::read('CacheConfig.engine');
		if (!$engine) {
			$folder = CACHE . 'views' . DS;
			$file = $folder . $cacheKey . '.cache';
			if (!file_exists($file)) {
				return null;
			}

			return file_get_contents($file);
		}

		return Cache::read($cacheKey, $engine) ?: null;
	}

	/**
	 * @param string $content
	 *
	 * @return array Time/Ext
	 */
	public function extractCacheInfo(&$content) {
		if ($this->_cacheInfo) {
			return $this->_cacheInfo;
		}

		$cacheStart = $cacheEnd = 0;
		$cacheExt = 'html';
		$content = preg_replace_callback('/^<!--cachetime:(\d+)\/(\d+);ext:(\w+)-->/', function ($matches) use (&$cacheStart, &$cacheEnd, &$cacheExt) {
			$cacheStart = (int)$matches[1];
			$cacheEnd = (int)$matches[2];
			$cacheExt = $matches[3];
			return '';
		}, $content);

		if (!$cacheStart) {
			return [];
		}

		$this->_cacheInfo = [
			'start' => $cacheStart,
			'end' => $cacheEnd,
			'ext' => $cacheExt,
		];

		return $this->_cacheInfo;
	}

}
