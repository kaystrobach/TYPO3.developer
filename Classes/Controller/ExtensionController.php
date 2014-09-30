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
		$extensionArray = $this->listUtility->getAvailableExtensions();
		$extensionObjects = array();
		foreach($extensionArray as $extension) {
			$extensionObjects[] = (object) $extension;
		}

		$this->view->assign('extensions', $extensionObjects);
	}

	public function indexAction() {

	}

	/**
	 * @param string $extensionName
	 */
	public function uploadAction($extensionName = '') {

	}
} 