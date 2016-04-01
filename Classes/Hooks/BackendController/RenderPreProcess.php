<?php

namespace KayStrobach\Developer\Hooks\BackendController;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

class RenderPreProcess
{
	public function addBackendItems() {
		return;
		$this->getBackendController()->addToolbarItem('developer', 'KayStrobach\Developer\Backend\ToolbarItems\QueryRecorderLegacy');
		$this->getBackendController()->addCss(
			'
			#tx-developer-query-recorder .developer-queryrecorder-run {
				display:inline-block;
			}
			#tx-developer-query-recorder .developer-queryrecorder-stop {
				display:none;
			}
			#tx-developer-query-recorder.running .developer-queryrecorder-run {
				display:none;
			}
			#tx-developer-query-recorder.running .developer-queryrecorder-stop {
				display:inline-block;
			}
			'
		);
		$this->getBackendController()->addJavascriptFile(
			ExtensionManagementUtility::extRelPath('developer') . 'Resources/Public/Backend/ToolbarItem/QueryRecorder.js'
		);
	}

	/**
	 * @return \TYPO3\CMS\Backend\Controller\BackendController
	 */
	public function getBackendController() {
		return $GLOBALS['TYPO3backend'];
	}
}