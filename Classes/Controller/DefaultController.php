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
			'key' => 'phpunit',
			'pos' => 'with phpunit you can test your whole installation by running unit and functionaltests',
			'neg' => 'with phpunit you can test your whole installation by running unit and functionaltests',
		),
		array(
			'key' => 'styleguide',
			'pos' => 'with styleguide you can get an insight of the backend styles',
			'neg' => 'with styleguide you can get an insight of the backend styles',
		),
		array(
			'key' => 'extension_builder',
			'pos' => 'with the extension_builder you can easily kickstart extbase extensions',
			'neg' => 'with the extension_builder you can easily kickstart extbase extensions',
		),
		array(
			'key' => 'sphinx',
			'pos' => 'with sphinx you can render your documentation',
			'neg' => 'with sphinx you can render your documentation',
		),
		array(
			'key' => 'devlog',
			'pos' => 'with devlog it´s easier to see what your code is doing by using t3lib_div::devlog() or Generalutility::devlog()',
			'neg' => 'with devlog it´s easier to see what your code is doing by using t3lib_div::devlog() or Generalutility::devlog()',
		),
		array(
			'key' => 'kickstarter',
			'pos' => 'with the kickstarter you can start new piBase plugins, be aware that this extension may not work in 6.2',
			'neg' => 'with the kickstarter you can start new piBase plugins, be aware that the working version of this extension is not available in TER, but via Github https://github.com/mneuhaus/TYPO3-Kickstarter',
		),
	);

	/**
	 * default action and welcome screen
	 */
	public function indexAction() {
		foreach($this->extensionsToCheck as $extension) {
			$this->checkExtensionInstalled($extension['key'], $extension['pos'], $extension['neg']);
		}
		$this->view->assign('applicationContext', GeneralUtility::getApplicationContext());

		$this->view->assign('configurationContext', $this->getConfigurationPreset());
	}

	protected function getConfigurationPreset() {
		$features       = $this->featureManager->getInitializedFeatures(array());
		/** @var \TYPO3\CMS\Install\Configuration\Context\ContextFeature $contextPreset */
		$contextFeature = NULL;
		foreach($features as $feature) {
			if($feature instanceof \TYPO3\CMS\Install\Configuration\Context\ContextFeature) {
				$contextFeature = $feature;
				continue;
			}
		}
		if($contextFeature === NULL) {
			return NULL;
		}
		$presets = $contextFeature->getPresetsOrderedByPriority();
		foreach($presets as $preset) {
			/** @var \TYPO3\CMS\Install\Configuration\AbstractPreset $preset */
			if($preset->isActive()) {
				return $preset;
			}
		}
	}

	/**
	 * Wrapper for checking the extension state and displaying a flashmessage
	 *
	 * @param $extensionName
	 * @param $positiveMessage
	 * @param $negativeMessage
	 */
	protected function checkExtensionInstalled($extensionName, $positiveMessage, $negativeMessage) {
		if(ExtensionManagementUtility::isLoaded($extensionName, FALSE)) {
			$this->addFlashMessage($positiveMessage, 'Extension ' . $extensionName . ' installed', FlashMessage::OK);
		} else {
			$this->addFlashMessage($negativeMessage, 'Extension ' . $extensionName . ' NOT installed', FlashMessage::WARNING);
		}
	}
}