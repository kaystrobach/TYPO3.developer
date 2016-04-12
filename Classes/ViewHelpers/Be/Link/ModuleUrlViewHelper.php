<?php

namespace KayStrobach\Developer\ViewHelpers\Be\Link;
use TYPO3\CMS\Backend\Utility\BackendUtility;

/**
 * Class HighlightViewHelper
 *
 * @package KayStrobach\Developer\ViewHelpers
 */
class ModuleUrlViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper
{

    /**
     * @param $moduleName
     * @param $config
     * @param string     $blindLinkOptions
     * @param string     $allowedExtensions
     * @return string
     */
    public function render($moduleName, $config, $blindLinkOptions = null, $allowedExtensions = null) 
    {
        return $this->getUrl($moduleName, $config, $blindLinkOptions, $allowedExtensions);
    }

    /**
     * @param $moduleName
     * @param $config
     * @return string
     */
    protected function getUrl($moduleName, $config, $blindLinkOptions = null, $allowedExtensions = null) 
    {
        $config = $config + array(
         'mode' => 'wizard'
        );
        if(!array_key_exists('P', $config)) {
            $config['P'] = array();
        }
        if(!array_key_exists('params', $config['P'])) {
            $config['P']['params'] = array();
        }
        if($blindLinkOptions !== null) {
            $config['P']['params']['blindLinkOptions'] = $blindLinkOptions;
        }
        if($allowedExtensions !== null) {
            $config['P']['params']['allowedExtensions'] = $allowedExtensions;
        }
        return BackendUtility::getModuleUrl($moduleName, $config);
    }
}