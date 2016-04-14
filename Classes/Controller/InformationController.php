<?php

namespace KayStrobach\Developer\Controller;

use KayStrobach\Developer\Services\PhpInfoService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class InformationController
 *
 * Displays some information
 *
 * @package KayStrobach\Developer\Controller
 */
class InformationController extends ActionController
{
    /**
     * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
     * @inject
     */
    protected $signalSlotDispatcher;

    /**
     * show phpinfo
     */
    public function listPhpInfoAction() 
    {
        $this->view->assign('information', PhpInfoService::extractPhpInfoData());
    }

    public function environmentVariablesAction() 
    {
        $this->view->assign('environment', GeneralUtility::getIndpEnv('_ARRAY'));
    }

    /**
     *
     */
    public function hooksAction() 
    {
        $this->view->assign('hooks', $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']);
    }

    /**
     *
     */
    public function signalsAction() 
    {
        $reflection = new \ReflectionClass($this->signalSlotDispatcher);
        $attribute = $reflection->getProperty('slots');
        $attribute->setAccessible(true);
        $this->view->assign('classes', $attribute->getValue($this->signalSlotDispatcher));
    }

    /**
     *
     */
    public function xclassAction() 
    {
        $this->view->assign('xClasses', $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']);
    }

    /**
     *
     */
    public function extDirectAction() {
        $extdirects = $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ExtDirect'];
        foreach($extdirects as $key => $value) {
            if(isset($value['callbackClass'])) {
                $extdirects[$key]['functions'] = get_class_methods('\\' . $value['callbackClass']);
            }
        }

        $this->view->assign('extdirects', $extdirects);
    }

}