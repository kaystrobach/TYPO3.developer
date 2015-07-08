<?php

if (TYPO3_MODE === 'BE') {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'KayStrobach.' . $_EXTKEY, 'tools', // Main area
		'mod1', // Name of the module
		'', // Position of the module
		array(// Allowed controller action combinations
			'Default'      => 'index',
			'Community'    => 'index,documentation',
			'Information'  => 'index,listPhpInfo,environmentVariables,hooks,signals,xclass',
			'Language'     => 'index,xmlToXlf',
			'Sprite'       => 'listSpriteIcons,regenerateSkinFiles,listTableIcons',
			'Extension'    => 'index,upload,uploadProcess,autoload,autoloadGenerate,codestylecheck,downloadT3x,directoryStructureCheck',
			'Distribution' => 'index,new,create,update,edit,t3dExport',
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
 * add sprites
 */
\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons(
	array(
		'wrench' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath($_EXTKEY) . 'Resources/Public/Icons/iconmonstr-wrench-7-icon-16.png',
	),
	$_EXTKEY
);