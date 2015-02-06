<?php

namespace KayStrobach\Developer\Controller;


use KayStrobach\Developer\Services\T3dService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\Flow\Utility\Files;

class DistributionController extends ActionController {
	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\ListUtility
	 * @inject
	 */
	protected $listUtility;

	/**
	 * @var \TYPO3\CMS\Core\Package\PackageManager
	 * @inject
	 */
	protected $packageManager;

	/**
	 * @param string $extensionName
	 */
	public function indexAction($extensionName = '') {
		$allExtensions = $this->listUtility->enrichExtensionsWithEmConfAndTerInformation($this->listUtility->getAvailableExtensions());
		$distributions = array();

		// @todo refactor into distribution repo
		foreach($allExtensions as $extension) {
			if($extension['category'] === 'distribution') {
				$distributions[$extension['key']] = (object) $extension;
			}
		}

		$this->view->assign('extensions', $distributions);
		$this->view->assign('extension', $extensionName);
	}

	public function newAction() {

	}

	public function createAction() {

	}

	public function editAction() {

	}

	/**
	 * @param string $extensionName
	 */
	public function updateAction($extensionName) {
		try {
			$distributionPathFileadmin = PATH_site . 'fileadmin/' . $extensionName;
			$packagePath = $this->packageManager->getPackage($extensionName)->getPackagePath();
			if(is_dir($packagePath)) {
				Files::removeDirectoryRecursively($packagePath . 'Initialisation');
				Files::createDirectoryRecursively($packagePath . 'Initialisation/Files');
				Files::copyDirectoryRecursively($distributionPathFileadmin, $packagePath . 'Initialisation/Files');
				$this->addFlashMessage('Copied files from ' . $distributionPathFileadmin . ' to ' . $packagePath . 'Initialisation/Files');
			}
		} catch(\Exception $e) {
			$this->addFlashMessage($e->getMessage(), '', AbstractMessage::ERROR);
			$this->redirect('index');
		}
		$this->redirect('index');
	}

	public function t3dExportAction() {
		$distService = new T3dService();
		$distService->export('');
	}
}