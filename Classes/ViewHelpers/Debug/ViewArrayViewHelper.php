<?php

namespace KayStrobach\Developer\ViewHelpers\Debug;
use TYPO3\CMS\Core\Utility\DebugUtility;


/**
 * Class ViewArrayViewHelper
 *
 * @package KayStrobach\Developer\ViewHelpers\Be\Buttons
 */
class ViewArrayViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @param mixed $value
     * @return string
     */
    public function render($value) 
    {
        return DebugUtility::viewArray($value);
    }
} 