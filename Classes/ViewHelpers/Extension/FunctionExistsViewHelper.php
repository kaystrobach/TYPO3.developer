<?php
namespace KayStrobach\Developer\ViewHelpers\Extension;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class IfInstalledViewHelper
 *
 * checks if a given function exists is installed
 */
class FunctionExistsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * @param string $function
	 * @return string
	 */
	public function render($function = '') {
		if(function_exists($function)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

} 