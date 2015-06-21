<?php
namespace Cache\Shell;

use Cake\Console\Shell;

/**
 * Shell for tasks related to plugins.
 *
 */
class CacheShell extends Shell {

	/**
	 * @return void
	 */
	public function info() {
		$folder = CACHE . 'views' . DS;

		$fi = new \FilesystemIterator($folder, \FilesystemIterator::SKIP_DOTS);
		$count = iterator_count($fi);
		$this->out($count . ' cache files found.');
	}

	/**
	 * @return void|int
	 */
	public function clear($path = null) {
		$folder = CACHE . 'views' . DS;

		$continue = $this->in('Clear `' . $folder . '`?', ['y', 'n'], 'y');
		if ($continue !== 'y') {
			return $this->error('Aborted!');
		}

		$fi = new \FilesystemIterator($folder, \FilesystemIterator::SKIP_DOTS);
		foreach ($fi as $file) {
			unlink($file->getPathname());
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
        
        $parser->description('Cache Shell to cleanup caching of view files.')
				->addSubcommand('info', [
					'help' => 'Infos about the files',
					'parser' => $parser
				])
				->addSubcommand('clear', [
                    'help' => 'Clear all or part of the files',
                    'parser' => $parser
                ]);

        return $parser;
    }

}
