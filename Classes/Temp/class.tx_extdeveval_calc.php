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
 * Contains a class, tx_extdeveval_calc, which can do various handy calculations
 *
 * $Id: class.tx_extdeveval_calc.php 63721 2012-06-22 14:12:37Z ohader $
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   59: class tx_extdeveval_calc
 *   71:     function main()
 *
 *              SECTION: Tools functions:
 *  121:     function calc_unixTime()
 *  158:     function calc_crypt()
 *  182:     function calc_md5()
 *  205:     function calc_diff()
 *  297:     function calc_sql()
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Class for calculations
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_calc {

		// Internal GPvars:
	var $cmd;				// Command array
	var $inputCalc;			// Data array


	/**
	 * Main function, launching the calculator.
	 *
	 * @return	string		HTML content for the module.
	 */
	function main()	{

			// Set GPvar:
		$this->cmd = @key(t3lib_div::_GP('cmd'));
		$this->inputCalc = t3lib_div::_GP('inputCalc');

			// Call calculators:
		switch($this->cmd)	{
			case 'unixTime_toTime':
			case 'unixTime_toSeconds':
				$content = $this->calc_unixTime();
			break;
			case 'crypt':
				$content = $this->calc_crypt();
			break;
			case 'md5':
				$content = $this->calc_md5();
			break;
			case 'diff':
				$content = $this->calc_diff();
			break;
			case 'sql':
				$content = $this->calc_sql();
			break;
			case 'codelistclean':
				$content = $this->calc_codelistclean();
			break;
			case 'wiki2llxml':
				$content = $this->calc_wiki2llxml();
			break;
			case 'softref':
				$content = $this->calc_softref();
			break;
			default:
				$content = $this->calc_unixTime();
				$content.=$this->calc_crypt();
				$content.=$this->calc_md5();
				$content.=$this->calc_diff();
				$content.=$this->calc_sql();
				$content.=$this->calc_codelistclean();
				$content.=$this->calc_wiki2llxml();
				$content.=$this->calc_softref();
			break;
		}

			// Return content:
		return $content;
	}



	/*************************
	 *
	 * Tools functions:
	 *
	 *************************/

	/**
	 * Converting from human-readable time to unix time and vice versa
	 *
	 * @return	string		HTML content
	 */
	function calc_unixTime()	{

			// Processing incoming command:
		if ($this->cmd=='unixTime_toTime')	{
			$this->inputCalc['unixTime']['seconds'] = intval($this->inputCalc['unixTime']['seconds']);
		} elseif ($this->cmd=='unixTime_toSeconds')	{
			$timeParts=array();
			preg_match(
				'#(\d+)\s*-\s*(\d+)\s*-\s*(\d+)\s*(\d*):?(\d*):?(\d*)#',
				trim($this->inputCalc['unixTime']['time']),
				$timeParts
			);
			$timeParts = array_map('intval', $timeParts);
			$this->inputCalc['unixTime']['seconds'] = gmmktime($timeParts[4],$timeParts[5],$timeParts[6],$timeParts[2],$timeParts[1],$timeParts[3]);
		} else {
			$this->inputCalc['unixTime']['seconds'] = time();
		}

			// Render input form:
		$content = '
			<h3>Time:</h3>
			<p>Input UNIX time seconds (all values GMT):</p>
				<input type="text" name="inputCalc[unixTime][seconds]" value="'.htmlspecialchars($this->inputCalc['unixTime']['seconds']).'" size="30" style="'.($this->cmd=='unixTime_toSeconds' ? 'color: red;' :'').'" />
				<input type="submit" name="cmd[unixTime_toTime]" value="'.htmlspecialchars('>>').'" />
				<input type="submit" name="cmd[unixTime_toSeconds]" value="'.htmlspecialchars('<<').'" />
				<input type="text" name="inputCalc[unixTime][time]" value="'.htmlspecialchars(gmdate('d-m-Y H:i:s',$this->inputCalc['unixTime']['seconds'])).'" size="30" style="'.($this->cmd=='unixTime_toTime' ? 'color: red;' :'').'" /> (d-m-Y H:i:s)
		';

			// Check if the input time was different:
		if (t3lib_div::isFirstPartOfStr($this->cmd,'unixTime') && $this->inputCalc['unixTime']['time'] && gmdate('d-m-Y H:i:s',$this->inputCalc['unixTime']['seconds']) != trim($this->inputCalc['unixTime']['time']))	{
			$content.='<p><strong>Notice: </strong>The input time string was reformatted during clean-up! Please check it!</p>';
		}

			// Output:
		return $content;
	}

	/**
	 * Converting input string with "crypt()" - for maing htaccess passwords.
	 *
	 * @return	string		HTML content
	 */
	function calc_crypt()	{

			// Render input form:
		$content = '
			<h3>Input string to crypt:</h3>
			<p>Useful for making passwords for .htaccess files.</p>
				<input type="text" name="inputCalc[crypt][input]" value="'.htmlspecialchars($this->inputCalc['crypt']['input']).'" size="50" />
				<input type="submit" name="cmd[crypt]" value="Crypt" />
		';
		if ($this->cmd=='crypt' && trim($this->inputCalc['crypt']['input']))	{
			$content.='
			<p>Crypted string:</p>
			<input type="text" name="-" value="'.htmlspecialchars(crypt($this->inputCalc['crypt']['input'])).'" size="50" />
			';
		}

		return $content;
	}

	/**
	 * Creating MD5 hash of input content.
	 *
	 * @return	string		HTML content
	 */
	function calc_md5()	{

			// Render input form:
		$content = '
			<h3>Input string to MD5 process:</h3>
				<textarea rows="10" name="inputCalc[md5][input]" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->inputCalc['md5']['input']).
				'</textarea>
				<input type="submit" name="cmd[md5]" value="MD5 process" />
		';
		if ($this->cmd=='md5' && trim($this->inputCalc['md5']['input']))	{
			$content.='
			<p>MD5 hash: <strong>'.md5($this->inputCalc['md5']['input']).'</strong></p>';
		}

		return $content;
	}

	/**
	 * Shows a diff between the two input text strings.
	 *
	 * @return	string		HTML content
	 */
	function calc_diff()	{

			// Render input form:
		$content = '
			<h3>Diff\'ing strings:</h3>
			<p>"Old" string (red):</p>
				<textarea rows="10" name="inputCalc[diff][input1]" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->inputCalc['diff']['input1']).
				'</textarea>
				'.($this->inputCalc['diff']['input1']?'<p>MD5 hash: <strong>'.md5($this->inputCalc['diff']['input1']).'</strong></p>':'').'
			<p>"New" string (green):</p>
				<textarea rows="10" name="inputCalc[diff][input2]" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->inputCalc['diff']['input2']).
				'</textarea>
				'.($this->inputCalc['diff']['input2']?'<p>MD5 hash: <strong>'.md5($this->inputCalc['diff']['input2']).'</strong></p>':'').'
				<br />
				<input type="submit" name="cmd[diff]" value="Make diff" /><br />

				<input type="radio" name="inputCalc[diff][diffmode]" value="0"'.(!$this->inputCalc['diff']['diffmode']?' checked="checked"':'').' /> Classic diff (line by line)<br />
				<input type="radio" name="inputCalc[diff][diffmode]" value="1"'.($this->inputCalc['diff']['diffmode']==1?' checked="checked"':'').' /> Unified output, 3 lines<br />
				<input type="radio" name="inputCalc[diff][diffmode]" value="2"'.($this->inputCalc['diff']['diffmode']==2?' checked="checked"':'').' /> Diff word by word<br />
		';
		if ($this->cmd=='diff' && trim($this->inputCalc['diff']['input1']) && trim($this->inputCalc['diff']['input2']))	{
			if (strcmp($this->inputCalc['diff']['input1'],$this->inputCalc['diff']['input2']))	{
				require_once(PATH_t3lib.'class.t3lib_diff.php');

				$diffEngine = t3lib_div::makeInstance('t3lib_diff');
				switch($this->inputCalc['diff']['diffmode'])	{
					case 1:	// Unified
						$diffEngine->diffOptions = '--unified=3 --ignore-all-space';

						$resultA = $diffEngine->getDiff($this->inputCalc['diff']['input1'],$this->inputCalc['diff']['input2']);
						$result='';
						foreach($resultA as $line)	{
							if (substr($line,0,3)!='---' && substr($line,0,3)!='+++')	{
								switch(substr($line,0,1))	{
									case '+':	// New
										$result.='<span class="diff-g">'.htmlspecialchars($line).'</span>';
									break;
									case '-':	// Old
										$result.='<span class="diff-r">'.htmlspecialchars($line).'</span>';
									break;
									default:
										$result.=htmlspecialchars($line);
									break;
								}
								$result.=chr(10);
							}
						}
					break;
					case 2:	// Word by word
						$result = $diffEngine->makeDiffDisplay($this->inputCalc['diff']['input1'],$this->inputCalc['diff']['input2']);
					break;
					default:
						$diffEngine->diffOptions = '--ignore-all-space';
						$resultA = $diffEngine->getDiff($this->inputCalc['diff']['input1'],$this->inputCalc['diff']['input2']);
						$result='';
						foreach($resultA as $line)	{
							switch(substr($line,0,1))	{
								case '>':	// New
									$result.='<span class="diff-g">'.htmlspecialchars($line).'</span>';
								break;
								case '<':	// Old
									$result.='<span class="diff-r">'.htmlspecialchars($line).'</span>';
								break;
								default:
									$result.=htmlspecialchars($line);
								break;
							}
							$result.=chr(10);
						}
					break;
				}
				$content.='
					<hr />
					<pre>'.$result.'</pre>
					<hr />
				';
			} else {
				$content.='
					<p><strong>The two test strings are exactly the same!</strong></p>
				';
			}
		}

		return $content;
	}

	/**
	 * Parsing input SQL with t3lib_sqlengine
	 *
	 * @return	string		SQL content
	 */
	function calc_sql()	{

			// Render input form:
		$content = '
			<h3>Input SQL string:</h3>
				<textarea rows="10" name="inputCalc[sql][input]" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->inputCalc['sql']['input']).
				'</textarea>
				<input type="submit" name="cmd[sql]" value="Parse SQL" />
		';
		if ($this->cmd=='sql' && trim($this->inputCalc['sql']['input']))	{

				// Start SQL engine:
			require_once(PATH_t3lib.'class.t3lib_sqlparser.php');
			$sqlParser = t3lib_div::makeInstance('t3lib_sqlparser');

				// Parse query:
			$result = $sqlParser->parseSQL($this->inputCalc['sql']['input']);

				// If success (array is returned), recompile/show:
			if (is_array($result))	{

					// TEsting if query can be re-compiled and will match original:
				$recompiledSQL = $sqlParser->compileSQL($result);
				if ($parts = $sqlParser->debug_parseSQLpartCompare($this->inputCalc['sql']['input'],$recompiledSQL))	{
					if ($parts = $sqlParser->debug_parseSQLpartCompare($this->inputCalc['sql']['input'],$recompiledSQL,TRUE))	{
						$content.= '<p><strong>Error:</strong> Re-compiled query did not match original!</p>'.Tx_Extdeveval_Compatibility::viewArray($parts);
					} else {
						$content.= '<p><strong>CASE Error:</strong> Re-compiled OK insensitive to character case, BUT did not match original without case equalization!</p>'.Tx_Extdeveval_Compatibility::viewArray($parts);
					}
				} else {
					$content.= '<p><strong>OK: </strong> Re-compiled query OK</p>';
				}
				$content.= '<hr />';

				$content.= Tx_Extdeveval_Compatibility::viewArray($result);
			} else {
				$content.= '<p>'.$result.'</p>';
			}
		}

		return $content;
	}

	/**
	 * Cleaning PHP code listing with linenumbers prefixed.
	 *
	 * @return	string		HTML content
	 */
	function calc_codelistclean()	{

			// Render input form:
		$content = '
			<h3>Input PHP code to clean for prefixed linenumbers and hard spaces:</h3>
				<textarea rows="10" name="inputCalc[codelistclean][input]" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->inputCalc['codelistclean']['input']).
				'</textarea>
				<input type="submit" name="cmd[codelistclean]" value="Clean" />
		';
		if ($this->cmd=='codelistclean' && trim($this->inputCalc['codelistclean']['input']))	{
			$inputValue = $this->inputCalc['codelistclean']['input'];
				// Clean value:
			$inputValue = str_replace(chr(160),chr(32),$inputValue);	// Clean hard-spaces.
			$inputValue = str_replace(chr(13),'',$inputValue);	// Remove char-13
			$inputValue = preg_replace('#' . chr(10) . '[ ]*[0-9]+: #', chr(10), chr(10) . $inputValue);	// Remove number prefix
			$inputValue = str_replace('    ',chr(9),$inputValue);	// 4 spaces to a tab
			$inputValue = trim($inputValue);

			$content.='
			<textarea rows="10" name="_" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($inputValue).
				'</textarea>';
		}

		return $content;
	}

	/**
	 * Testing soft reference parsers on content
	 *
	 * @return	string		HTML content
	 */
	function calc_softref()	{

			// Render input form:
		$content = '
			<h3>Input values to run soft reference parsers on:</h3>' .
					'Input the content to parse:<br/>
				<textarea rows="10" name="inputCalc[softref][input]" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->inputCalc['softref']['input']).
				'</textarea>' .
				'<br/>Input parser keys to test: <br/>
				<input name="inputCalc[softref][parsers]" value="'.htmlspecialchars($this->inputCalc['softref']['parsers'] ? $this->inputCalc['softref']['parsers'] : implode(',',array_keys($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['GLOBAL']['softRefParser']))).'" type="text" /><br/>
				<input type="submit" name="cmd[softref]" value="Test" />
		';
		if ($this->cmd=='softref' && trim($this->inputCalc['softref']['input']))	{
			$value = $this->inputCalc['softref']['input'];

			$table = '_TABLE';
			$field = '_FIELD_';
			$uid = '_UID_';

				// Soft References:
			if (strlen($value) && $softRefs = t3lib_BEfunc::explodeSoftRefParserList($this->inputCalc['softref']['parsers']))	{
				$softRefValue = $value;
				foreach($softRefs as $spKey => $spParams)	{
					$softRefObj = &t3lib_BEfunc::softRefParserObj($spKey);
					if (is_object($softRefObj))	{
						$resultArray = $softRefObj->findRef($table, $field, $uid, $softRefValue, $spKey, $spParams);
						if (is_array($resultArray))	{
							$outRow[$field]['softrefs']['keys'][$spKey] = $resultArray['elements'];
							if (strlen($resultArray['content'])) {
								$softRefValue = $resultArray['content'];
							}
						}
					}
				}

				if (is_array($outRow[$field]['softrefs']) && count($outRow[$field]['softrefs']) && strcmp($value,$softRefValue) && strstr($softRefValue,'{softref:'))	{
					$outRow[$field]['softrefs']['tokenizedContent'] = $softRefValue;
				}
			}

			$content.=Tx_Extdeveval_Compatibility::viewArray($outRow);
		}

		return $content;
	}

	/**
	 * Creating TYPO3 Glossary XML file
	 *
	 * @return	string		HTML content
	 */
	function calc_wiki2llxml()	{

			// Render input form:
		$content = '
			<h3>Input Wiki code for TYPO3 glossary to form a locallang-XML file out of it:</h3>
				<textarea rows="10" name="inputCalc[wiki2llxml][input]" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->inputCalc['wiki2llxml']['input']).
				'</textarea>
				<input type="submit" name="cmd[wiki2llxml]" value="Convert" />
		';
		if ($this->cmd=='wiki2llxml' && trim($this->inputCalc['wiki2llxml']['input']))	{
			$inputValue = $this->inputCalc['wiki2llxml']['input'];

				// Clean out super-headers:
			$inputValue = preg_replace('#([^=])===[[:space:]]*[[:alnum:]]*[[:space:]]*===([^=])#', '${1}${2}', $inputValue);


				// Split by term header:
			$rawTerms = explode('=====',$inputValue);
			$organizedTerms = array();
			$termKey='';

			foreach($rawTerms as $k => $v)	{
				if ($k%2==0)	{
					if ($termKey)	{
						$tK = $this->calc_wiki2llxml_termkey($termKey);

						if ($tK)	{
							$organizedTerms[$tK] = array();

							$rawdata = trim($v);
							list($description,$moreInfo) = explode("''More info:''",$rawdata,2);
							if ($moreInfo)	{
								list($moreInfo,$otherTerms) = explode("''Other matching terms:''",$moreInfo,2);
							} else {
								list($description,$otherTerms) = explode("''Other matching terms:''",$description,2);
							}

							$description = trim(strip_tags($description));
							$moreInfo = trim(strip_tags($moreInfo));
							$otherTerms = trim(strip_tags($otherTerms));

							$organizedTerms[$tK] = array(
								'term' => $termKey,
								'RAWDATA' => trim($rawdata),
								'description' => $description,
								'moreInfo' => $moreInfo,
								'otherTerms' => $otherTerms
							);
						}
					}
				} else {
					$termKey = trim($v);
				}
			}

				// Traverse terms to clean up moreInfo and otherTerms:
			foreach($organizedTerms as $key => $termData)	{

					// Other Terms fixing.
				$oT = t3lib_div::trimExplode(',',$termData['otherTerms'],1);
				$organizedTerms[$key]['otherTerms'] = array();

				foreach($oT as $t)	{
					$tK = $this->calc_wiki2llxml_termkey($t);
					if (isset($organizedTerms[$tK]))	{
						$organizedTerms[$key]['otherTerms'][$tK] = array('type' => 'existing');
					} elseif ($tK) {
						$organizedTerms[$tK] = array(
							'RAWDATA' => '[alias for "'.$key.'"]',
							'term' => $t,
							'description' => 'See "'.$organizedTerms[$key]['term'].'"',
							'otherTerms' => array($key=>array('type'=>'existing'))
						);

						$organizedTerms[$key]['otherTerms'][$tK] = array('type' => 'alias');
					}
				}

					// More information splitted:
				$termData['moreInfo'] = preg_replace('#\[\[([^]]+)\]\]#', '[http://wiki.typo3.org/index.php/${1}]', $termData['moreInfo']);
				$parts = preg_split('#(\[)([^\]]+)(\])#', $termData['moreInfo'], 10000, PREG_SPLIT_DELIM_CAPTURE);

				$organizedTerms[$key]['moreInfo'] = array();
				foreach($parts as $kk => $vv)	{
					if ($kk%2==0)	{
						$link = trim($vv);
						if ($link && $link!=',')	{
							list($link, $title) = preg_split('#[ |]#', $link, 2);
							$organizedTerms[$key]['moreInfo'][] = array('url' => $link, 'title' => $title);
						}
					}
				}

					// Removal of the RAW data which is not so interesting for us:
				unset($organizedTerms[$key]['RAWDATA']);
			}

			$content.= Tx_Extdeveval_Compatibility::viewArray($organizedTerms);

			ksort($organizedTerms);

			$content.='
			<textarea rows="10" name="_" wrap="off"'.$GLOBALS['TBE_TEMPLATE']->formWidthText(48,'width:98%;','off').'>'.
				t3lib_div::formatForTextarea($this->calc_wiki2llxml_createXML($organizedTerms)).
				'</textarea>';
		}

		return $content;
	}

	/**
	 * Converts a term into a key for that term.
	 *
	 * @param	string		Input string
	 * @return	string		Output string
	 */
	function calc_wiki2llxml_termkey($string)	{
		return preg_replace('#[^[:alnum:]_-]*#', '', str_replace(' ', '_', strtolower(trim($string))));
	}

	function calc_wiki2llxml_createXML($termArray)	{

		$xmlCode = '';

			// Being script:
		$xmlCode.= '
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<T3locallang>
	<meta type="array">
		<description>TYPO3 glossary</description>
		<type>CSH</type>
		<fileId>EXT:t3glossary/locallang_csh_glossary_t3.xml</fileId>
		<csh_table>xGLOSSARY_t3</csh_table>
		<keep_original_text>1</keep_original_text>
		<ext_filename_template>EXT:csh_###LANGKEY###/t3glossary/###LANGKEY###.locallang_csh_glossary_t3.xml</ext_filename_template>
		<labelContext type="array">
		</labelContext>
	</meta>
	<data type="array">
		<languageKey index="default" type="array">
			<label index=".alttitle">TYPO3 glossary</label>
			<label index=".description">Glossary of TYPO3 related terms.</label>';

			foreach($termArray as $tK => $tValue)	{

					// Initialize:
				$seeAlso = array();

					// Other matching terms:
				if (is_array($tValue['otherTerms']) && count($tValue['otherTerms']))	{
					foreach($tValue['otherTerms'] as $otK => $otV)	{
						if ($otV['type']!='alias')	{
							$seeAlso[] = 'xGLOSSARY_t3:'.$otK;
						}
					}
				}

					// More information
				if (is_array($tValue['moreInfo']) && count($tValue['moreInfo']))	{
					foreach($tValue['moreInfo'] as $mI)	{
						if ($mI['url'])	{
							$seeAlso[] = $mI['url']. ($mI['title'] ? '|'.$mI['title'] : '');
						}
					}
				}


					// Compile:
				$xmlCode.='
			<label index="'.$tK.'.alttitle">'.htmlspecialchars($tValue['term']).'</label>
			<label index="'.$tK.'.description">'.htmlspecialchars($tValue['description']).'</label>';

				if (count($seeAlso))	{
					$xmlCode.='
			<label index="_'.$tK.'.seeAlso">'.htmlspecialchars(implode(', ',$seeAlso)).'</label>';
				}
			}

				// End script:
			$xmlCode.='
		</languageKey>
		<languageKey index="dk">EXT:csh_dk/t3glossary/dk.locallang_csh_glossary_t3.xml</languageKey>
	</data>
</T3locallang>
		';

		return trim($xmlCode);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_calc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_calc.php']);
}
?>