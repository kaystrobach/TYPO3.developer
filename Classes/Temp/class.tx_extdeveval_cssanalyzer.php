<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Kasper Skårhøj (kasper@typo3.com)
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
 * Contains a class, tx_extdeveval_cssanalyzer, which can display the hierarchy of CSS selectors in HTML source code.
 *
 * $Id: class.tx_extdeveval_cssanalyzer.php 653 2004-06-24 09:48:11Z kasper $
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   54: class tx_extdeveval_cssanalyzer
 *   71:     function main()
 *  159:     function getHierarchy($HTMLcontent,$count=20,$selPrefix='')
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
require_once(PATH_t3lib.'class.t3lib_parsehtml.php');


/**
 * Class for displaying the hierarchy of CSS selectors in HTML source code
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_cssanalyzer {

		// GPvars:
	var $inputHTML = '';			// Input HTML code to analyze
	var $removePrefix = '';			// Prefix to remove from shown selectors
	var $useLimit = '';				// Default limit - the value of the limit field

		// Internal, dynamic:
	var $foundSelectors = array();
	var $contentIndex = array();


	/**
	 * Main function, branching out to rendering functions
	 *
	 * @return	string		HTML content for the module.
	 */
	function main()	{

			// Set GPvar:
		$this->inputHTML = t3lib_div::_GP('inputHTML');
		$this->removePrefix = trim(t3lib_div::_GP('rmPre'));
		$this->useLimit = t3lib_div::_GP('uselimit');

			// Render input form:
		$content.='
			<p>Input HTML source here:</p>

			<textarea rows="15" name="inputHTML" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->inputHTML).
			'</textarea>

			<br />
			<p>Enter selector prefix to remove: </p>
				<input type="text" name="rmPre" value="'.htmlspecialchars($this->removePrefix).'" size="50" />
			<p>Enter default filter prefix (or click a selector in table below): </p>
				<input type="text" name="uselimit" value="'.htmlspecialchars($this->useLimit).'" size="50" />

			<br />
			<input type="submit" name="_submit" value="Analyze" />
		';


			// Parse input content:
		if ($this->inputHTML)	{
			$this->parseHTML = t3lib_div::makeInstance('t3lib_parsehtml');
			$bodyParts = $this->parseHTML->splitIntoBlock('body',$this->inputHTML,1);
			list($analysedResult,$thisSelectors) = $this->getHierarchy($bodyParts[1]);
			$this->foundSelectors = array_keys(array_flip($this->foundSelectors));


			$rows=array();
			$textarea=array();
			foreach($this->foundSelectors as $v)	{
				if (!$this->useLimit || t3lib_div::isFirstPartOfStr($v,$this->useLimit))	{
					$v_orig=$v;
					if ($this->removePrefix)	{
						if (t3lib_div::isFirstPartOfStr(trim($v),$this->removePrefix))	{
							$v=trim(substr(trim($v),strlen($this->removePrefix)));
						}
					}
					if (trim($v))	{
						$rows[]='
							<tr class="bgColor4">
								<td nowrap="nowrap">'.htmlspecialchars($v).'</td>
								<td><a href="#" onclick="'.htmlspecialchars('document.forms[0].uselimit.value=unescape(\''.rawurlencode(trim($v_orig)).'\'); document.forms[0].submit(); return false;').'">[LIMIT]</a></td>
								<td><a href="#" onclick="'.htmlspecialchars('document.forms[0].rmPre.value=unescape(\''.rawurlencode(trim($v_orig)).'\'); document.forms[0].submit(); return false;').'">[REM. PREFIX]</a></td>
							</tr>';
						$textarea[]=trim($v).' {}';
					}
				}
			}

			$content.='<hr />

				<!--
					Listing of selectors (in table):
				-->
				<table border="0" cellpadding="2" cellspacing="2">
					'.implode(chr(10),$rows).'
				</table>
				<br />

				<!--
					Listing of selectors (in textarea field):
				-->
				<textarea rows="'.t3lib_div::intInRange(count($textarea)+2,5,30).'" name="" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
					t3lib_div::formatForTextarea(implode(chr(10),$textarea)).'
				</textarea>
				<hr />
				';
		}

			// Return content:
		return $content;
	}

	/**
	 * Creates hierarchy of CSS selectors from input HTML content:
	 *
	 * @param	string		HTML body content
	 * @param	integer		Max recursions
	 * @param	string		Current selector prefix
	 * @return	array		Array with information about the found selectors.
	 */
	function getHierarchy($HTMLcontent,$count=20,$selPrefix='')	{
		$parts = $this->parseHTML->splitIntoBlock('a,b,blockquote,body,div,em,font,form,h1,h2,h3,h4,h5,h6,i,li,ol,option,p,pre,select,span,strong,table,td,textarea,tr,u,ul,iframe',$HTMLcontent,1);

		$thisSelectors=array();
		$exampleContentAccum=array();

		reset($parts);
		while(list($k,$v)=each($parts))	{
			if ($k%2 && $count)	{
				$firstTag = $this->parseHTML->getFirstTag($v);
				$firstTagName = $this->parseHTML->getFirstTagName($v);
				$attribs = $this->parseHTML->get_tag_attributes_classic($firstTag,1);
				$thisSel =$selPrefix.' '.$firstTagName;
				if ($attribs['class'])	{
					$this->foundSelectors[]=trim($thisSel.'.'.$attribs['class']);
				}
				if ($attribs['id'])	{
					$this->foundSelectors[]=trim($thisSel.'#'.$attribs['id']);
				}
				if ($attribs['class'])	{
					$thisSel.= '.'.$attribs['class'];
				} elseif ($attribs['id'])	{
					$thisSel.= '#'.$attribs['id'];
				} else {
					$this->foundSelectors[]=trim($thisSel);
				}

				$v = $this->parseHTML->removeFirstAndLastTag($v);
				$pC = $this->getHierarchy($v,$count-1,$thisSel);
				$hash = md5(serialize($pC[1]));
				if (!isset($exampleContentAccum[$hash]))		$exampleContentAccum[$hash]=$v;

				$parts[$k]=array(
					'tag' => $firstTag,
					'tagName' => $firstTagName,
					'thisSel' => $thisSel,
					'subContent' => $pC[0],
					'accum_selectors' => $pC[1],
					'accum_selectors_hash' => $hash,
					'example_content' => $pC[2]
				);

				$thisSelectors=array_merge($thisSelectors,$pC[1]);
				$thisSelectors[]=$thisSel;
			} else {
				$parts[$k]=array();

				$singleParts = $this->parseHTML->splitTags('img,input,hr',$v);
				reset($singleParts);
				while(list($kk,$vv)=each($singleParts))	{
					if ($kk%2)	{
						$firstTag = $this->parseHTML->getFirstTag($vv);
						$firstTagName = $this->parseHTML->getFirstTagName($vv);
						$attribs = $this->parseHTML->get_tag_attributes_classic($firstTag,1);

						$thisSel =$selPrefix.' '.$firstTagName;
						if ($attribs['class'])	{
							$this->foundSelectors[]=trim($thisSel.'.'.$attribs['class']);
						}
						if ($attribs['id'])	{
							$this->foundSelectors[]=trim($thisSel.'#'.$attribs['id']);
						}
						if (!$attribs['class'] && !$attribs['id']) {
							$this->foundSelectors[]=trim($thisSel);
						}
						$parts[$k][$kk]=array(
							'tag' => $firstTag,
							'tagName' => $firstTagName,
							'thisSel' => $thisSel,
						);

						$thisSelectors[]=$thisSel;
					}
					$parts[$k][$kk]['content']=$vv;
				}
			}
		}

		asort($thisSelectors);
		$thisSelectors = array_unique($thisSelectors);

		return array($parts,$thisSelectors,implode('',$exampleContentAccum));
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_cssanalyzer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_cssanalyzer.php']);
}
?>