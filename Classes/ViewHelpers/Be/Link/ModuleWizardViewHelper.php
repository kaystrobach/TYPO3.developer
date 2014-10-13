<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 13.10.14
 * Time: 14:18
 */

namespace KayStrobach\Developer\ViewHelpers\Be\Link;


class ModuleWizardViewHelper extends ModuleUrlViewHelper {

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
	 * @param string $iconName
	 * @return string
	 */
	public function getIcon($icon) {
		return $icon === '' ? '' : \TYPO3\CMS\Backend\Utility\IconUtility::getSpriteIcon($icon);
	}

	/**
	 * @param $onClick
	 * @param $icon
	 * @return string
	 */
	public function buildTag($onClick, $icon, $title = '') {
		return '<a href="#" onclick="' . $onClick . '" title="' . $title . '" >' . $icon . '</a>';
	}

} 