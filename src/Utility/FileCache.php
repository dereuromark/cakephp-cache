<?php

namespace Cache\Utility;

use Cake\Core\Configure;
use Cake\Utility\Text;

class FileCache {

	/**
	 * The amount of time to browser cache files (which are unlimited).
	 *
	 * @var string
	 */
	protected $_cacheTime = '+1 day';

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
	}

	/**
	 * @param string $url
	 * @param bool $mustExist
	 *
	 * @return string|null
	 */
	public function getFile($url, $mustExist = true) {
		if ($url === '/') {
			$url = '_root';
		}

		$path = $url;
		$prefix = Configure::read('Cache.prefix');
		if ($prefix) {
			$path = $prefix . '_' . $path;
		}

		if ($url !== '_root') {
			$path = Text::slug($path);
		}

		$folder = CACHE . 'views' . DS;
		$file = $folder . $path . '.html';
		if ($mustExist && !file_exists($file)) {
			return null;
		}
		return $file;
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

		$cacheTime = 0;
		$cacheExt = 'html';
		$this->_cacheContent = preg_replace_callback('/^\<\!--cachetime\:(\d+);ext\:(\w+)--\>/', function ($matches) use (&$cacheTime, &$cacheExt) {
			$cacheTime = $matches[1];
			$cacheExt = $matches[2];
			return '';
		}, $this->_cacheContent);

		$this->_cacheInfo = [
			'time' => (int)$cacheTime,
			'ext' => $cacheExt,
		];

		return $this->_cacheInfo;
	}

	/**
	 * @param string $file
	 *
	 * @return string
	 */
	protected function extractCacheContent($file) {
		if ($this->_cacheContent !== null) {
			return $this->_cacheContent;
		}

		$this->_cacheContent = (string)file_get_contents($file);

		return $this->_cacheContent;
	}

}
