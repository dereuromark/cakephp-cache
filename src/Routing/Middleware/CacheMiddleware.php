<?php

namespace Cache\Routing\Middleware;

use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Utility\Text;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CacheMiddleware implements MiddlewareInterface {

	use InstanceConfigTrait;

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'when' => null,
		'cacheTime' => '+1 day',
	];

	/**
	 * @var string|null
	 */
	protected $_cacheContent;

	/**
	 * @var array|null
	 */
	protected $_cacheInfo;

	/**
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		$this->setConfig($config);
	}

	/**
	 * @param \Cake\Http\ServerRequest $request
	 * @param \Psr\Http\Server\RequestHandlerInterface $handler
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		if (Configure::read('Cache.check') === false || !$request->is('get')) {
			return $handler->handle($request);
		}
		/** @var callable $when */
		$when = $this->getConfig('when');
		if ($when !== null && $when($request) !== true) {
			return $handler->handle($request);
		}

		/** @var \Cake\Http\ServerRequest $request */
		$url = $request->getRequestTarget();
		$url = str_replace($request->getAttribute('base'), '', $url);
		$file = $this->getFile($url);

		if ($file === null) {
			return $handler->handle($request);
		}

		$cacheContent = $this->extractCacheContent($file);
		$cacheInfo = $this->extractCacheInfo($cacheContent);
		$cacheTime = $cacheInfo['time'];

		if ($cacheTime < time() && $cacheTime !== 0) {
			unlink($file);
			return $handler->handle($request);
		}

		/** @var \Cake\Http\Response $response */
		$response = $handler->handle($request);

		$modified = filemtime($file) ?: time();
		/** @var \Cake\Http\Response $response */
		$response = $response->withModified($modified);
		if ($response->checkNotModified($request)) {
			return $response;
		}

		$pathSegments = explode('.', $file);
		$ext = array_pop($pathSegments);
		$response = $this->_deliverCacheFile($request, $response, $file, $ext);

		return $response;
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

	/**
	 * Sends an asset file to the client
	 *
	 * @param \Cake\Http\ServerRequest $request The request object to use.
	 * @param \Cake\Http\Response $response The response object to use.
	 * @param string $file Path to the asset file in the file system
	 * @param string $ext The extension of the file to determine its mime type
	 *
	 * @return \Cake\Http\Response
	 */
	protected function _deliverCacheFile(ServerRequest $request, Response $response, $file, $ext) {
		$compressionEnabled = $response->compress();
		if ($response->getType() === $ext) {
			$contentType = 'application/octet-stream';
			$agent = $request->getEnv('HTTP_USER_AGENT');
			if ($agent && (preg_match('%Opera(/| )([0-9].[0-9]{1,2})%', $agent) || preg_match('/MSIE ([0-9].[0-9]{1,2})/', $agent))) {
				$contentType = 'application/octetstream';
			}

			$response = $response->withType($contentType);
		}

		if (!$compressionEnabled) {
			$response = $response->withHeader('Content-Length', (string)filesize($file));
		}

		$cacheContent = $this->_cacheContent;
		$cacheInfo = $this->_cacheInfo;

		$modifiedTime = filemtime($file) ?: time();
		$cacheTime = $cacheInfo['time'];
		if (!$cacheTime) {
			$cacheTime = $this->getConfig('cacheTime');
		}

		$response = $response->withCache($modifiedTime, $cacheTime);
		$response = $response->withType($cacheInfo['ext']);

		if (Configure::read('debug') || $this->getConfig('debug')) {
			if ($cacheInfo['ext'] === 'html') {
				$cacheContent = '<!--created:' . date('Y-m-d H:i:s', $modifiedTime) . '-->' . $cacheContent;
			}
		}

		$body = $response->getBody();
		$body->write($cacheContent);
		return $response->withBody($body);
	}

}
