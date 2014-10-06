<?php
namespace KayStrobach\Developer\ViewHelpers\Package;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\Flow\Package\PackageManager;


/**
 * Class IfInstalledViewHelper
 *
 * checks if extension is installed
 */
class IfInstalledViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {
	/**
	 * @var \TYPO3\CMS\Core\Package\PackageManager
	 * @inject
	 */
	protected $packageManager;

	/**
	 * @param string $package
	 * @return string
	 */
	public function render($package = '') {
		$this->packageManager->scanAvailablePackages();

		$packages = GeneralUtility::trimExplode(',', $package);

		$allAvailable = TRUE;

		foreach($packages as $package) {
			if(!$this->isInstalled($package)) {
				$allAvailable = FALSE;
			}
		}

		if($allAvailable) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

	/**
	 * @param $package
	 * @return boolean
	 */
	protected function isInstalled($package) {
		$package = $this->packageManager->getPackageKeyFromComposerName($package);
		return $this->packageManager->isPackageAvailable($package);
	}

} 