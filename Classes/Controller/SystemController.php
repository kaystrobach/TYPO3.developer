<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 30.09.14
 * Time: 18:50
 */

namespace KayStrobach\Developer\Controller;


use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class SystemController
 *
 * does system actions
 *
 * @package KayStrobach\Developer\Controller
 */
class SystemController extends ActionController{
	/**
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 * @inject
	 */
	protected $objectManager = NULL;

	/**
	 * really clears all caches
	 * @param bool $execute
	 */
	public function clearAllCachesAction($execute = TRUE) {
		if($execute === TRUE) {
			/** @var \TYPO3\CMS\Core\Cache\CacheManager $clearCacheService */
			$clearCacheService = $this->objectManager->get('TYPO3\CMS\Core\Cache\CacheManager');
			$clearCacheService->flushCaches();
		}
	}
}