<?php

namespace KayStrobach\Developer\ViewHelpers\Be\Menus;

class ActionMenuItemGroupViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Be\Menus\ActionMenuViewHelper
{
    /**
     * @var string
     */
    protected $tagName = 'optgroup';

    /**
     * @param string $label
     * @return string
     */
    public function render($label = '') 
    {
        $this->tag->addAttribute('label', $label);
        $options = '';
        foreach ($this->childNodes as $childNode) {
            if ($childNode instanceof \TYPO3\CMS\Fluid\Core\Parser\SyntaxTree\ViewHelperNode) {
                $options .= $childNode->evaluate($this->renderingContext);
            }
        }
        $this->tag->setContent($options);
        return $this->tag->render();
    }
}