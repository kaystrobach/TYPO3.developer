<?php

if (TYPO3_MODE === 'BE') {
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'KayStrobach.' . $_EXTKEY, 'tools', // Main area
		'mod1', // Name of the module
		'', // Position of the module
		array(// Allowed controller action combinations
			'Default'     => 'index',
			'Information' => 'index,listPhpInfo,documentation',
			'Language'    => 'index,xmlToXlf',
			'Sprite'      => 'listSpriteIcons,regenerateSkinFiles,listTableIcons',
			'Extension'   => 'index,upload,uploadProcess,autoload,autoloadGenerate',
			'Tools'       => 'calculate,diff,cssanalyze',
			'System'      => 'clearAllCaches,'
		), array(// Additional configuration
			'access' => 'admin',
			'icon' => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/iconmonstr-wrench-7-icon-16.png',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang.xml',
		)
	);
}
