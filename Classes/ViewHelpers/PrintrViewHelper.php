<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 02.10.14
 * Time: 12:48
 */

namespace KayStrobach\Developer\ViewHelpers;


class PrintrViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @param mixed $value
     * @return string
     */
    public function render($value) 
    {
        return '<pre>' . print_r($value, true) . '</pre>';
    }
} 