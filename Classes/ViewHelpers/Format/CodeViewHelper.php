<?php

namespace KayStrobach\Developer\ViewHelpers\Format;

class CodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
	/**
	 * @param string $title
	 * @param string $language
	 * @return string
	 */
	public function render($title = NULL, $language = NULL) {
		$buffer = explode(LF, $this->renderChildren());
		if(trim($buffer[0]) !== '') {
			$cutOff = strlen($buffer[0]) - strlen(ltrim($buffer[0]));
		} else {
			unset($buffer[0]);
			$cutOff = strlen($buffer[1]) - strlen(ltrim($buffer[1]));
		}

		foreach($buffer as $line => $lineContent) {
			$buffer[$line] = substr($lineContent, $cutOff);
		}
		if(trim($buffer[count($buffer)-1]) === '') {
			unset($buffer[count($buffer)-1]);
		}
		return '<table class="t3-table"><tbody><tr><td><pre><code class="' . $language . '">' . htmlspecialchars(implode(LF, $buffer)) . '</code></pre></td></tr></tbody><tfoot><tr><td>' . $title . '</td></tr></tfoot></table>';
	}
} 