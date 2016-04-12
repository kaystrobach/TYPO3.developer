<?php

namespace KayStrobach\Developer\ViewHelpers\Format;

class CodeViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper {
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
		return '<p><small>' . $title . '</small><br><pre class="language' . $language . '"><code class="' . $language . '">' . htmlspecialchars(implode(LF, $buffer))
		. '</code></pre></p>';
	}
} 