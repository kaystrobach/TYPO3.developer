<?php
namespace KayStrobach\Developer\ViewHelpers\Extension;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class IfInstalledViewHelper
 *
 * checks if extension is installed
 */
class IfInstalledViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * @param string $extensionName
	 * @return string
	 */
	public function render($extensionName = '') {
		if(ExtensionManagementUtility::isLoaded($extensionName, FALSE)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

} 