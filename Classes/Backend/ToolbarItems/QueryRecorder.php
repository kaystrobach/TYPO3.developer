<?php
namespace  KayStrobach\Developer\Backend\ToolbarItems;

use TYPO3\CMS\Backend\Toolbar\ToolbarItemInterface;
use TYPO3\CMS\Backend\Utility\IconUtility;

class QueryRecorder implements ToolbarItemInterface
{

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser() 
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * Returns LanguageService
     *
     * @return \TYPO3\CMS\Lang\LanguageService
     */
    protected function getLanguageService() 
    {
        return $GLOBALS['LANG'];
    }

    /**
     * Checks whether the user has access to this toolbar item
  *
     * @TODO: Split into two methods a permission method and a "hasContent" or similar
     *
     * @return bool TRUE if user has access, FALSE if not
     */
    public function checkAccess()
    {
        return $this->getBackendUser()->isAdmin();
    }

    /**
     * Render "item" part of this toolbar
     *
     * @return string Toolbar item HTML
     */
    public function getItem()
    {
        $title = $this->getLanguageService()->sL('LLL:EXT:lang/locallang_core.xlf:rm.clearCache_clearCache', true);
        return IconUtility::getSpriteIcon('apps-toolbar-menu-cache', array('title' => $title));
    }

    /**
     * TRUE if this toolbar item has a collapsible drop down
     *
     * @return bool
     */
    public function hasDropDown()
    {
        return true;
    }

    /**
     * Render "drop down" part of this toolbar
     *
     * @return string Drop down HTML
     */
    public function getDropDown()
    {
        return '';
    }

    /**
     * Returns an array with additional attributes added to containing <li> tag of the item.
     *
     * Typical usages are additional css classes and data-* attributes, classes may be merged
     * with other classes needed by the framework. Do NOT set an id attribute here.
     *
     * array(
     *     'class' => 'my-class',
     *     'data-foo' => '42',
     * )
     *
     * @return array List item HTML attributes
     */
    public function getAdditionalAttributes()
    {
        // TODO: Implement getAdditionalAttributes() method.
    }

    /**
     * Returns an integer between 0 and 100 to determine
     * the position of this item relative to others
     *
     * By default, extensions should return 50 to be sorted between main core
     * items and other items that should be on the very right.
     *
     * @return int 0 .. 100
     */
    public function getIndex()
    {
        return 50;
    }
}