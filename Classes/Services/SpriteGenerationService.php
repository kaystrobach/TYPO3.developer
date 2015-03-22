<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.03.15
 * Time: 21:36
 */

namespace KayStrobach\Developer\Services;


use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SpriteGenerationService {
	public function regenerateSprites() {
		/** @var $generator \TYPO3\CMS\Backend\Sprite\SpriteGenerator */
		$generator = GeneralUtility::makeInstance('TYPO3\CMS\Backend\Sprite\SpriteGenerator', 't3skin');

		$this->unlinkT3SkinFiles();

		$data = $generator
			->setSpriteFolder(TYPO3_mainDir . 'sysext/t3skin/images/sprites/')
			->setCSSFolder(TYPO3_mainDir . 'sysext/t3skin/stylesheets/sprites/')
			->setOmmitSpriteNameInIconName(TRUE)
			->setIncludeTimestampInCSS(TRUE)
			->generateSpriteFromFolder(array(TYPO3_mainDir . 'sysext/t3skin/images/icons/'));


		$stddbPath = ExtensionManagementUtility::extPath('core') . 'ext_tables.php';

		$stddbContents = file_get_contents($stddbPath);
		$newContent = '$GLOBALS[\'TBE_STYLES\'][\'spriteIconApi\'][\'coreSpriteImageNames\'] = array(' . LF . TAB . '\''
			. implode('\',' . LF . TAB . '\'', $data['iconNames']) . '\'' . LF . ');';
		$stddbContents = preg_replace('/\$GLOBALS\[\'TBE_STYLES\'\]\[\'spriteIconApi\'\]\[\'coreSpriteImageNames\'\] = array\([\s\',\w-]*\);/' , $newContent, $stddbContents);

		if (FALSE === GeneralUtility::writeFile($stddbPath, $stddbContents)) {
			throw new \Exception('Could not write file "' . $stddbPath . '"');
		}

		$output = 'Sprites successfully regenerated';

		return $output;
	}

	/**
	 * Unlinks old T3Skin files.
	 *
	 * @throws \Exception
	 */
	protected function unlinkT3SkinFiles() {
		$files = array(
			'stylesheets/ie6/z_t3-icons-gifSprites.css',
			'stylesheets/sprites/t3skin.css',
			'images/sprites/t3skin.png',
			'images/sprites/t3skin.gif',
		);

		foreach ($files as $file) {
			$filePath = PATH_typo3 . 'sysext/t3skin/' . $file;
			if (file_exists($filePath) && (FALSE === unlink($filePath))) {
				throw new \Exception('The file "' . $filePath . '" could not be removed');
			}
		}
	}

}