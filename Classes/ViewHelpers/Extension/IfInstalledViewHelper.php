<?php
namespace KayStrobach\Developer\ViewHelpers\Extension;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class IfInstalledViewHelper
 *
 * checks if extension is installed
 */
class IfInstalledViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{

    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('extensionName', 'string', 'extensionName', false);
    }

    /**
     * @param string $extensionName
     * @return string
     */
    public function render($extensionName = '') 
    {
        if(ExtensionManagementUtility::isLoaded($extensionName, false)) {
            return $this->renderThenChild();
        } else {
            return $this->renderElseChild();
        }
    }

} 