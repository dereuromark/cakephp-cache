<?php
namespace Cache\Controller\Component;

use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Utility\Inflector;

/**
 */
class CacheComponent extends Component {

	/**
	 * Default config
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'duration' => null,
		'actions' => [],
		'debug' => false,
		'compress' => false,
	];

	/**
	 * @param \Cake\Event\Event $event
	 * @return void
	 */
	public function shutdown(Event $event) {
		$this->response = $event->subject->response;

		$content = $this->response->body();
		if (!$content) {
			return;
		}

		$duration = $this->config('duration');
		$isActionCached = $this->_isActionCached();
		if ($isActionCached === false) {
			return;
		}
		if ($isActionCached !== true) {
			$duration = $isActionCached;
		}

		$this->_writeFile($content, $duration);
	}

	/**
	 * @param \Cake\Event\Event $event
	 * @return bool|int|string
	 */
	protected function _isActionCached() {
		$actions = $this->config('actions');
		if (!$actions) {
			return true;
		}

		$action = $this->request->params['action'];

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
		//$cacheTime = date('Y-m-d H:i:s', $timestamp);
		$now = time();
		if (!$duration) {
			$cacheTime = 0;
		} elseif (is_numeric($duration)) {
			$cacheTime = $now + $duration;
		} else {
			$cacheTime = strtotime($duration, $now);
		}

		$path = $this->request->here();
		if ($path === '/') {
			$path = 'home';
		}
		$prefix = Configure::read('Cache.prefix');
		if ($prefix) {
			$path = $prefix . '_' . $path;
		}
		$cache = Inflector::slug($path);
		if (empty($cache)) {
			return false;
		}

		$ext = $this->response->mapType($this->response->type());

		$compress = $this->config('compress');
		if ($compress === true) {
			$content = $this->_compress($content, $ext);
		} elseif (is_callable($compress)) {
			$content = $compress($content, $ext);
		} elseif ($compress) {
			$content = call_user_func($compress, $content, $ext);
		}

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
	 * Compress HTML
	 *
	 * @param string $content
	 * @param string $ext
	 * @return string Content
	 */
	protected function _compress($content, $ext) {
		if ($ext !== 'html') {
			return $content;
		}

		// Removes HTML comments (not containing IE conditional comments).
		$content = preg_replace_callback('/<!--([\\s\\S]*?)-->/', [$this, '_commentIgnore'], $content);

		// Trim each line.
		$content = preg_replace('/^\\s+|\\s+$/m', '', $content);

		return $content;
	}

	/**
	 * @param array $m
	 * @return string
	 */
	protected function _commentIgnore($m) {
		return (strpos($m[1], '[') === 0 || strpos($m[1], '<![') !== false) ? $m[0] : '';
	}

}
