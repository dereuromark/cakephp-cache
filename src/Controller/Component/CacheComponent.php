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
		$content = $event->subject->response->body();
		if (!$content) {
			return;
		}

		$duration = $this->config('duration');
		$isActionCached = $this->_isActionCached($event);
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
	protected function _isActionCached(Event $event) {
		$actions = $this->config('actions');
		if (!$actions) {
			return true;
		}

		$action = $event->subject->request->params['action'];

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
		if (is_numeric($duration)) {
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
		$cache = $cache . '.html';
		$content = '<!--cachetime:' . $cacheTime . '-->' . $content;

		$folder = CACHE . 'views' . DS;
		if (Configure::read('debug') && !is_dir($folder)) {
			mkdir($folder, 0770, true);
		}
		$file = $folder . $cache;
		die(debug($file));
		return file_put_contents($file, $content);
	}

}
