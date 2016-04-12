<?php

namespace KayStrobach\Developer\ViewHelpers\Be\Link;

/**
 * Class HighlightViewHelper
 *
 * @package KayStrobach\Developer\ViewHelpers
 */
class ModuleLinkViewHelper extends ModuleUrlViewHelper
{

    /**
     * @param string $icon
     * @return string
     */
    public function render($moduleName, $config) 
    {
        return $this->getUrl($moduleName, $config);
    }
}