<?php

namespace App\Controller;

use Cake\Controller\Controller;

/**
 * Use Controller instead of AppController to avoid conflicts
 */
class CacheComponentTestController extends Controller {

	/**
	 * @var array
	 */
	public $components = ['Cache.Cache'];

}
