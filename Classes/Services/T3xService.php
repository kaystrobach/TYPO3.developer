<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 04.02.15
 * Time: 16:59
 */

namespace KayStrobach\Developer\Services;

use KayStrobach\Developer\Services\Soap\Helper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class T3xService {
	public static function create($extensionKey, $saveTo = NULL) {
		/** @var \TYPO3\CMS\Core\Package\PackageManager $packageManager */
		$packageManager = GeneralUtility::makeInstance('TYPO3\CMS\Core\Package\PackageManager');
		$extensionPath = $packageManager->getPackage($extensionKey)->getPackagePath();

		$extensionData = array(
			'extKey' => $extensionKey,
			'EM_CONF' => Helper::getEmConf($extensionKey, $extensionPath),
			'misc' => array(),
			'techInfo' => array(),
			'FILES' => Helper::getExtensionFilesData($extensionPath)
		);

		$data = serialize($extensionData);
		$md5 = md5($data);
		$compress = '';
		if (function_exists('gzcompress')) {
			$compress = 'gzcompress';
			$data = gzcompress($data);
		}
		$content =  $md5 . ':' . $compress . ':' . $data;
		if($saveTo !== NULL) {
			file_put_contents($saveTo, $content);
		} else {
			return $content;
		}
	}
	public static function extract() {

	}
}