<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 02.10.14
 * Time: 19:21
 */

namespace KayStrobach\Developer\ViewHelpers;


class FlashMessageViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param string $title
     * @param string $type
     * @return string
     */
    public function render($title = '', $type = 'warning') 
    {
        $buffer = '<div class="typo3-message message-' . $type . '">';
        $buffer .= '<div class="header-container">';
        $buffer .= '<div class="message-header">' . $title . '</div>';
        $buffer .= '<div class="message-body">' . $this->renderChildren() . '</div>';
        $buffer .= '</div>';
        return $buffer;
    }
} 