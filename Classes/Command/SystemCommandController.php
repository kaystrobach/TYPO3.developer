<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 17.10.14
 * Time: 08:06
 */

namespace KayStrobach\Developer\Command;


class SystemCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {
	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager = NULL;

	/**
	 * really clears all caches
	 */
	public function clearAllCachesCommand() {
		/** @var \TYPO3\CMS\Core\Cache\CacheManager $clearCacheService */
		$clearCacheService = $this->objectManager->get('TYPO3\CMS\Core\Cache\CacheManager');
		$clearCacheService->flushCaches();
	}
} 