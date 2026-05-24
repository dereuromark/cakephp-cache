<?php

namespace Cache\Controller\Component;

use Cache\Utility\CacheKey;
use Cache\Utility\Compressor;
use Cake\Cache\Cache;
use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * For complete URL caching. Allows to set a very specific duration per URL.
 */
class CacheComponent extends Component {

	/**
	 * Default config
	 *
	 * @var array<string, mixed>
	 */
	protected array $_defaultConfig = [
		'duration' => null,
		'engine' => null,
		'actions' => [],
		'compress' => false,
		'force' => false,
		'prefix' => false,
		'when' => null,
		'keyGenerator' => null,
		'timestamp' => null,
	];

	/**
	 * @param \Cake\Controller\ComponentRegistry $registry A component registry
	 *  this component can use to lazy load its components.
	 * @param array $config Array of configuration settings.
	 */
	public function __construct(ComponentRegistry $registry, array $config = []) {
		$config += (array)Configure::read('CacheConfig');

		parent::__construct($registry, $config);
	}

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @return void
	 */
	public function afterFilter(EventInterface $event): void {
		if (Configure::read('debug') && !$this->getConfig('force')) {
			return;
		}
		/** @var callable|null $when */
		$when = $this->getConfig('when');
		/** @var \Cake\Controller\Controller $controller */
		$controller = $event->getSubject();
		if ($when !== null && $when($controller->getRequest()) !== true) {
			return;
		}

		$content = (string)$controller->getResponse()->getBody();
		if (!$content) {
			return;
		}

		$duration = $this->getConfig('duration');
		$isActionCachable = $this->_isActionCachable();
		if ($isActionCachable === false) {
			return;
		}
		if ($isActionCachable !== true) {
			$duration = $isActionCachable;
		}

		$this->_writeContent($content, $duration);
	}

	/**
	 * @return string|int|bool
	 */
	protected function _isActionCachable() {
		if (!$this->getController()->getRequest()->is('get')) {
			return false;
		}

		$actions = (array)$this->getConfig('actions');
		if (!$actions) {
			return true;
		}

		$action = $this->getController()->getRequest()->getParam('action');
		if (array_key_exists($action, $actions)) {
			return $actions[$action];
		}

		return in_array($action, $actions, true);
	}

	/**
	 * Write a cached version of the file
	 *
	 * @param string $content view content to write to a cache file.
	 * @param string|int $duration Duration to set for cache file.
	 * @return bool Success of caching view.
	 */
	protected function _writeContent(string $content, $duration) {
		$cacheTime = CacheKey::cacheTime($duration);

		$url = $this->getController()->getRequest()->getRequestTarget();
		$url = str_replace($this->getController()->getRequest()->getAttribute('base'), '', $url);
		$cacheKey = CacheKey::generate($url, $this->getConfig('prefix'), $this->getConfig('keyGenerator'));

		$ext = (string)$this->getController()->getResponse()->mapType($this->getController()->getResponse()->getType());
		$content = $this->_compress($content, $ext);

		$content = '<!--cachetime:' . $cacheTime . ';ext:' . $ext . '-->' . $content;

		// Add timestamp comment at the end for debugging
		$timestampConfig = $this->getConfig('timestamp');
		if ($timestampConfig !== false && $ext === 'html') {
			$timestamp = date('Y-m-d H:i:s');
			$content .= '<!-- ' . $timestamp . ' -->';
		}

		$engine = $this->getConfig('engine');
		if (!$engine) {
			return $this->_writeFile($content, $cacheKey);
		}

		return Cache::write($cacheKey, $content, $engine);
	}

	/**
	 * Writes the cache file atomically.
	 *
	 * Uses a temp file in the same directory plus `rename()` so concurrent
	 * readers never observe a torn header (which previously could fall
	 * through the regex check, return `[]` from `extractCacheInfo()`, and
	 * result in `cacheTime` parsing to `0` so the file would never expire).
	 *
	 * @param string $content
	 * @param string $cache
	 *
	 * @return bool
	 */
	protected function _writeFile(string $content, string $cache) {
		$folder = CACHE . 'views' . DS;
		if (Configure::read('debug')) {
			@mkdir($folder, 0770, true);
		}

		$cache .= '.cache';
		$file = $folder . $cache;

		$tmp = @tempnam($folder, '.cache-tmp-');
		if ($tmp === false) {
			return false;
		}

		$bytes = @file_put_contents($tmp, $content, LOCK_EX);
		if ($bytes === false || $bytes !== strlen($content)) {
			@unlink($tmp);

			return false;
		}

		// Match the permissions file_put_contents would have produced.
		@chmod($tmp, 0664 & ~umask());

		if (!@rename($tmp, $file)) {
			@unlink($tmp);

			return false;
		}

		return true;
	}

	/**
	 * Compresses HTML
	 *
	 * @param string $content
	 * @param string $ext
	 * @return string Content
	 */
	protected function _compress($content, $ext) {
		$compress = $this->getConfig('compress');
		if ($compress === true) {
			// Native compressor only supports HTML right now
			if ($ext === 'html') {
				$Compressor = new Compressor();
				$content = $Compressor->compress($content);
			}
		} elseif (is_callable($compress)) {
			$content = $compress($content, $ext);
		}

		return $content;
	}

}
