<?php

namespace KayStrobach\Developer\ViewHelpers\Be\Buttons;

/**
 * Class HighlightViewHelper
 *
 * @package KayStrobach\Developer\ViewHelpers
 */
class IconClassViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @param string $icon
     * @return string
     */
    public function render($icon = '') 
    {
        return \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIconClasses($icon);
    }
}