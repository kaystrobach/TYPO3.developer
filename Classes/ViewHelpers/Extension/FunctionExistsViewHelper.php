<?php
namespace KayStrobach\Developer\ViewHelpers\Extension;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Class IfInstalledViewHelper
 *
 * checks if a given function exists is installed
 */
class FunctionExistsViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper
{
    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
  *
     * @see isOutputEscapingEnabled()
     *
     * @var boolean
     * @api
     */
    protected $escapeOutput = false;

    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('function', 'string', 'package', false);
    }

    /**
     * @param string $function
     * @return string
     */
    public function render($function = '') 
    {
        if(function_exists($function)) {
            return $this->renderThenChild();
        } else {
            return $this->renderElseChild();
        }
    }

} 