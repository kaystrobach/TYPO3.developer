<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 22.03.15
 * Time: 21:32
 */

namespace KayStrobach\Developer\Command;


use TYPO3\CMS\Core\Utility\GeneralUtility;

class CoreCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {
	/**
	 * regenerates the icon sprites
	 */
	public function regenerateSpriteCommand() {
		/** @var \KayStrobach\Developer\Services\SpriteGenerationService $iconService */
		$iconService = GeneralUtility::makeInstance('KayStrobach\Developer\Services\SpriteGenerationService');
		$iconService->regenerateSprites();
	}
}