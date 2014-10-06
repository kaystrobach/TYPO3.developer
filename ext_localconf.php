<?php

/**
 * Signalslot for adding icons in the extmgm
 *
 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher
 */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\SignalSlot\\Dispatcher');
$signalSlotDispatcher->connect(
	'TYPO3\\CMS\\Extensionmanager\\ViewHelpers\\ProcessAvailableActionsViewHelper',
	'processActions',
	'KayStrobach\\Developer\\Slots\\ExtensionManager',
	'processActions'
);

/**
 * Register ExtDirect Commands
 */

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerExtDirectComponent(
	'TYPO3.Ajax.ExtDirect.Developer',
	'KayStrobach\Developer\ExtDirect\CommandsExtDirect',
	NULL,
	'user,group'
);