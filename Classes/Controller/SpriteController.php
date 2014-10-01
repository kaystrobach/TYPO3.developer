<?php

namespace KayStrobach\Developer\Controller;

use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class SpriteController extends ActionController {
	/**
	 *
	 */
	public function listSpriteIconsAction() {
		$this->view->assign('sprites', $GLOBALS['TBE_STYLES']['spriteIconApi']['iconsAvailable']);
	}

	/**
	 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
	 */
	public function regenerateSkinFilesAction() {
		$files = array(
			'stylesheets/ie6/z_t3-icons-gifSprites.css',
			'stylesheets/sprites/t3skin.css',
			'images/sprites/t3skin.png',
			'images/sprites/t3skin.gif',
		);

		foreach ($files as $file) {
			$filePath = PATH_typo3 . 'sysext/t3skin/' . $file;
			if (file_exists($filePath) && (FALSE === unlink($filePath))) {
				$this->addFlashMessage('The file "' . $filePath . '" could not be removed', '', AbstractMessage::ERROR);
			}
		}
		$this->addFlashMessage('Sprites refreshed', '', AbstractMessage::OK);
		$this->redirect('listSpriteIcons');
	}

	/**
	 *
	 */
	public function listTableIconsAction() {
		$tableIconService = GeneralUtility::makeInstance('KayStrobach\Developer\Services\TableIconService');
		$this->view->assign('tables', $tableIconService->getIconsForAllTables());
	}
} 