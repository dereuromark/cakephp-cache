<?php

namespace Cache\Shell;

use Cache\Utility\CacheKey;
use Cache\Utility\FileCache;
use Cake\Cache\Cache;
use Cake\Console\ConsoleOptionParser;
use Cake\Console\Shell;
use Cake\Core\Configure;
use FilesystemIterator;

/**
 * Shell for tasks related to plugins.
 */
class CacheShell extends Shell {

	/**
	 * @param string|null $url
	 * @return void
	 */
	public function status($url = null) {
		$engine = Configure::read('CacheConfig.engine');
		if (!$engine) {
			$this->fileStatus($url);

			return;
		}

		$this->out('Cache Engine: ' . $engine);

		if (!$url) {
			return;
		}

		$this->cacheStatus($url, $engine);
	}

	/**
	 * @param string|null $url
	 *
	 * @return void
	 */
	protected function fileStatus($url) {
		$folder = CACHE . 'views' . DS;
		if (!is_dir($folder)) {
			mkdir($folder, 0770, true);
		}

		if (!$url) {
			$fi = new FilesystemIterator($folder, FilesystemIterator::SKIP_DOTS);
			$count = iterator_count($fi);
			$this->out($count . ' cache files found.');
			if ($this->param('verbose')) {
				foreach ($fi as $f) {
					$this->out(' - ' . $f->getFileName());
				}
			}
			return;
		}

		$this->cacheStatus($url);
	}

	/**
	 * @param string|null $url
	 * @param string|null $engine
	 *
	 * @return void
	 */
	protected function cacheStatus($url, $engine = null) {
		$cache = new FileCache();
		$fileContent = $cache->getContent($url);
		if (!$fileContent) {
			$this->abort('No cache file found');
		}

		$cacheInfo = $cache->extractCacheInfo($fileContent);
		if (!$cacheInfo) {
			$this->abort('Invalid cache file');
		}

		$time = $cacheInfo['end'];
		if ($time) {
			$time = date('Y-m-d H:i:s', $time);
		} else {
			$time = '(unlimited)';
		}

		if (!$engine) {
			$file = CacheKey::generate($url, Configure::read('CacheConfig.prefix'));
			$file .= '.cache';
			$this->out('Cache File: ' . basename($file));
		}
		$this->out('URL ext: ' . $cacheInfo['ext']);
		$this->out('Cached since: ' . date('Y-m-d H:i:s', $cacheInfo['start']));
		$this->out('Cached until: ' . $time);
		$this->verbose('Server time: ' . date('Y-m-d H:i:s', time()));
	}

	/**
	 * @param string|null $url
	 * @return int|null
	 */
	public function delete($url = null) {
		if ($url) {
			$cache = new FileCache();
			$file = $cache->getContent($url);
			if (!$file) {
				$this->abort('No cache file found');
			}

			$cacheKey = CacheKey::generate($url, Configure::read('CacheConfig.prefix'));
			$this->removeContent($cacheKey);

			$this->out('File ' . $file . ' deleted');
			return null;
		}

		$engine = Configure::read('CacheConfig.engine');
		if (!$engine) {

			$folder = CACHE . 'views' . DS;

			$continue = $this->in('Clear `' . $folder . '`?', ['y', 'n'], 'y');
			if ($continue !== 'y') {
				$this->abort('Aborted!');
			}

			/** @var \SplFileInfo[] $files */
			$files = new FilesystemIterator($folder, FilesystemIterator::SKIP_DOTS);
			foreach ($files as $file) {
				$path = $file->getPathname();
				if ($this->params['verbose']) {
					$this->out('Deleting ' . $path);
				}
				unlink($path);
			}
			$this->out('Done!');

			return null;
		}

		$continue = $this->in('Clear Cache `' . $engine . '`?', ['y', 'n'], 'y');
		if ($continue !== 'y') {
			$this->abort('Aborted!');
		}

		Cache::clear($engine);

		return null;
	}

	/**
	 * @param string $cacheKey
	 *
	 * @return void
	 */
	protected function removeContent(string $cacheKey): void {
		$engine = Configure::read('CacheConfig.engine');
		if (!$engine) {
			$folder = CACHE . 'views' . DS;
			$file = $folder . $cacheKey . '.cache';
			unlink($file);

			return;
		}

		Cache::delete($cacheKey, $engine);
	}

	/**
	 * Gets the option parser instance and configures it.
	 *
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser(): ConsoleOptionParser {
		$parser = parent::getOptionParser();

		$infoParser = $parser->toArray();
		$infoParser['arguments']['url'] = [
			'help' => 'Absolute URL',
			'required' => false,
		];

		$parser->setDescription('Cache Shell to cleanup caching of view files.')
				->addSubcommand('status', [
					'help' => 'Status information about the files',
					'parser' => $infoParser,
				])
				->addSubcommand('delete', [
					'help' => 'Clear all or part of the files',
					'parser' => $parser,
				]);

		return $parser;
	}

}
