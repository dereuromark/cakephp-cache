<?php

namespace TestApp\Controller;

use Cake\Controller\Controller;

/**
 * Use Controller instead of AppController to avoid conflicts
 *
 * @property \Cache\Controller\Component\CacheComponent $Cache
 */
class CacheComponentTestController extends Controller {

	/**
	 * @return void
	 */
	public function initialize(): void {
		parent::initialize();

		$this->loadComponent('Cache.Cache');
	}

}
