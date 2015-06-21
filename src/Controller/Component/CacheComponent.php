<?php
namespace Cache\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\Controller;
use Cake\Core\App;
use Cake\Core\Exception\Exception;
use Cake\Event\Event;
use Cake\Event\EventManagerTrait;
use Cake\Network\Exception\ForbiddenException;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Routing\Router;
use Cake\Utility\Inflector;
use Cake\Core\Configure;

/**
 *
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
	];

	/**
	 * @param Event $event
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
	 * @param Event $event
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
			return;
		}

		$ext = $this->response->mapType($this->response->type());

		$cache = $cache . '.html';
		$content = '<!--cachetime:' . $cacheTime . ';ext:' . $ext . '-->' . $content;

		$folder = CACHE . 'views' . DS;
		if (Configure::read('debug') && !is_dir($folder)) {
			mkdir($folder, 0770, true);
		}
		$file = $folder . $cache;

		return file_put_contents($file, $content);
	}

}
