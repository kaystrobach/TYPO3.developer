<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 01.10.14
 * Time: 14:29
 */

namespace KayStrobach\Developer\ViewHelpers;


/**
 * Class Md5ViewHelper
 *
 * @package KayStrobach\Developer\ViewHelpers
 */
class Md5ViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * md5's a string
     *
     * @param  string value
     * @return string
     */
    public function render($value) 
    {
        return md5($value);
    }
} 