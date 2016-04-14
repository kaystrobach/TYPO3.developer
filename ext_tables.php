<?php

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'KayStrobach.' . $_EXTKEY,
        'help', // Main area
        'mod1', // Name of the module
        '', // Position of the module
        array(// Allowed controller action combinations
        'Default'      => 'index,downloadComposer',
        'Community'    => 'index,documentation',
        'Information'  => 'index,listPhpInfo,environmentVariables,hooks,signals,xclass,extDirect',
        'Language'     => 'index,xmlToXlf',
        'Extension'    => 'index,upload,uploadProcess,autoload,autoloadGenerate,codestylecheck,downloadT3x,directoryStructureCheck,compareWithTerVersion',
        'Distribution' => 'index,new,create,update,edit,t3dExport,status,resetStatus',
        'Tools'        => 'calculate,diff,,cssanalyze',
        'System'       => 'clearAllCaches,'
        ), array(// Additional configuration
        'access' => 'admin',
        'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/iconmonstr-wrench-7-icon-16.png',
        'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml',
        )
    );
}

/**
 * query logging
 */

// $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_db.php']['queryProcessors'][] = 'KayStrobach\Developer\Hooks\DatabaseConnection\QueryProcessor';
// if (TYPO3_MODE == 'BE') {
// 	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/backend.php']['renderPreProcess'][] = 'KayStrobach\Developer\Hooks\BackendController\RenderPreProcess->addBackendItems';
// 	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
// 		'developer::enableQueryRecording',
// 		'KayStrobach\Developer\Hooks\DatabaseConnection\QueryProcessor->enableQueryRecording'
// 	);
// 	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler (
// 		'developer::disableQueryRecording',
// 		'KayStrobach\Developer\Hooks\DatabaseConnection\QueryProcessor->disableQueryRecording'
// 	);
// }