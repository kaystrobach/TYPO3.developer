<?php

namespace KayStrobach\Developer\Controller;


use FluidTYPO3\Flux\Utility\VersionUtility;
use KayStrobach\Developer\Services\T3dService;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
	 * @var \TYPO3\CMS\Core\Registry
	 * @inject
	 */
	protected $registry;

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	public function getBeUser() {
		return $GLOBALS['BE_USER'];
	}

	/**
	 * returns all distributions
	 * @return array
	 */
	protected function getDistributions() {
		$allExtensions = $this->listUtility->enrichExtensionsWithEmConfAndTerInformation($this->listUtility->getAvailableExtensions());
		$distributions = array();

		// @todo refactor into distribution repo
		foreach($allExtensions as $extension) {
			if($extension['category'] === 'distribution') {
				$distributions[$extension['key']] = (object) $extension;
			}
		}
		return $distributions;
	}

	/**
	 * @param string $extensionName
	 */
	public function indexAction($extensionName = '') {
		$this->view->assign('extensions', $this->getDistributions());
		$this->view->assign('extension', $extensionName);
	}

	public function newAction() {
		$version = VersionNumberUtility::convertVersionStringToArray(VersionNumberUtility::getCurrentTypo3Version());
		$this->view->assign('compatVersion', $version['version_main'] . '.' . $version['version_sub'] . '.0-' . $version['version_main'] . '.' . $version['version_sub'] . '.99');
	}

	/**
	 * @param string $extensionName
	 * @param string $compatVersion
	 */
	public function createAction($extensionName, $compatVersion) {
		try {
			$package = $this->packageManager->getPackage($extensionName);
			$this->addFlashMessage('Package ' . $extensionName . ' does already exist', '', FlashMessage::ERROR);
			$this->redirect('index');
			return;
		} catch(\Exception $e) {

		}

		$packageRootPath = PATH_site . 'typo3conf/ext/' . $extensionName . '/';
		GeneralUtility::mkdir_deep($packageRootPath);
		GeneralUtility::mkdir_deep($packageRootPath . 'Initialisation/Files');
		$buffer = file_get_contents(ExtensionManagementUtility::extPath('developer') . 'Resources/Private/Pattern/Distribution/ext_emconf.php');
		$replacements = array(
			'###title###' => 'Distribution ' . time(),
			'###distribution###' => '',
			'###author###' => $this->getBeUser()->user['realName'],
			'###email###' => $this->getBeUser()->user['email'],
			'###version###' => $compatVersion,
		);
		$buffer = str_replace(array_keys($replacements), array_values($replacements), $buffer);
		GeneralUtility::writeFile($packageRootPath . 'ext_emconf.php', $buffer);
		copy(PATH_site . 'typo3/gfx/typo3.png', $packageRootPath . 'ext_icon.png');

		$this->packageManager->scanAvailablePackages();

		$this->addFlashMessage('Distribution' . $extensionName . ' created, use the other actions to fill it with data and files.');

		$this->redirect(
			't3dExport',
			NULL,
			NULL,
			array(
				'extensionName' => $extensionName
			)
		);
	}

	/**
	 * @param string $extensionName
	 */
	public function updateAction($extensionName) {
		try {
			$distributionPathFileadmin = PATH_site . 'fileadmin/' . $extensionName;
			$packagePath = $this->packageManager->getPackage($extensionName)->getPackagePath();
			if(is_dir($packagePath)) {
				GeneralUtility::rmdir($packagePath . 'Initialisation', TRUE);
				GeneralUtility::mkdir_deep($packagePath . 'Initialisation/Files');
				GeneralUtility::copyDirectory($distributionPathFileadmin, $packagePath . 'Initialisation/Files');
				$this->addFlashMessage('Copied files from ' . $distributionPathFileadmin . ' to ' . $packagePath . 'Initialisation/Files');
			}
		} catch(\Exception $e) {
			$this->addFlashMessage($e->getMessage(), '', AbstractMessage::ERROR);
			$this->redirect('index');
		}

		$this->redirect(
			't3dExport',
			NULL,
			NULL,
			array(
				'extensionName' => $extensionName
			)
		);
	}

	/**
	 * @param string $extensionName

	 */
	public function t3dExportAction($extensionName) {
		try {
			$packagePath = $this->packageManager->getPackage($extensionName)->getPackagePath();
			$t3dFileName = $packagePath . 'Initialisation/data.t3d';
			GeneralUtility::mkdir_deep($packagePath . 'Initialisation');
			if(file_exists($t3dFileName)) {
				unlink($t3dFileName);
			}
			$distService = new T3dService();
			$distService->export($t3dFileName);
			$this->addFlashMessage($t3dFileName . ' created with ' . filesize($t3dFileName) . ' Bytes');
		} catch(\Exception $e) {
			$this->addFlashMessage('<pre>' . $e->getTraceAsString() . '</pre>', $e->getMessage(), FlashMessage::ERROR);
		}
		$this->redirect(
			'index',
			NULL,
			NULL,
			array(
				'extensionName' => $extensionName
			)
		);
	}

	public function statusAction() {
		$distributions = $this->getDistributions();

		foreach($distributions as $key => $distribution) {
			$packagePath = $this->packageManager->getPackage($distribution->key)->getPackagePath();
			$packagePath = substr($packagePath, strlen(PATH_site));
			$distributions[$key]->distributionFilesImportPath = $packagePath . 'Initialisation/Files';
			if($this->registry->get('extensionDataImport', $packagePath . 'Initialisation/Files')) {
				$distributions[$key]->distributionFilesImported = TRUE;
			}
		}

		$this->view->assign('distributions', $distributions);
	}

	/**
	 * @param string $extensionName
	 */
	public function resetStatusAction($extensionName) {
		$packagePath = $this->packageManager->getPackage($extensionName)->getPackagePath();
		$packagePath = substr($packagePath, strlen(PATH_site));
		$this->registry->remove(
			'extensionDataImport',
			$packagePath . 'Initialisation/Files'
		);
		$this->redirect('status');
	}
}