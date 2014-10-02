<?php

namespace KayStrobach\Developer\Controller;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class DefaultController
 *
 * Welcome Controller
 *
 * @package KayStrobach\Developer\Controller
 */
class DefaultController extends ActionController {
	/**
	 * @var \TYPO3\CMS\Install\Configuration\FeatureManager
	 * @inject
	 */
	protected $featureManager;

	/**
	 * list of extensions to be checked on welcome screen
	 * @var array
	 */
	protected $extensionsToCheck = array(
		array(
			'key' => 'styleguide',
			'description' => 'with styleguide you can get an insight of the backend styles',
		),
		array(
			'key' => 'extension_builder',
			'description' => 'with the extension_builder you can easily kickstart extbase extensions',
		),
		array(
			'key' => 'builder',
			'description' => 'with builder you get various development supports for building and working with Fluid templates and Extbase extensions',
		),
		array(
			'key' => 'devlog',
			'description' => 'with devlog it´s easier to see what your code is doing by using t3lib_div::devlog() or Generalutility::devlog()',
		),
		array(
			'key' => 'sphinx',
			'description' => 'with sphinx you can render your documentation',
		),
		array(
			'key' => 'uncache',
			'description' => 'with uncache all caches are prevented and disabled. NOT FOR PRODUCTION USE!',
		),
		array(
			'key' => 'kickstarter',
			'description' => 'with the kickstarter you can start new piBase plugins, be aware that this extension may not work in 6.2',
		),
	);

	protected $phpExtensionsToCheck = array(
		array(
			'key' => 'xdebug',
			'function' => 'xdebug_call_line',
			'description' => 'with XDebug you can take a look into the settings in the phpinfo module, please refer to your IDE help to get setup instructions.',
		),
	);

	protected $composerPackagesToCheck = array(
		array(
			'key' => 'phpunit/phpunit',
			'description' => 'with phpunit you can test your whole installation by running unit and functionaltests',
		),
	);

	/**
	 * default action and welcome screen
	 */
	public function indexAction() {

		$this->view->assign('extensions', $this->extensionsToCheck);
		$this->view->assign('phpExtensions', $this->phpExtensionsToCheck);
		$this->view->assign('composerPackages', $this->composerPackagesToCheck);

		$this->view->assign('applicationContext', GeneralUtility::getApplicationContext());
		$this->view->assign('configurationContext', $this->getConfigurationPreset());
	}

	protected function getConfigurationPreset() {
		$features       = $this->featureManager->getInitializedFeatures(array());
		/** @var \TYPO3\CMS\Install\Configuration\Context\ContextFeature $contextPreset */
		$contextFeature = NULL;
		foreach ($features as $feature) {
			if ($feature instanceof \TYPO3\CMS\Install\Configuration\Context\ContextFeature) {
				$contextFeature = $feature;
				continue;
			}
		}
		if ($contextFeature === NULL) {
			return NULL;
		}
		$presets = $contextFeature->getPresetsOrderedByPriority();
		foreach ($presets as $preset) {
			/** @var \TYPO3\CMS\Install\Configuration\AbstractPreset $preset */
			if ($preset->isActive()) {
				return $preset;
			}
		}
	}
}