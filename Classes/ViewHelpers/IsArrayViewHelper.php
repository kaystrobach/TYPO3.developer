<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 02.10.14
 * Time: 12:44
 */

namespace KayStrobach\Developer\ViewHelpers;


class IsArrayViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractConditionViewHelper {
	/**
	 * @param mixed $value
	 * @return string
	 */
	public function render($value) {
		if(is_array($value)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

} 