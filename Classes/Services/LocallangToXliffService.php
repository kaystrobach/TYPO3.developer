<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 13.10.14
 * Time: 16:28
 */

namespace KayStrobach\Developer\Services;


use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocallangToXliffService {

	/**
	 * @var string
	 */
	protected $xml = NULL;

	/**
	 * @var null
	 */
	protected $parsedXml = NULL;
	/**
	 * @var array
	 */
	protected $languageLabels = NULL;
	/**
	 * @param $xml
	 */
	public function setXml($xml) {
		$this->xml = $xml;
		$this->parsedXml = GeneralUtility::xml2array($this->xml);
	}

	/**
	 * @return array
	 */
	public function getDefinedLanguages() {
		if (!isset($this->parsedXml['data'])) {
			throw new \RuntimeException('data section not found in xml', 1314187884);
		}
		return array_keys($this->parsedXml['data']);
	}

	/**
	 * @param $langKey
	 * @param string $productName
	 * @return string
	 */
	public function getXlfForLangKey($langKey, $productName = '') {
		// Initialize variables:
		$xml = array();
		$xml[] = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>';
		$xml[] = '<xliff version="1.0">';
		$xml[] = '	<file source-language="en"' . ($langKey !== 'default' ? ' target-language="' . $langKey . '"' : '')
			. ' datatype="plaintext" original="messages" date="' . gmdate('Y-m-d\TH:i:s\Z') . '"'
			. ' product-name="' . $productName . '">';
		$xml[] = '		<header/>';
		$xml[] = '		<body>';

		foreach ($this->parsedXml['data'][$langKey] as $key => $data) {

			if ($langKey === 'default') {
				$xml[] = '			<trans-unit id="' . $key . '" xml:space="preserve">';
				$xml[] = '				<source>' . $data . '</source>';
				$xml[] = '			</trans-unit>';
			} else {
				$xml[] = '			<trans-unit id="' . $key . '" xml:space="preserve" approved="yes">';
				$xml[] = '				<source>' . $this->parsedXml['data']['default'][$key] . '</source>';
				$xml[] = '				<target>' . $data . '</target>';
				$xml[] = '			</trans-unit>';
			}
		}

		$xml[] = '		</body>';
		$xml[] = '	</file>';
		$xml[] = '</xliff>';

		return implode(LF, $xml);
	}

	/**
	 * @param string $langKey
	 * @return string
	 */
	public function getFileExtension($langKey) {
		if($langKey === 'default') {
			return '.xlf';
		} else {
			return '.' . $langKey . '.xlf';
		}
	}
} 