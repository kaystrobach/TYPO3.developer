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
	 * Specifies whether the escaping interceptors should be disabled or enabled for the result of renderChildren() calls within this ViewHelper
	 * @see isChildrenEscapingEnabled()
	 *
	 * Note: If this is NULL the value of $this->escapingInterceptorEnabled is considered for backwards compatibility
	 *
	 * @var boolean
	 * @api
	 */
	protected $escapeChildren = false;

	/**
	 * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
	 * @see isOutputEscapingEnabled()
	 *
	 * @var boolean
	 * @api
	 */
	protected $escapeOutput = false;
	/**
	 * @var \TYPO3\CMS\Core\Package\PackageManager
	 * @inject
	 */
	protected $packageManager;

	/**
	 * Initializes the "then" and "else" arguments
	 */
	public function initializeArguments()
	{
		$this->registerArgument('package', 'string', 'package', FALSE);
	}

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