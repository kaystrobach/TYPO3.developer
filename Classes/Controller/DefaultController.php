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
			'key' => 'typo3_console',
			'description' => 'use this extension to add a very nice commandline interface to TYPO3.',
		),
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
		array(
			'key' => 'additional_reports',
			'description' => 'Useful informations in the reports module : xclass, ajax, cliKeys, eID, general status of the system (encoding, DB, php vars...), hooks, compare local and TER extension (diff), used content type, used plugins, ExtDirect... It can really help you during migration or new existing project (to have a global reports of the system).',
		),
		array(
			'key' => 't3adminer',
			'description' => 'SQL Administration tool, which is similar to PHPMyAdmin but well integrated and faster. And due to a well done integration less risky then having phpmyadmin installed in TYPO3.',
		),
		array(
			'key' => 'examples',
			'description' => 'Contains some code examples on how to use the TYPO3 core API.',
		),
		array(
			'key' => 'lfeditor',
			'description' => 'Easy to use editor for xlf and locallang files.',
		)
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
		array(
			'key' => 'typo3-ci/typo3cms',
			'description' => 'CodeSniffing for TYPO3 CMS CGL, includes phpcs/phpcs as dependency'
		),
		array(
			'key' => 'sebastian/phpcpd',
			'description' => 'phpcpd is a Copy/Paste Detector (CPD) for PHP code.',
		),
		array(
			'key' => 'phpmd/phpmd',
			'description' => 'finds mess in your Code, Possible bugs, Suboptimal code, Overcomplicated expressions, Unused parameters, methods, properties'
		),
		array(
			'key' => 'pdepend/pdepend',
			'description' => 'PHP Depend can generate a large set of software metrics from a given code base, these values can be used to measure the quality of a software project and they help to identify that parts of an application where a refactoring should be applied.'
		),
		array(
			'key' => 'mayflower/php-codebrowser',
			'description' => 'Generates a browsable representation of PHP code where sections with violations found by quality assurance tools such as PHP_CodeSniffer or PHPMD are highlighted.'
		),
		array(
			'key' => 'nikic/php-parser',
			'description' => 'Allows you to parse php code in pure php, can be used for syntax checks',
		)
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
		$features = $this->featureManager->getInitializedFeatures(array());
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
