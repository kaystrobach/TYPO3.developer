<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 14.10.15
 * Time: 18:10
 */

namespace KayStrobach\Developer\Backend\ToolbarItems;


use TYPO3\CMS\Backend\Utility\IconUtility;

class QueryRecorderLegacy implements \TYPO3\CMS\Backend\Toolbar\ToolbarItemHookInterface
{
	/**
	 * @var \TYPO3\CMS\Backend\Controller\BackendController
	 */
	protected $backendReference;

	public function __construct(\TYPO3\CMS\Backend\Controller\BackendController &$backendReference = NULL) {
		$this->backendReference = $backendReference;
	}
	public function checkAccess () {
		return $this->getBackendUser()->isAdmin();
	}

	public function render () {
		$buffer = '<span class="developer-queryrecorder-run">'
			. IconUtility::getSpriteIcon('extensions-scheduler-run-task')
			. '</span>'
			. '<span class="developer-queryrecorder-stop">'
			. IconUtility::getSpriteIcon('apps-pagetree-drag-place-denied');
		return $buffer;
	}

	public function getAdditionalAttributes () {
		return ' id="tx-developer-query-recorder"';
	}

	/**
	 * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
	 */
	protected function getBackendUser() {
		return $GLOBALS['BE_USER'];
	}

}