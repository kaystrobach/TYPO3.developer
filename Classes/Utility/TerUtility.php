<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 08.07.15
 * Time: 15:18
 */

namespace KayStrobach\Developer\Utility;


class TerUtility {
	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\Repository\Helper
	 * @inject
	 */
	protected $repositoryHelper;

	/**
	 * @var \TYPO3\CMS\Extensionmanager\Utility\Connection\TerUtility
	 * @inject
	 */
	protected $terUtility;

	/**
	 * @param \TYPO3\CMS\Extensionmanager\Domain\Model\Extension $extension
	 */
	public function downloadToTemp($extension) {
		$mirrorUrl = $this->repositoryHelper->getMirrors()->getMirrorUrl();
		return $fetchedExtension = $this->terUtility->fetchExtension($extension->getExtensionKey(), $extension->getVersion(), $extension->getMd5hash(), $mirrorUrl);

	}
}