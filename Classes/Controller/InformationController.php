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

}