<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 13.10.14
 * Time: 14:18
 */

namespace KayStrobach\Developer\ViewHelpers\Be\Link;


use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

class ModuleWizardViewHelper extends AbstractViewHelper{
	/**
	 * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
	 * @see isOutputEscapingEnabled()
	 *
	 * @var boolean
	 * @api
	 */
	protected $escapeOutput = false;

	/**
	 * @param string $moduleName
	 * @param string $formName
	 * @param string $formField
	 * @param array $config
	 * @param string $jsOptions
	 * @param string $icon
	 * @param string $blindLinkOptions
	 * @param string $allowedExtensions
	 * @return string
	 */
	public function render($moduleName, $formName, $formField, $config = array(), $jsOptions = '', $icon = '', $blindLinkOptions = NULL, $allowedExtensions = NULL) {
		$config = $config + array(
			'P' => array(
				'formName' => $formName,
				'itemName' => $formField,
				'params' => array()
			)
		);
		return $this->buildTag(
			$this->wrapJs($this->getUrl($moduleName, $config, $blindLinkOptions, $allowedExtensions), $jsOptions),
			$this->getIcon($icon) . $this->renderChildren()
		);
	}

	/**
	 * @param string $url
	 * @return string
	 */
	public function wrapJs($url, $jsOptions) {
		$js = 'this.blur(); vHWin=window.open(\'' . addslashes($url) . '\', \'popup\', \'' . $jsOptions . '\'); vHWin.focus();return false;';
		return $js;
	}

	/**
	 * @param string $icon
	 * @return string
	 */
	public function getIcon($icon) {

	}

	/**
	 * @param $onClick
	 * @param $icon
	 * @return string
	 */
	public function buildTag($onClick, $icon, $title = '') {
		return '<a href="#" onclick="' . $onClick . '" title="' . $title . '" >' . $icon . '</a>';
	}

	/**
	 * @param $moduleName
	 * @param $config
	 * @return string
	 */
	protected function getUrl($moduleName, $config, $blindLinkOptions = NULL, $allowedExtensions = NULL) {
		$config = $config + array(
				'mode' => 'wizard'
			);
		if(!array_key_exists('P', $config)) {
			$config['P'] = array();
		}
		if(!array_key_exists('params', $config['P'])) {
			$config['P']['params'] = array();
		}
		if($blindLinkOptions !== NULL) {
			$config['P']['params']['blindLinkOptions'] = $blindLinkOptions;
		}
		if($allowedExtensions !== NULL) {
			$config['P']['params']['allowedExtensions'] = $allowedExtensions;
		}
		return BackendUtility::getModuleUrl($moduleName, $config);
	}

} 