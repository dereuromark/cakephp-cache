<?php

namespace Cache;

use Cache\Command\PageCacheClearCommand;
use Cache\Command\PageCacheStatusCommand;
use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;

class CachePlugin extends BasePlugin {

	/**
	 * @var bool
	 */
	protected bool $middlewareEnabled = false;

	/**
	 * @var bool
	 */
	protected bool $bootstrapEnabled = false;

	/**
	 * @var bool
	 */
	protected bool $routesEnabled = false;

	/**
	 * @param \Cake\Console\CommandCollection $commands
	 *
	 * @return \Cake\Console\CommandCollection
	 */
	public function console(CommandCollection $commands): CommandCollection {
		$commands->add('page_cache status', PageCacheStatusCommand::class);
		$commands->add('page_cache clear', PageCacheClearCommand::class);

		return $commands;
	}

}
