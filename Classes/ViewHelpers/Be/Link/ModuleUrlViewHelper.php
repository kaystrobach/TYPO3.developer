<?php

namespace KayStrobach\Developer\ViewHelpers\Be\Link;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Class HighlightViewHelper
 * @package KayStrobach\Developer\ViewHelpers
 */
class ModuleUrlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @param $moduleName
	 * @param $config
	 * @param string $blindLinkOptions
	 * @param string $allowedExtensions
	 * @return string
	 */
	public function render($moduleName, $config, $blindLinkOptions = NULL, $allowedExtensions = NULL) {
		return $this->getUrl($moduleName, $config, $blindLinkOptions, $allowedExtensions);
	}

	/**
	 * @param $moduleName
	 * @param $config
	 * @return string
	 */
	protected function getUrl($moduleName, $config, $blindLinkOptions = NULL, $allowedExtensions = NULL) {
		$config = $config + array(
			'mode' => 'wizard'
		);
		if(!array_key_exists('P', $config)) {
			$config['P'] = array();
		}
		if(!array_key_exists('params', $config['P'])) {
			$config['P']['params'] = array();
		}
		if($blindLinkOptions !== NULL) {
			$config['P']['params']['blindLinkOptions'] = $blindLinkOptions;
		}
		if($allowedExtensions !== NULL) {
			$config['P']['params']['allowedExtensions'] = $allowedExtensions;
		}
		return BackendUtility::getModuleUrl($moduleName, $config);
	}
}