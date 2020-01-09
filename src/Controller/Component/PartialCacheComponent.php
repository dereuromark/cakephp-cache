<?php

namespace Cache\Controller\Component;

use Cache\View\PartialCacheView;
use Cake\Controller\Component;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

class PartialCacheComponent extends Component {

	/**
	 * Default config
	 *
	 * @var array
	 */
	protected $_defaultConfig = [
		'actions' => [],
		'compress' => false,
	];

	/**
	 * @param \Cake\Event\EventInterface $event
	 * @return void
	 */
	public function startup(EventInterface $event): void {
		if (Configure::read('Cache.check') === false) {
			return;
		}

		if ($this->getController()->getRequest()->getParam('_ext') && $this->getController()->getRequest()->getParam('_ext') !== 'html') {
			return;
		}

		$isActionCachable = $this->_isActionCachable();
		if ($isActionCachable === false) {
			return;
		}

		/** @var \Cake\Controller\Controller $controller */
		$controller = $event->getSubject();
		$builder = $controller->viewBuilder();
		if ($builder->getClassName()) {
			return;
		}

		$builder->setClassName(PartialCacheView::class);
		$duration = $isActionCachable === true ? null : $isActionCachable;
		$builder->setOptions(['duration' => $duration, 'compress' => $this->getConfig('compress')]);
	}

	/**
	 * @return bool|int|string
	 */
	protected function _isActionCachable() {
		$actions = $this->getConfig('actions');
		if (!$actions) {
			return true;
		}
		if (!$this->getController()->getRequest()->is('get')) {
			return false;
		}

		$action = $this->getController()->getRequest()->getParam('action');

		if (array_key_exists($action, $actions)) {
			return $actions[$action];
		}
		if (in_array($action, $actions, true)) {
			return true;
		}
		return false;
	}

}
