<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 17.10.14
 * Time: 08:18
 */

namespace KayStrobach\Developer\Command;


use TYPO3\CMS\Core\Utility\File\BasicFileUtility;
use TYPO3\CMS\Core\Utility\File\ExtendedFileUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extensionmanager\Exception\ExtensionManagerException;

class ExtensionCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {
	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\Connection\TerUtility
	 * @inject
	 */
	protected $terUtility;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\EmConfUtility
	 * @inject
	 */
	protected $emConfUtility;

	/**
	 * @param string $extensionKey
	 */
	public function qualityCheckCommand($extensionKey) {
		$this->outputLine('will run a quality check on your extensions, with phpcs, phpunit, etc.');
	}

	/**
	 * @param string $extensionKey
	 */
	public function createT3xCommand($extensionKey) {

	}

	/**
	 * @param string $pathToT3x
	 * @param bool $listOnly
	 * @param bool $verbose
	 * @throws ExtensionManagerException
	 */
	public function extractT3xCommand($pathToT3x, $listOnly = FALSE, $verbose = FALSE) {
		$fileContent = GeneralUtility::getUrl($pathToT3x);
		if (!$fileContent) {
			throw new ExtensionManagerException('File had no or wrong content.', 1342859339);
		}
		$extensionData = $this->terUtility->decodeExchangeData($fileContent);
		if (empty($extensionData['extKey'])) {
			throw new ExtensionManagerException('Decoding the file went wrong. No extension key found', 1342864309);
		}
		if(!file_exists($extensionData['extKey'])) {
			mkdir($extensionData['extKey']);
			chdir($extensionData['extKey']);
			foreach($extensionData['FILES'] as $fileName => $data) {
				if($verbose) {
					$this->outputLine($data['name']);
				}
				if(!$listOnly) {
					GeneralUtility::mkdir_deep('', dirname($data['name']));
					GeneralUtility::writeFile($data['name'], $data['content']);
				}
			}
		} else {
			$this->outputLine('Directory ' . $extensionData['extKey'] . ' is already present in your system');
		}
		$emConfContent = $this->emConfUtility->constructEmConf($extensionData);
		GeneralUtility::writeFile('ext_emconf.php', $emConfContent);

		$this->outputLine('extracted content to directory ' . $extensionData['extKey']);
	}
} 