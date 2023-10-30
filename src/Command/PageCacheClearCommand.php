<?php

namespace Cache\Command;

use Cache\Utility\CacheKey;
use Cache\Utility\FileCache;
use Cake\Cache\Cache;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use FilesystemIterator;

class PageCacheClearCommand extends Command {

	/**
	 * @var \Cake\Console\ConsoleIo
	 */
	protected $io;

	/**
	 * @param \Cake\Console\Arguments $args
	 * @param \Cake\Console\ConsoleIo $io
	 *
	 * @return null|void
	 */
	public function execute(Arguments $args, ConsoleIo $io) {
		$this->io = $io;

		$url = $args->getArgument('url');
		if ($url) {
			$cache = new FileCache();
			$file = $cache->getContent($url);
			if (!$file) {
				$this->io->abort('No cache file found');
			}

			$cacheKey = CacheKey::generate($url, Configure::read('CacheConfig.prefix'), Configure::read('CacheConfig.keyGenerator'));
			$this->removeContent($cacheKey);

			$this->io->out('File ' . $file . ' deleted');

			return null;
		}

		$engine = Configure::read('CacheConfig.engine');
		if (!$engine) {

			$folder = CACHE . 'views' . DS;

			$continue = $this->io->askChoice('Clear `' . $folder . '`?', ['y', 'n'], 'y');
			if ($continue !== 'y') {
				$this->io->abort('Aborted!');
			}

			/** @var \FilesystemIterator<\SplFileInfo> $files */
			$files = new FilesystemIterator($folder, FilesystemIterator::SKIP_DOTS);
			foreach ($files as $file) {
				$path = $file->getPathname();
				if ($args->getOption('verbose')) {
					$this->io->out('Deleting ' . $path);
				}
				unlink($path);
			}
			$this->io->out('Done!');

			return null;
		}

		$continue = $this->io->askChoice('Clear Cache `' . $engine . '`?', ['y', 'n'], 'y');
		if ($continue !== 'y') {
			$io->abort('Aborted!');
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

		$parser->setDescription('Clear all or part of the cached files');
		$parser->addArgument('url', [
			'help' => 'Absolute URL',
			'required' => false,
		]);

		return $parser;
	}

}
