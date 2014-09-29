<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Kasper Sk�hj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Conversion of locallang.php files to new XML format.
 *
 * $Id: class.tx_extdeveval_ll2xml.php 63721 2012-06-22 14:12:37Z ohader $
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   59: class tx_extdeveval_ll2xml
 *   68:     function main($phpFile, $extensionDir)
 *   94:     function renderSaveForm($phpFile)
 *  130:     function renderSaveDone($phpFile,$newFileName)
 *
 *              SECTION: Helper functions
 *  205:     function checkLLfilename($phpFile)
 *  218:     function getLLarray($phpFile)
 *  243:     function convertLLarrayToUTF8($LOCAL_LANG)
 *  273:     function localizedFileRef($fileRef,$lang)
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Conversion of locallang.php files to new XML format.
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_ll2xml {

	/**
	 * Main function
	 *
	 * @param	string		Absolute path to the selected PHP file
	 * @param	string		Extension dir (local, global, system) relative to PATH_site
	 * @return	string		HTML content.
	 */
	function main($phpFile, $extensionDir)	{

		if (@is_file($phpFile))	{
			$fCheck = $this->checkLLfilename($phpFile);
			if (!$fCheck)	{
				$newFileName = preg_replace('#\.php$#', '.xml', $phpFile);
				if (!@is_file($newFileName))	{

					if (t3lib_div::_GP('doSave'))	{
						return $this->renderSaveDone($phpFile,$newFileName);
					} else {
						return $this->renderSaveForm($phpFile);
					}

					return Tx_Extdeveval_Compatibility::viewArray(array($phpFile, $extensionDir));
				} else return 'ERROR: Output file "'.$newFileName.'" existed already!';
			} else return 'ERROR: '.$fCheck;
		}
	}

	/**
	 * Creates the form which allows to specify description, type etc and perform the conversion.
	 *
	 * @param	string		The PHP locallang.php file reference (absolute)
	 * @return	string		HTML output (form)
	 */
	function renderSaveForm($phpFile)	{
		$formOutput = '
		<form action="'.t3lib_div::linkThisScript().'" method="post">
			Description of file context:<br/>
			<input type="text" name="_DAT[meta][description]" /><br/>
			<br/>

			Type:<br/>
			<select name="_DAT[meta][type]">
				<option value=""></option>
				<option value="database">Database tables / fields</option>
				<option value="module">Module labels</option>
				<option value="CSH">CSH (Context Sensitive Help)</option>
			</select>
			<br/>

			CSH "table" (ONLY for CSH types!):<br/>
			(This is the same value as the first argument in the function call t3lib_extMgm::addLLrefForTCAdescr() for this file in ext_tables.php)<br/>
			<input type="text" name="_DAT[meta][csh_table]" /><br/>
			<br/>

			<input type="submit" name="doSave" value="Convert" />
		</form>
		';

		return $formOutput;
	}

	/**
	 * Processing of the submitted form; Will create and write the XML file and tell the new file name.
	 *
	 * @param	string		Absolute path to the locallang.php file to convert.
	 * @param	string		The new file name to write to (absolute path, .xml ending). The locallang-xml file is in UTF-8
	 * @return	string		HTML text string message
	 */
	function renderSaveDone($phpFile,$newFileName)	{

			// Initialize variables:
		$outputArray = array();
		$formContent = t3lib_div::_POST('_DAT');
		$LOCAL_LANG = $this->getLLarray($phpFile);
		$LOCAL_LANG = $this->convertLLarrayToUTF8($LOCAL_LANG);

			// Setting meta data:
		$outputArray['meta'] = $formContent['meta'];

			// Setting label context dummy tags:
		$outputArray['meta']['labelContext'] = array();
	/*	foreach($LOCAL_LANG['default'] as $labelKey => $labelValue)	{
			$outputArray['meta']['labelContext'][$labelKey] = '';
		}
*/
			// Setting data content:
		$outputArray['data'] = $LOCAL_LANG;

			// Setting orig-hash/content
		foreach($LOCAL_LANG as $langKey => $labelsArray)	{
			$outputArray['orig_hash'][$langKey] = array();
			$outputArray['orig_text'][$langKey] = array();
			foreach($labelsArray as $labelKey => $_temp)	{
				$outputArray['orig_hash'][$langKey][$labelKey] = t3lib_div::md5int($LOCAL_LANG['default'][$labelKey]);
				#$outputArray['orig_text'][$langKey][$labelKey] = $LOCAL_LANG['default'][$labelKey];
			}
		}

			// Options:
		$options = array(
			#'useIndexTagForAssoc'=>'key',
			'parentTagMap' => array(
				'data' => 'languageKey',
				'orig_hash' => 'languageKey',
				'orig_text' => 'languageKey',
				'labelContext' => 'label',
				'languageKey' => 'label'
			)
		);

			// Creating XML file from $outputArray:
		$XML = '<?xml version="1.0" encoding="utf-8" standalone="yes" ?>'.chr(10);
		$XML.= t3lib_div::array2xml($outputArray,'',0,'T3locallang',4,$options);

		if (!file_exists($newFileName))	{
	#		debug(array($XML));
			t3lib_div::writeFile($newFileName,$XML);
			return 'File written to disk: '.$newFileName;
		}
	}











	/****************************
	 *
	 * Helper functions
	 *
	 ****************************/

	/**
	 * Checking for a valid locallang*.php filename.
	 *
	 * @param	string		Absolute reference to the php locallang file.
	 * @return	string		Empty (false) return value means "OK" while otherwise is an error string.
	 */
	function checkLLfilename($phpFile)	{
		$basename = basename($phpFile);
		if (!t3lib_div::isFirstPartOfStr($basename, 'locallang') || preg_match('#\.[a-z][a-z]\.php$#', $basename))	{
			return 'Filename didn\'t start with "locallang" or had a language suffix.';
		}
	}

	/**
	 * Includes locallang files and returns raw $LOCAL_LANG array
	 *
	 * @param	string		Absolute reference to the php locallang file.
	 * @return	array		LOCAL-LANG array from php file (with all possible sub-files for languages included)
	 */
	function getLLarray($phpFile)	{
		$LOCAL_LANG = t3lib_div::readLLfile($phpFile, $GLOBALS['LANG']->lang, $GLOBALS['LANG']->charSet);

		if (defined('TYPO3_languages')) {
			$languages = explode('|', TYPO3_languages);
			foreach($languages as $langKey)	{

					// Localized addition?
				$lFileRef = $this->localizedFileRef($phpFile,$langKey);
				if ($lFileRef && (string)$LOCAL_LANG[$langKey]=='EXT')	{
					$llang = t3lib_div::readLLfile($lFileRef, $GLOBALS['LANG']->lang, $GLOBALS['LANG']->charSet);
					$LOCAL_LANG = t3lib_div::array_merge_recursive_overrule($LOCAL_LANG, $llang);
				}
			}
		}

		return $LOCAL_LANG;
	}

	/**
	 * Converts all entries in the $LOCAL_LANG array to utf-8
	 *
	 * @param	array		LOCAL_LANG array with mixed charset
	 * @return	array		LOCAL_LANG array, all languages in UTF-8
	 */
	function convertLLarrayToUTF8($LOCAL_LANG)	{
		$languages = explode('|', TYPO3_languages);

		foreach($languages as $langKey)	{

				// Init $charset
			$charset = $GLOBALS['LANG']->csConvObj->charSetArray[$langKey];
			if (!$charset)	$charset = 'iso-8859-1';
			$charset = $GLOBALS['LANG']->csConvObj->parse_charset($charset);

				// Traverse single language for conversion:
			if ($charset!='utf-8' && is_array($LOCAL_LANG[$langKey]))	{
				foreach($LOCAL_LANG[$langKey] as $labelKey => $labelValue)	{
					$LOCAL_LANG[$langKey][$labelKey] = $GLOBALS['LANG']->csConvObj->utf8_encode($labelValue, $charset);
				}
			}
		}

		return $LOCAL_LANG;
	}

	/**
	 * Returns localized fileRef (.[langkey].php)
	 *
	 * @param	string		Filename/path of a 'locallang.php' file
	 * @param	string		Language key
	 * @return	string		Input filename with a '.[lang-key].php' ending added if $this->lang is not 'default'
	 */
	function localizedFileRef($fileRef,$lang)	{
		if ($lang!='default' && substr($fileRef,-4)=='.php')	{
			return substr($fileRef,0,-4).'.'.$lang.'.php';
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_ll2xml.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_ll2xml.php']);
}
?>