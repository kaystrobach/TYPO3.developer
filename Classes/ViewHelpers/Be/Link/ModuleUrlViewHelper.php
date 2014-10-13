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
	 * @return string
	 */
	public function render($moduleName, $config) {
		return $this->getUrl($moduleName, $config);
	}

	/**
	 * @param $moduleName
	 * @param $config
	 * @return string
	 */
	protected function getUrl($moduleName, $config) {
		$config = $config + array(
			'mode' => 'wizard'
		);
		return BackendUtility::getModuleUrl($moduleName, $config);
	}
}