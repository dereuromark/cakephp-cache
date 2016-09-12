<?php
namespace TestApp\Controller;

use Cake\Controller\Controller;

/**
 * Use Controller instead of AppController to avoid conflicts
 */
class CacheComponentTestController extends Controller {

	/**
	 * @var array
	 */
	public $components = ['Cache.Cache'];

	/**
	 * @var bool
	 */
	public $failed = false;

	/**
	 * @var array
	 */
	public $testHeaders = [];

	/***
     * @return void
     */
	public function fail() {
		$this->failed = true;
	}

	/**
	 * @param array|string $url
	 * @param int|null $status
	 *
	 * @return null
	 */
	public function redirect($url, $status = null) {
		return $status;
	}

	/**
	 * @param int $status
	 *
	 * @return void
	 */
	public function header($status) {
		$this->testHeaders[] = $status;
	}

}
