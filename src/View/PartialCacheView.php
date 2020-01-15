<?php

namespace Cache\View;

use App\View\AppView;
use Cache\Utility\Compressor;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\Utility\Text;

/**
 * A view class that is used for caching partial view templates.
 *
 * @author Mark Scherer
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
class PartialCacheView extends AppView {

	/**
	 * @var string|int|null
	 */
	protected $_duration;

	/**
	 * @var string|bool
	 */
	protected $_compress;

	/**
	 * @var bool
	 */
	protected $hasRendered = false;

	/**
	 * @param \Cake\Http\ServerRequest|null $request Request instance.
	 * @param \Cake\Http\Response|null $response Response instance.
	 * @param \Cake\Event\EventManager|null $eventManager Event manager instance.
	 * @param array $viewOptions View options.
	 */
	public function __construct(
		ServerRequest $request = null,
		Response $response = null,
		EventManager $eventManager = null,
		array $viewOptions = []
	) {
		parent::__construct($request, $response, $eventManager, $viewOptions);

		$this->_duration = isset($viewOptions['duration']) ? $viewOptions['duration'] : 0;
		$this->_compress = isset($viewOptions['compress']) ? $viewOptions['compress'] : false;
	}

	/**
	 * Renders view for given template file and layout.
	 *
	 * @param string|null $view Name of view file to use
	 * @param string|null $layout Layout to use.
	 * @return string Rendered content.
	 */
	public function render(?string $view = null, $layout = null): string {
		if ($this->hasRendered) {
			return '';
		}

		$defaultLayout = null;
		if ($layout !== null) {
			$defaultLayout = $this->layout;
			$this->layout = $layout;
		}

		$viewFileName = null;
		if ($view !== '') {
			$viewFileName = $this->_getTemplateFileName($view);
		}

		if ($viewFileName) {
			$this->_currentType = static::TYPE_TEMPLATE;
			$this->dispatchEvent('View.beforeRender', [$viewFileName]);
			$this->Blocks->set('content', $this->_getCachedOrRender($viewFileName));
			$this->dispatchEvent('View.afterRender', [$viewFileName]);
		}

		if ($this->layout && $this->autoLayout) {
			$this->Blocks->set('content', $this->renderLayout('', $this->layout));
		}
		if ($layout !== null) {
			$this->layout = $defaultLayout;
		}

		$this->hasRendered = true;

		return $this->Blocks->get('content');
	}

	/**
	 * @param string $viewFileName
	 *
	 * @return string
	 */
	protected function _getCachedOrRender($viewFileName) {
		$path = str_replace(APP, '', $viewFileName);

		$prefix = Configure::read('Cache.prefix');
		if ($prefix) {
			$path = $prefix . '_' . $path;
		}

		$cacheFolder = CACHE . 'views' . DS;
		if (Configure::read('debug') && !is_dir($cacheFolder)) {
			mkdir($cacheFolder, 0770, true);
		}

		$cacheFile = $cacheFolder . Text::slug($path);

		if (file_exists($cacheFile)) {
			$cacheContent = $this->extractCacheContent($cacheFile);
			$cacheTime = $cacheContent['time'];

			if ($cacheTime < time() && $cacheTime !== 0) {
				unlink($cacheFile);
			} else {
				return $cacheContent['content'];
			}
		}

		$content = $this->_render($viewFileName);
		$content = $this->_compress($content);

		$cacheContent = '<!--cachetime:' . (int)$this->_duration . '-->' . $content;

		file_put_contents($cacheFile, $cacheContent);

		return $content;
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function _compress($content) {
		$compress = $this->_compress;
		if ($compress === true) {
			$Compressor = new Compressor();
			$content = $Compressor->compress($content);
		} elseif (is_callable($compress)) {
			$content = $compress($content);
		} elseif ($compress) {
			$content = call_user_func($compress, $content);
		}

		return $content;
	}

	/**
	 * @param string $file
	 *
	 * @return array
	 */
	protected function extractCacheContent($file) {
		$content = (string)file_get_contents($file);

		$cacheTime = 0;
		$content = preg_replace_callback('/^\<\!--cachetime\:(\d+)--\>/', function ($matches) use (&$cacheTime) {
			$cacheTime = (int)$matches[1];
			return '';
		}, $content);

		if (Configure::read('debug')) {
			$modifiedTime = date('Y-m-d H:i:s', filemtime($file));
			$content = '<!--created:' . $modifiedTime . '-->' . $content . '<!--end-->';
		}

		return [
			'content' => $content,
			'time' => $cacheTime,
		];
	}

}
