<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 29.09.14
 * Time: 17:50
 */

namespace KayStrobach\Developer\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class ExtensionController extends ActionController {
	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility
	 * @inject
	 */
	protected $listUtility;

	public function initializeView() {
		$this->view->assign('extensions', $this->listUtility->getAvailableExtensions());
	}

	public function indexAction() {

	}

	/**
	 * @param string $extensionName
	 */
	public function uploadAction($extensionName = '') {

	}
} 