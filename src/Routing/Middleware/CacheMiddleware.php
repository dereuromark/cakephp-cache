<?php

namespace Cache\Routing\Middleware;

use Cache\Utility\CacheKey;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\InstanceConfigTrait;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * For use with CacheComponent and basic file caching.
 */
class CacheMiddleware implements MiddlewareInterface {

	use InstanceConfigTrait;

	/**
	 * @var array
	 */
	protected $_defaultConfig = [
		'engine' => null,
		'when' => null,
		'cacheTime' => null,
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
		$config += (array)Configure::read('CacheConfig');

		$this->setConfig($config);

		if (!$this->getConfig('engine') && $this->getConfig('cacheTime') === null) {
			$this->setConfig('cacheTime', '+1 hour');
		}
	}

	/**
	 * @param \Cake\Http\ServerRequest $request
	 * @param \Psr\Http\Server\RequestHandlerInterface $handler
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
		if (Configure::read('CacheConfig.check') === false || !$request->is('get')) {
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

		$cacheKey = CacheKey::generate($url, $this->getConfig('prefix'));
		$fileContent = $this->getContent($url, $cacheKey);

		if ($fileContent === null) {
			return $handler->handle($request);
		}

		$cacheContent = $this->extractCacheContent($fileContent);
		$cacheInfo = $this->extractCacheInfo($cacheContent);
		if (!$cacheInfo) {
			return $handler->handle($request);
		}

		$cacheStart = $cacheInfo['start'];
		$cacheEnd = $cacheInfo['end'];
		$cacheExt = $cacheInfo['ext'];

		if ($cacheEnd < time() && $cacheEnd !== 0) {
			$this->removeContent($cacheKey);

			return $handler->handle($request);
		}

		$response = new Response();

		$modified = $cacheStart ?: time();
		/** @var \Cake\Http\Response $response */
		$response = $response->withModified($modified);
		if ($response->checkNotModified($request)) {
			return $response;
		}

		$response = $this->_deliverCacheFile($request, $response, $fileContent, $cacheExt);

		return $response;
	}

	/**
	 * @param string $url
	 * @param string $cacheKey
	 *
	 * @return string|null
	 */
	protected function getContent(string $url, string $cacheKey) {
		$engine = $this->getConfig('engine');
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
	 * @param string $cacheKey
	 *
	 * @return void
	 */
	protected function removeContent(string $cacheKey): void {
		$engine = $this->getConfig('engine');
		if (!$engine) {
			$folder = CACHE . 'views' . DS;
			$file = $folder . $cacheKey . '.cache';
			unlink($file);

			return;
		}

		Cache::delete($cacheKey, $engine);
	}

	/**
	 * @param string $content
	 *
	 * @return array
	 */
	protected function extractCacheInfo(&$content) {
		if ($this->_cacheInfo) {
			return $this->_cacheInfo;
		}

		$cacheStart = $cacheEnd = 0;
		$cacheExt = 'html';
		$this->_cacheContent = preg_replace_callback('/^<!--cachetime:(\d+)\/(\d+);ext:(\w+)-->/', function ($matches) use (&$cacheStart, &$cacheEnd, &$cacheExt) {
			$cacheStart = (int)$matches[1];
			$cacheEnd = (int)$matches[2];
			$cacheExt = $matches[3];
			return '';
		}, $this->_cacheContent);

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

	/**
	 * @param string $fileContent
	 *
	 * @return string
	 */
	protected function extractCacheContent($fileContent) {
		if ($this->_cacheContent !== null) {
			return $this->_cacheContent;
		}

		$this->_cacheContent = (string)$fileContent;

		return $this->_cacheContent;
	}

	/**
	 * Sends an asset file to the client
	 *
	 * @param \Cake\Http\ServerRequest $request The request object to use.
	 * @param \Cake\Http\Response $response The response object to use.
	 * @param string $content Content
	 * @param string $ext Extension
	 *
	 * @return \Cake\Http\Response
	 */
	protected function _deliverCacheFile(ServerRequest $request, Response $response, $content, $ext) {
		$compressionEnabled = $response->compress();
		if ($response->getType() === $ext) {
			$contentType = 'application/octet-stream';
			$agent = $request->getEnv('HTTP_USER_AGENT');
			if ($agent && (preg_match('%Opera([/ ])([0-9].[0-9]{1,2})%', $agent) || preg_match('/MSIE ([0-9].[0-9]{1,2})/', $agent))) {
				$contentType = 'application/octetstream';
			}

			$response = $response->withType($contentType);
		}

		if (!$compressionEnabled) {
			$response = $response->withHeader('Content-Length', (string)strlen($content));
		}

		$cacheContent = $this->_cacheContent;
		$cacheInfo = $this->_cacheInfo;
		$cacheStart = $cacheInfo['start'];
		$cacheEnd = $cacheInfo['end'];

		$modifiedTime = $cacheStart ?: time();
		if (!$cacheEnd) {
			$cacheEnd = $this->getConfig('cacheTime');
		}

		$response = $response->withCache($modifiedTime, $cacheEnd);
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
