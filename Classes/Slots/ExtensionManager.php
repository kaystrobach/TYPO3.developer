<?php

namespace KayStrobach\Developer\Slots;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ExtensionManager {

	/**
	 * Extends the list of actions for EXT:developer to change the
	 * icon of "ext_update.php"
	 *
	 * @param array $extension
	 * @param array $actions
	 */
	public function processActions(array $extension, array &$actions) {
		if($extension['type'] !== 'System') {
			/** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
			$view = GeneralUtility::makeInstance('\TYPO3\CMS\Fluid\View\StandaloneView');
			$view->setTemplatePathAndFilename(ExtensionManagementUtility::extPath('developer') . '/Resources/Private/Templates/Slots/Extensionmanager.html');
			$view->assignMultiple(
				array(
					'extension' => $extension,
					'actions' => array(
						array(
							'icon'       => 'extensions-developer-wrench',
							'label'      => 'open extension in ext:developer',
							'action'     => 'index',
							'controller' => 'Extension',
						),
						array(
							'icon'       => 'actions-document-export-t3d',
							'label'      => 'Download t3x',
							'action'     => 'downloadT3x',
							'controller' => 'Extension',
						),
					)
				)
			);
			$buffer = '<span class="t3-icon t3-icon-actions t3-icon-actions-document t3-icon-document-open"></span>';
			$buffer = $view->render();
			array_unshift($actions, $buffer);
		}
	}

}