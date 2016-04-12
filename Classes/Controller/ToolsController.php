<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 30.09.14
 * Time: 18:50
 */

namespace KayStrobach\Developer\Controller;


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ToolsController
 *
 * contains some calculators and other tools
 *
 * @package KayStrobach\Developer\Controller
 */
class ToolsController extends ActionController
{
    /**
     * simply calculator
     */
    public function calculateAction() 
    {

    }

    /**
     * diff 2 textareas
     */
    public function diffAction() 
    {

    }

    /**
     * analyze css
  *
     * @param string $sourceCode
     */
    public function cssanalyzeAction($sourceCode = null) 
    {
        $this->view->assign('sourceCode', $sourceCode);
        if($sourceCode !== null) {
            /**
 * @var \KayStrobach\Developer\Services\CssAnalyzeService $cssAnalyzeService 
*/
            $cssAnalyzeService = GeneralUtility::makeInstance('KayStrobach\Developer\Services\CssAnalyzeService');
            $this->view->assign(
                'result',
                $cssAnalyzeService->getHierarchy($sourceCode)
            );
        }
    }
} 