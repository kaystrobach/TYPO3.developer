<?php

namespace KayStrobach\Developer\Controller;


use KayStrobach\Developer\Services\T3dService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
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
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	public function getBeUser() {
		return $GLOBALS['BE_USER'];
	}

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

	/**
	 * @param string $extensionName
	 */
	public function createAction($extensionName) {
		try {
			$package = $this->packageManager->getPackage($extensionName);
			$this->addFlashMessage('Package ' . $extensionName . ' does already exist', '', FlashMessage::ERROR);
			$this->redirect('index');
			return;
		} catch(\TYPO3\Flow\Package\Exception\UnknownPackageException $e) {

		}

		$packageRootPath = PATH_site . 'typo3conf/ext/' . $extensionName . '/';
		Files::createDirectoryRecursively($packageRootPath);
		Files::createDirectoryRecursively($packageRootPath . 'Initialisation/Files');
		$buffer = file_get_contents(ExtensionManagementUtility::extPath('developer') . 'Resources/Private/Pattern/Distribution/ext_emconf.php');
		$replacements = array(
			'###title###' => 'Distribution ' . time(),
			'###distribution###' => '',
			'###author###' => $this->getBeUser()->user['realName'],
			'###email###' => $this->getBeUser()->user['email'],
		);
		$buffer = str_replace(array_keys($replacements), array_values($replacements), $buffer);
		GeneralUtility::writeFile($packageRootPath . 'ext_emconf.php', $buffer);
		copy(PATH_site . 'typo3/gfx/typo3.png', $packageRootPath . 'ext_icon.png');

		$this->packageManager->scanAvailablePackages();

		$this->addFlashMessage('Distribution' . $extensionName . ' created, use the other actions to fill it with data and files.');
		$this->redirect('index');
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

	/**
	 * @param string $extensionName

	 */
	public function t3dExportAction($extensionName) {
		try {
			$packagePath = $this->packageManager->getPackage($extensionName)->getPackagePath();
			$t3dFileName = $packagePath . 'Initialisation/data.t3d';
			Files::createDirectoryRecursively($packagePath . 'Initialisation');
			if(file_exists($t3dFileName)) {
				unlink($t3dFileName);
			}
			$distService = new T3dService();
			$distService->export($t3dFileName);
			$this->addFlashMessage($t3dFileName . ' created with ' . filesize($t3dFileName) . ' Bytes');
		} catch(\Exception $e) {
			$this->addFlashMessage('<pre>' . $e->getTraceAsString() . '</pre>', $e->getMessage(), FlashMessage::ERROR);
		}
		$this->redirect('index');
	}
}