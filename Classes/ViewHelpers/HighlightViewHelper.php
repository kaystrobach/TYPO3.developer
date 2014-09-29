<?php

namespace KayStrobach\Developer\ViewHelpers;

/**
 * Class HighlightViewHelper
 * @package KayStrobach\Developer\ViewHelpers
 */
class HighlightViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {

	/**
	 * @return string
	 */
	public function render() {
		return highlight_string($this->renderChildren(), TRUE);
	}
}