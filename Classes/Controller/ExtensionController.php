<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 29.09.14
 * Time: 17:50
 */

namespace KayStrobach\Developer\Controller;

use KayStrobach\Developer\Services\ExtractExtensionClassNameService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ExtensionController
 *
 * Does everything related to extensions
 *
 * @package KayStrobach\Developer\Controller
 */
class ExtensionController extends ActionController {
	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility
	 * @inject
	 */
	protected $listUtility;

	/**
	 * global init view to have the list of extensions available
	 */
	public function initializeView() {
		$extensionArray = $this->listUtility->getAvailableExtensions();
		$extensionObjects = array();
		foreach($extensionArray as $extension) {
			$extensionObjects[$extension['key']] = (object) $extension;
		}
		ksort($extensionObjects);
		$this->view->assign('extensions', $extensionObjects);
	}

	/**
	 * default action
	 */
	public function indexAction() {

	}

	/**
	 * Form for uploading an extension to TER
	 *
	 * @param string $extensionName
	 */
	public function uploadAction($extensionName = '') {

	}

	/**
	 * Process the upload to TER
	 *
	 * @param string $extensionName
	 * @param string $username
	 * @param string $password
	 * @param string $description
	 * @param string $version
	 */
	public function uploadProcessAction($extensionName, $username, $password, $description, $version) {

	}

	/**
	 * form for generating autoload
	 */
	public function autoloadAction() {

	}

	/**
	 * generate autoloader
	 *
	 * @param string $extensionName
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
	 */
	public function autoloadGenerateAction($extensionName) {
		/** @var \KayStrobach\Developer\Services\ExtractExtensionClassNameService $autoloadService */
		$autoloadService = GeneralUtility::makeInstance('KayStrobach\Developer\Services\ExtractExtensionClassNameService');
		$autoloadService->createAutoloadRegistryForExtension($extensionName, ExtensionManagementUtility::extPath($extensionName));

		foreach($autoloadService->getErrors() as $error) {
			$this->addFlashMessage($error, '', FlashMessage::ERROR);
		}
		foreach($autoloadService->getMessages() as $message) {
			$this->addFlashMessage($message);
		}

		$this->redirect('autoload');
	}
} 