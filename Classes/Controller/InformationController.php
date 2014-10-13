<?php

namespace KayStrobach\Developer\Controller;

use KayStrobach\Developer\Services\PhpInfoService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class InformationController
 *
 * Displays some information
 *
 * @package KayStrobach\Developer\Controller
 */
class InformationController extends ActionController {
	/**
	 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
	 * @inject
	 */
	protected $signalSlotDispatcher;

	/**
	 * show phpinfo
	 */
	public function listPhpInfoAction() {
		$this->view->assign('information', PhpInfoService::extractPhpInfoData());
	}

	/**
	 * show some documentation links
	 */
	public function documentationAction() {

	}

	/**
	 *
	 */
	public function hooksAction() {
		$this->view->assign('hooks', $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']);
	}

	public function signalsAction() {
		$reflection = new \ReflectionClass($this->signalSlotDispatcher);
		$attribute = $reflection->getProperty('slots');
		$attribute->setAccessible(TRUE);
		$this->view->assign('classes', $attribute->getValue($this->signalSlotDispatcher));
	}

}