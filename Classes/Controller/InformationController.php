<?php

namespace KayStrobach\Developer\Controller;

use KayStrobach\Developer\Services\PhpInfoService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class InformationController extends ActionController {
	/**
	 *
	 */
	public function listPhpInfoAction() {
		$this->view->assign('information', PhpInfoService::extractPhpInfoData());
	}

	/**
	 *
	 */
	public function documentationAction() {

	}

}