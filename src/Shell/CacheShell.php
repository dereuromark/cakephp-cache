<?php
namespace Cache\Shell;

use Cache\Routing\Filter\CacheFilter;
use Cake\Console\Shell;
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
		$folder = CACHE . 'views' . DS;
		if (!is_dir($folder)) {
			mkdir($folder, 0770, true);
		}

		if (!$url) {
			$fi = new FilesystemIterator($folder, FilesystemIterator::SKIP_DOTS);
			$count = iterator_count($fi);
			$this->out($count . ' cache files found.');
			return;
		}

		$cache = new CacheFilter();
		$file = $cache->getFile($url);
		if (!$file) {
			$this->abort('No cache file found');
		}

		$content = file_get_contents($file);
		$cacheInfo = $cache->extractCacheInfo($content);
		$time = $cacheInfo['time'];
		if ($time) {
			$time = date('Y-m-d H:i:s', $time);
		} else {
			$time = '(unlimited)';
		}

		$this->out('Cache File: ' . basename($file));
		$this->out('URL ext: ' . $cacheInfo['ext']);
		$this->out('Cached until: ' . $time);
	}

	/**
	 * @param string|null $url
	 * @return int|null
	 */
	public function clear($url = null) {
		if ($url) {
			$cache = new CacheFilter();
			$file = $cache->getFile($url);
			if (!$file) {
				$this->abort('No cache file found');
			}
			unlink($file);
			$this->out('File ' . $file . ' deleted');
			return null;
		}

		$folder = CACHE . 'views' . DS;

		$continue = $this->in('Clear `' . $folder . '`?', ['y', 'n'], 'y');
		if ($continue !== 'y') {
			$this->abort('Aborted!');
		}

		$fi = new FilesystemIterator($folder, FilesystemIterator::SKIP_DOTS);
		foreach ($fi as $file) {
			$path = $file->getPathname();
			if ($this->params['verbose']) {
				$this->out('Deleting ' . $path);
			}
			unlink($path);
		}
		$this->out('Done!');
	}

	/**
	 * Gets the option parser instance and configures it.
	 *
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$infoParser = $parser->toArray();
		$infoParser['arguments']['url'] = [
			'help' => 'Absolute URL',
			'required' => false
		];

		$parser->description('Cache Shell to cleanup caching of view files.')
				->addSubcommand('status', [
					'help' => 'Status information about the files',
					'parser' => $infoParser,
				])
				->addSubcommand('clear', [
					'help' => 'Clear all or part of the files',
					'parser' => $parser
				]);

		return $parser;
	}

}
