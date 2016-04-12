<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 29.09.14
 * Time: 17:50
 */

namespace KayStrobach\Developer\Controller;

use KayStrobach\Developer\Services\ExtractExtensionClassNameService;
use KayStrobach\Developer\Services\Soap\TerUpload;
use KayStrobach\Developer\Services\T3xService;
use KayStrobach\Developer\Utility\DirectoryStructureCheck;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Documentation\Slots\ExtensionManager;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use KayStrobach\Developer\Utility\ShellCaptureUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;


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
	 * @var \TYPO3\CMS\Extensionmanager\Domain\Repository\ExtensionRepository
	 * @inject
	 */
	protected $extensionRepository;

	/**
	 * @var \KayStrobach\Developer\Utility\TerUtility
	 * @inject
	 */
	protected $terUtility;

	/**
	 * indicates a successfull upload
	 */
	const TX_TER_RESULT_EXTENSIONSUCCESSFULLYUPLOADED = 10504;

	/**
	 * global init view to have the list of extensions available
	 */
	public function initializeView(ViewInterface $view) {
		$extensionArray = $this->listUtility->getAvailableExtensions();
		$extensionObjects = array();
		$allExtensionObjects = array();
		foreach($extensionArray as $extension) {
			if($extension['type'] !== 'System') {
				$extensionObjects[$extension['key']] = (object) $extension;
			}
			$allExtensionObjects[$extension['key']] = (object) $extension;
		}
		ksort($extensionObjects);
		$this->view->assign('extensions', $extensionObjects);
		ksort($allExtensionObjects);
		$this->view->assign('allExtensions', $allExtensionObjects);
	}

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
		$this->view->assign('extensionName', $extensionName);
	}

	/**
	 * Form for uploading an extension to TER
	 *
	 * @param string $extensionName
	 */
	public function uploadAction($extensionName = '') {
		$beUser = $this->getBeUser();
		$data = $beUser->getModuleData('developer/controller/extension');

		if(($extensionName === '') && (isset($data['extensionName']))) {
			$extensionName = $data['extensionName'];
		}
		$this->view->assign('extensionName', $extensionName);

		if(isset($data['username'])) {
			$this->view->assign('username', $data['username']);
		}

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
	public function uploadProcessAction($extensionName, $username, $password, $description) {
		$beUser = $this->getBeUser();
		$beUser->pushModuleData(
			'developer/controller/extension',
			array(
				'username' => $username,
				'extensionName' => $extensionName
			)
		);

		$upload = new TerUpload();
		$upload->setExtensionKey($extensionName)
			->setUsername($username)
			->setPassword($password)
			->setUploadComment($description)
			->setPath(ExtensionManagementUtility::extPath($extensionName));
		try {
			$response = $upload->execute();
		} catch (\SoapFault $s) {
			$this->addFlashMessage('SOAP: ' . $s->getMessage(), '', FlashMessage::ERROR);
			$this->redirect('upload');
			return;
		} catch(\Exception $e) {
			$this->addFlashMessage('Error: ' . $e->getMessage(), '', FlashMessage::ERROR);
			$this->redirect('upload');
			return;
		}

		if (!is_array($response)) {
			$this->addFlashMessage($response, '', FlashMessage::ERROR);
		}
		if ($response['resultCode'] == self::TX_TER_RESULT_EXTENSIONSUCCESSFULLYUPLOADED) {
			$this->addFlashMessage('Uploaded extension "' . $extensionName . '" successfully', '', FlashMessage::OK);
		}
		$this->redirect('upload');
	}

	/**
	 * form for generating autoload
	 * @param string $extensionName
	 */
	public function autoloadAction($extensionName = '') {
		$this->view->assign('extensionName', $extensionName);
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

		$this->redirect(
			'autoload',
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
	public function directoryStructureCheckAction($extensionName = '') {
		$extensionDirectory = ExtensionManagementUtility::extPath($extensionName);

		$this->view->assign('directories', DirectoryStructureCheck::checkDirectories($extensionDirectory));
		$this->view->assign('files', DirectoryStructureCheck::checkFiles($extensionDirectory));
	}

	/**
	 * @param string $extensionName
	 * @param string $path
	 */
	public function codestylecheckAction($extensionName = '', $path = '') {

		$phpcsPath = PATH_site.'typo3temp/phpcs.phar';

		if(!file_exists($phpcsPath)) {
			file_put_contents($phpcsPath, GeneralUtility::getUrl('https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar'));
		}



		$cleanedPath = NULL;
		if($extensionName) {
			$cleanedPath = ExtensionManagementUtility::extPath($extensionName);
		} elseif(is_dir($path)) {
			$cleanedPath = $path;
		}


		$command = PHP_BINDIR . '/php ' . $phpcsPath . ' --extensions=php -n ' . $cleanedPath;
		$this->view->assign('command', $command);
		$this->view->assign('raw', ShellCaptureUtility::execute($command));

		$this->view->assign('extensionName', $extensionName);
	}

	/**
	 * @param string $extensionName
	 * @param string $version
	 */
	public function compareWithTerVersionAction($extensionName, $version = NULL) {
		$versions = $this->extensionRepository->findByExtensionKeyOrderedByVersion($extensionName);
		$this->view->assign('extensionName', $extensionName);
		$this->view->assign('versions', $versions);
		if($version !== NULL) {
			if(!class_exists('\SebastianBergmann\Diff\Differ')) {
				$this->addFlashMessage('Please install phpunit extension to get a real diff of the files', '', FlashMessage::INFO);
			}

			/** @var \TYPO3\CMS\Extensionmanager\Domain\Model\Extension $version */
			$version = $this->extensionRepository->findOneByExtensionKeyAndVersion($extensionName, $version);
			$fetchedData = $this->terUtility->downloadToTemp($version);
			$this->view->assign('selectedVersion', $version);
			$this->view->assign('fetchedData', $fetchedData);

			$filestate = array();
			$extensionPath = ExtensionManagementUtility::extPath($extensionName);
			foreach($fetchedData['FILES'] as $filename => $file) {
				if(!file_exists($extensionPath . '/' . $filename)) {
					$filestate[$filename] = array(
						'state' => FALSE,
						'message' => 'File does not exist locally'
					);
					continue;
				}
				if(file_get_contents($extensionPath . '/' . $filename) === $file['content']) {
					$filestate[$filename] = array(
						'state' => TRUE,
						'message' => 'Files are similar.'
					);
					continue;
				} else {
					$diff = NULL;
					if(class_exists('\SebastianBergmann\Diff\Differ')) {
						$differ = new \SebastianBergmann\Diff\Differ();
						$diff = $differ->diffToArray(file_get_contents($extensionPath . '/' . $filename), $file['content']);
					}
					$filestate[$filename] = array(
						'state' => FALSE,
						'diff' => $diff,
						'message' => 'Files are not similar.'
					);
					continue;
				}
			}
			$this->view->assign('fileStates', $filestate);
		}
	}

	/**
	 * @param string $extensionName
	 * @return string
	 */
	public function downloadT3xAction($extensionName = '') {
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename=' . $extensionName . '.t3x');
		return T3xService::create($extensionName);
	}
} 