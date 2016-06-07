<?php
namespace Cache\Routing\Filter;

use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\Routing\DispatcherFilter;
use Cake\Utility\Inflector;

/**
 *
 */
class CacheFilter extends DispatcherFilter {

	/**
	 * Default priority for all methods in this filter
	 * This filter should run before the request gets parsed by router
	 *
	 * @var int
	 */
	protected $_priority = 9;

	/**
	 * The amount of time to browser cache files (which are unlimited).
	 *
	 * @var string
	 */
	protected $_cacheTime = '+1 day';

	/**
	 * Constructor.
	 *
	 * @param array $config Array of config.
	 */
	public function __construct($config = []) {
		if (!empty($config['cacheTime'])) {
			$this->_cacheTime = $config['cacheTime'];
		}
		parent::__construct($config);
	}

	/**
	 * Checks if a requested cache file exists and sends it to the browser
	 *
	 * @param \Cake\Event\Event $event containing the request and response object
	 * @return \Cake\Network\Response if the client is requesting a recognized cache file, null otherwise
	 */
	public function beforeDispatch(Event $event) {
		if (Configure::read('Cache.check') === false) {
			return;
		}

		$request = $event->data['request'];

		$url = $request->here();
		$file = $this->getFile($url);

		if ($file === null) {
			return;
		}

		$content = file_get_contents($file);
		$cacheInfo = $this->extractCacheInfo($content);
		$cacheTime = $cacheInfo['time'];

		if ($cacheTime < time() && $cacheTime != 0) {
			unlink($file);
			return;
		}

		$response = $event->data['response'];
		$event->stopPropagation();

		$response->modified(filemtime($file));
		if ($response->checkNotModified($request)) {
			return $response;
		}

		$pathSegments = explode('.', $file);
		$ext = array_pop($pathSegments);
		$this->_deliverCacheFile($request, $response, $file, $ext);
		return $response;
	}

	/**
	 * @param string $url
	 * @param bool $mustExist
	 * @return string
	 */
	public function getFile($url, $mustExist = true) {
		if ($url === '/') {
			$url = 'home';
		}

		$path = $url;
		$prefix = Configure::read('Cache.prefix');
		if ($prefix) {
			$path = $prefix . '_' . $path;
		}

		$path = Inflector::slug($path);

		$folder = CACHE . 'views' . DS;
		$file = $folder . $path . '.html';
		if ($mustExist && !file_exists($file)) {
			return null;
		}
		return $file;
	}

	/**
	 * @param string &$content
	 * @return array Time/Ext
	 */
	public function extractCacheInfo(&$content) {
		$cacheTime = 0;
		$cacheExt = 'html';
		$content = preg_replace_callback('/^\<\!--cachetime\:(\d+);ext\:(\w+)--\>/', function ($matches) use (&$cacheTime, &$cacheExt) {
			$cacheTime = $matches[1];
			$cacheExt = $matches[2];
			return '';
		}, $content);

		return [
			'time' => (int)$cacheTime,
			'ext' => $cacheExt
		];
	}

	/**
	 * Sends an asset file to the client
	 *
	 * @param \Cake\Network\Request $request The request object to use.
	 * @param \Cake\Network\Response $response The response object to use.
	 * @param string $file Path to the asset file in the file system
	 * @param string $ext The extension of the file to determine its mime type
	 * @return void
	 */
	protected function _deliverCacheFile(Request $request, Response $response, $file, $ext) {
		$compressionEnabled = $response->compress();
		if ($response->type($ext) === $ext) {
			$contentType = 'application/octet-stream';
			$agent = $request->env('HTTP_USER_AGENT');
			if (preg_match('%Opera(/| )([0-9].[0-9]{1,2})%', $agent) || preg_match('/MSIE ([0-9].[0-9]{1,2})/', $agent)) {
				$contentType = 'application/octetstream';
			}
			$response->type($contentType);
		}
		if (!$compressionEnabled) {
			$response->header('Content-Length', filesize($file));
		}

		$content = file_get_contents($file);
		$cacheInfo = $this->extractCacheInfo($content);

		$modifiedTime = filemtime($file);
		$cacheTime = $cacheInfo['time'];
		if (!$cacheTime) {
			$cacheTime = $this->_cacheTime;
		}
		$response->cache($modifiedTime, $cacheTime);
		$response->type($cacheInfo['ext']);

		if (Configure::read('debug') || $this->config('debug')) {
			if ($cacheInfo['ext'] === 'html') {
				$content = '<!--created:' . $modifiedTime . '-->' . $content;
			}
		}
		$response->body($content);
	}

}
