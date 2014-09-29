<?php

namespace KayStrobach\Developer\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class InformationController extends ActionController {
	public function ListPhpInfoAction() {
		\ob_start();
		\phpinfo();
		$buffer = \ob_get_clean();
		$this->view->assign('phpinfo', $buffer);
	}
}