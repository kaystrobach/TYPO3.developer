<?php
namespace KayStrobach\Developer\ViewHelpers\Package;
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
		$package = $this->packageManager->getPackageKeyFromComposerName($package);
		if($this->packageManager->isPackageAvailable($package)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

} 