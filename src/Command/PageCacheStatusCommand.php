<?php

namespace Cache\Command;

use Cache\Utility\CacheKey;
use Cache\Utility\FileCache;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use FilesystemIterator;

class PageCacheStatusCommand extends Command {

	/**
	 * @var \Cake\Console\ConsoleIo
	 */
	protected $io;

	/**
	 * @param \Cake\Console\Arguments $args
	 * @param \Cake\Console\ConsoleIo $io
	 *
	 * @return void
	 */
	public function execute(Arguments $args, ConsoleIo $io) {
		$this->io = $io;

		$url = $args->getArgument('url');
		$engine = Configure::read('CacheConfig.engine');
		if (!$engine) {
			$this->fileStatus($url, (bool)$args->getOption('verbose'));

			return;
		}

		$io->out('Cache Engine: ' . $engine);

		if (!$url) {
			return;
		}

		$this->cacheStatus($url, $engine);
	}

	/**
	 * @param string|null $url
	 * @param bool $verbose
	 *
	 * @return void
	 */
	protected function fileStatus(?string $url, bool $verbose = false) {
		$folder = CACHE . 'views' . DS;
		if (!is_dir($folder)) {
			mkdir($folder, 0770, true);
		}

		if (!$url) {
			$fi = new FilesystemIterator($folder, FilesystemIterator::SKIP_DOTS);
			$count = iterator_count($fi);
			$this->io->out($count . ' cache files found.');
			if ($verbose) {
				/** @var \SplFileInfo $f */
				foreach ($fi as $f) {
					$this->io->out(' - ' . $f->getFileName());
				}
			}

			return;
		}

		$this->cacheStatus($url);
	}

	/**
	 * @param string $url
	 * @param string|null $engine
	 *
	 * @return void
	 */
	protected function cacheStatus($url, $engine = null) {
		$cache = new FileCache();
		$fileContent = $cache->getContent($url);
		if (!$fileContent) {
			$this->io->abort('No cache file found');
		}

		$cacheInfo = $cache->extractCacheInfo($fileContent);
		if (!$cacheInfo) {
			$this->io->abort('Invalid cache file');
		}

		$time = $cacheInfo['end'];
		if ($time) {
			$time = date('Y-m-d H:i:s', $time);
		} else {
			$time = '(unlimited)';
		}

		if (!$engine) {
			$file = CacheKey::generate($url, Configure::read('CacheConfig.prefix'), Configure::read('CacheConfig.keyGenerator'));
			$file .= '.cache';
			$this->io->out('Cache File: ' . basename($file));
		}
		$this->io->out('URL ext: ' . $cacheInfo['ext']);
		$this->io->out('Cached since: ' . date('Y-m-d H:i:s', $cacheInfo['start']));
		$this->io->out('Cached until: ' . $time);
		$this->io->verbose('Server time: ' . date('Y-m-d H:i:s', time()));
	}

	/**
	 * Gets the option parser instance and configures it.
	 *
	 * @return \Cake\Console\ConsoleOptionParser
	 */
	public function getOptionParser(): ConsoleOptionParser {
		$parser = parent::getOptionParser();

		$parser->setDescription('Status information about cached files');
		$parser->addArgument('url', [
			'help' => 'Absolute URL',
			'required' => false,
		]);

		return $parser;
	}

}
