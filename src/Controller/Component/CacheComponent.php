<?php

namespace Cache\Controller\Component;

use Cache\Utility\Compressor;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Utility\Text;

class CacheComponent extends Component {

	/**
	 * Default config
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'duration' => null,
		'actions' => [],
		'compress' => false,
		'force' => false,
		'when' => null,
	];

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
		if ($when !== null && $when($request) !== true) {
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

		$this->_writeFile($content, $duration);
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
	protected function _writeFile($content, $duration) {
		$now = time();
		if (!$duration) {
			$cacheTime = 0;
		} elseif (is_numeric($duration)) {
			$cacheTime = $now + $duration;
		} else {
			$cacheTime = strtotime($duration, $now);
		}

		$url = $this->getController()->getRequest()->getRequestTarget();
		$url = str_replace($this->getController()->getRequest()->getAttribute('base'), '', $url);
		if ($url === '/') {
			$url = '_root';
		}

		$cache = $url;
		$prefix = Configure::read('Cache.prefix');
		if ($prefix) {
			$cache = $prefix . '_' . $url;
		}
		if ($url !== '_root') {
			$cache = Text::slug($cache);
		}
		if (empty($cache)) {
			return false;
		}

		$ext = $this->getController()->getResponse()->mapType($this->getController()->getResponse()->getType());
		$content = $this->_compress($content, $ext);

		$cache = $cache . '.html';
		$content = '<!--cachetime:' . $cacheTime . ';ext:' . $ext . '-->' . $content;

		$folder = CACHE . 'views' . DS;
		if (Configure::read('debug') && !is_dir($folder)) {
			mkdir($folder, 0770, true);
		}
		$file = $folder . $cache;

		return file_put_contents($file, $content);
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
