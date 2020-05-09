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
	 * @var array
	 */
	protected $_defaultConfig = [
		'duration' => null,
		'engine' => null,
		'actions' => [],
		'compress' => false,
		'force' => false,
		'prefix' => false,
		'when' => null,
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
	public function shutdown(EventInterface $event): void {
		if (Configure::read('debug') && !$this->getConfig('force')) {
			return;
		}
		/** @var callable $when */
		$when = $this->getConfig('when');
		if ($when !== null && $when($event->getSubject()->getRequest()) !== true) {
			return;
		}

		/** @var \Cake\Http\Response $response */
		$response = $event->getSubject()->getResponse();

		$content = (string)$response->getBody();
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
	 * @return bool|int|string
	 */
	protected function _isActionCachable() {
		$actions = $this->getConfig('actions');
		if (!$actions) {
			return true;
		}
		if (!$this->getController()->getRequest()->is('get')) {
			return false;
		}

		$action = $this->getController()->getRequest()->getParam('action');
		if (array_key_exists($action, $actions)) {
			return $actions[$action];
		}
		if (in_array($action, $actions, true)) {
			return true;
		}
		return false;
	}

	/**
	 * Write a cached version of the file
	 *
	 * @param string $content view content to write to a cache file.
	 * @param int|string $duration Duration to set for cache file.
	 * @return bool Success of caching view.
	 */
	protected function _writeContent(string $content, $duration) {
		$cacheTime = CacheKey::cacheTime($duration);

		$url = $this->getController()->getRequest()->getRequestTarget();
		$url = str_replace($this->getController()->getRequest()->getAttribute('base'), '', $url);
		$cacheKey = CacheKey::generate($url, $this->getConfig('prefix'));

		$ext = $this->getController()->getResponse()->mapType($this->getController()->getResponse()->getType());
		$content = $this->_compress($content, $ext);

		$content = '<!--cachetime:' . $cacheTime . ';ext:' . $ext . '-->' . $content;

		$engine = $this->getConfig('engine');
		if (!$engine) {
			return $this->_writeFile($content, $cacheKey);
		}

		return Cache::write($cacheKey, $content, $engine);
	}

	/**
	 * @param string $content
	 * @param string $cache
	 *
	 * @return bool
	 */
	protected function _writeFile(string $content, string $cache) {
		$folder = CACHE . 'views' . DS;
		if (Configure::read('debug') && !is_dir($folder)) {
			mkdir($folder, 0770, true);
		}

		$cache .= '.cache';
		$file = $folder . $cache;

		return (bool)file_put_contents($file, $content);
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
		} elseif ($compress) {
			$content = call_user_func($compress, $content, $ext);
		}

		return $content;
	}

}
