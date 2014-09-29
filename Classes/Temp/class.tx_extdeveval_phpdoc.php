<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Kasper Sk�rh�j (kasper@typo3.com)
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
 * Contains a class, tx_extdeveval_phpdoc, which can parse JavaDoc comments in PHP scripts, insert new, create a data-file for a display-plugin that exists as well.
 *
 * $Id: class.tx_extdeveval_phpdoc.php 63721 2012-06-22 14:12:37Z ohader $
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   65: class tx_extdeveval_phpdoc
 *   89:     function analyseFile($filepath,$extDir,$includeCodeAbstract=1)
 *  290:     function updateDat($extDir,$extPhpFiles,$passOn_extDir)
 *  462:     function generateComment($cDat,$commentLinesWhiteSpacePrefix,$isClass)
 *  525:     function tryToMakeParamTagsFromFunctionDefLine($v)
 *  549:     function parseFunctionComment($content,$arr)
 *  601:     function getWhiteSpacePrefix($string)
 *  614:     function isHeaderClass($string)
 *  627:     function splitHeader($inStr)
 *  692:     function includeContent($content, $class)
 *  713:     function getSectionDivisionComment($string)
 *  733:     function checkCommentQuality($datArray,$class=0)
 *  788:     function checkParameterComment($var,$label,&$messages,&$severity,$return=FALSE)
 *  812:     function countFunctionUsage($functionHeader, $extPhpFiles, $extDir)
 *  887:     function searchFile($splitString, $fileName, $extDir)
 *
 * TOTAL FUNCTIONS: 14
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Class for the PHP-doc functions.
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_phpdoc {

		// External, Static:
	var $includeContent=500;			// The number of bytes of a functions' code to include for the API
	var $argCommentLen=7;				// The number of chars which an argument comment should exceed in order to be accepted as sufficient
	var $funcCommentLen=20;				// The number of chars which a function/class comment should exceed in order to be accepted as sufficient
	var $varTypeList = 'string,integer,double,boolean,array,object,mixed,pointer,void';		// List of variable type values accepted for argument comments

		// Internal, dynamic:
	var $fileInfo=array();				// Used during the parsing of a file.
	var $sectionTextCounter=0;			// Counting when sections are found.
	var $classCounter=0;				// Counting for classes
	var $colorCount=array();			// Counting functions of "black", "navy" and "red" types

	var $searchFile_fileCache=array();	// Internal caching of files contents during searching for function names.

	/**
	 * The main function in the class
	 *
	 * @param	string		The absolute path to an existing PHP file which should be analysed
	 * @param	string		The local/global/system extension main directory relative to PATH_site - normally set to "typo3conf/ext/" for local extensions
	 * @param	boolean		If true, an abstract of the source code of the functions will be included (approx. 500 bytes per function)
	 * @return	string		HTML content from the function
	 */
	function analyseFile($filepath,$extDir,$includeCodeAbstract=1)	{
		$wsreg = "[\t ]*\r?\n";
		#$wsreg = "[\t\n\r ]";

			// Getting the content from the phpfile.
		$content = t3lib_div::getUrl($filepath);
		$hash_current = md5($content);
		$hash_current_noWhiteSpace = md5(preg_replace('#' . $wsreg . '#', '', $content));

			// Splitting the file based on a regex:
			// NOTICE: "\{" (escaping a curly brace) should NOT be done when it is in [] - thus below it should be "[^{]" and not "[^\{]" - the last will also find backslash characters in addition to curly braces. But curly braces outside of [] seems to need this.
		$splitRegEx = chr(10).'['.chr(13).chr(9).chr(32).']*('.
				'((private|public|protected)[[:space:]]+)?(static[[:space:]]+)?function[[:space:]]+[&]?[[:alnum:]_]+[[:space:]]*\([^{]*'.	// Finding functions...
				'|'.
				'class[[:space:]]+[[:alnum:]_]+[^{]*'.			// Finding classes.
				')\{['.chr(13).chr(9).chr(32).']*'.chr(10);
		$parts = preg_split('#' . $splitRegEx . '#', $content);

			// Traversing the splitted array and putting the pieces into a new array, $fileParts, where the cut-out part is also added.
		$fileParts=array();
		$lenCount=0;
		foreach($parts as $k => $v)	{
			if ($k)	{
					// Find the part that the regex matched (which is NOT in the parts array):
				$reg = '';
				preg_match('#^' . $splitRegEx . '#', substr($content, $lenCount), $reg);
				$fileParts[]=$reg[0];
				$lenCount+=strlen($reg[0]);
			}
				// ... Then add the value from the parts-array:
			$fileParts[]=$v;
			$lenCount+=strlen($v);
		}

			// Finally, if the processing into the $fileParts array was successful the imploded version of this array will match the input $content. So we do this integrity check here:
		if (md5(implode('',$fileParts)) == md5($content))	{

				// Remove trailing space and "13" chars:
			foreach($fileParts as $partKey => $partValue)	{
				$partLines = explode(chr(10), str_replace(chr(13),'',$partValue));
				foreach($partLines as $lineNum => $lineValue)	{
					$partLines[$lineNum] = rtrim($lineValue);
				}
				$fileParts[$partKey] = implode(chr(10), $partLines);
			}

				// Traversing the array, trying to find
			$visualParts=array();
			$currentClass='';
			$this->sectionTextCounter=0;
			$this->classCounter=0;
			foreach($fileParts as $k => $v)		{
				$visualParts[$k]=htmlspecialchars($v);

				if ($k%2)	{
					$this->fileInfo[$k]['header']=trim($v);
					$isClassName = $this->isHeaderClass($v);
					if ($isClassName)	{
						$this->fileInfo[$k]['class']=1;
						$this->classCounter++;
						$currentClass=$isClassName;
					}
					$this->fileInfo[$k]['parentClass']=$currentClass;

						// Try to locate existing comment:
					$SET=0;
					$cDat=array();
					$comment = t3lib_div::revExplode('**/',$fileParts[$k-1],2);
					if (trim($comment[1]) && preg_match('#\*\/$#', trim($comment[1])))	{
						$SET=1;

							// There was a comment! Now, parse it.
						if ($k>1)	{
							$sectionText = $this->getSectionDivisionComment($comment[0]);
							if (is_array($sectionText))	{
								$this->sectionTextCounter++;
								$this->fileInfo[$k]['sectionText']=$sectionText;
							}
						}

						$blankCDat = $this->tryToMakeParamTagsFromFunctionDefLine($v);
						$cDat = $this->parseFunctionComment($comment[1],$blankCDat);
						$this->fileInfo[$k]['cDat']=$cDat;
					} else {
						$comment = t3lib_div::revExplode('}',$fileParts[$k-1],2);

						if (isset($comment[1]) && !trim($comment[1]))	{
							$SET=2;
							$comment[0].='}'.chr(10).chr(10).'	';
						} else {
							$comment = t3lib_div::revExplode('{',$fileParts[$k-1],2);
							if (isset($comment[1]) && !trim($comment[1]))	{
								$SET=2;
								$comment[0].='{'.chr(10).chr(10).'	';
							}
						}

						if ($SET==2)	{
							$cDat['text']='[Describe function...]';		// Notice, if this is ever changed, should be changes for analyser tool (see checkCommentQuality() ) as well.
							$cDat['param'] = $this->tryToMakeParamTagsFromFunctionDefLine($v);
							$cDat['return'] = array('[type]','...');
						}
					}

					if (!isset($fileParts[$k+2]))	{	// ... if this is last item!
						$this->fileInfo[$k]['content'] = $includeCodeAbstract ? $this->includeContent($fileParts[$k+1], $this->fileInfo[$k]['class']) : '';
						$this->fileInfo[$k]['content_size']=strlen($fileParts[$k+1]);
						$this->fileInfo[$k]['content_lines']=substr_count($fileParts[$k+1],chr(10));
					} elseif (isset($this->fileInfo[$k-2]))	{	// ... otherwise operate on the FORMER item!
						$this->fileInfo[$k-2]['content'] = $includeCodeAbstract ? $this->includeContent($comment[0], $this->fileInfo[$k-2]['class']) : '';
						$this->fileInfo[$k-2]['content_size']=strlen($comment[0]);
						$this->fileInfo[$k-2]['content_lines']=substr_count($comment[0],chr(10));
					}

					if ($SET)	{
						$commentLinesWhiteSpacePrefix = $this->getWhiteSpacePrefix($comment[0]);
						$comment[1]=$this->generateComment($cDat,$commentLinesWhiteSpacePrefix,$this->isHeaderClass($v));

						$origPart = $fileParts[$k-1];
						$fileParts[$k-1]=implode('',$comment);

							// If there was a change, then make a markup of the visual output:
						$vComment = $comment;
						$vComment[0]=htmlspecialchars($vComment[0]);
						if ($k>1)	{
							if (strlen($vComment[0])>1000)	{
								$vComment[0] = substr($vComment[0],0,450).chr(10).'<span style="color:green; font-weight:bold;">[...]</span>'.chr(10).substr($vComment[0],-500);
							}
						}

						$color = ($origPart==$fileParts[$k-1] ? 'black' :($SET==1?'navy':'red'));
						$this->colorCount[$color]++;

						$vComment[1]='<span style="color:'.$color.'; font-weight:bold;">'.htmlspecialchars($vComment[1]).'</span>';
						$visualParts[$k-1]=implode('',$vComment);
					}
				}
			}

				// Count lines:
			$lines=0;
			foreach($fileParts as $k => $v)		{
				if ($k%2)	{
					$this->fileInfo[$k]['atLine']=$lines;
				}
				$lines+=substr_count($fileParts[$k],chr(10));
			}

			$fileParts[0] = $this->splitHeader($fileParts[0]);
			$visualParts[0] = '<span style="color:#663300;">'.htmlspecialchars($fileParts[0]).'</span>';


			$output='';
			$output.='<b>Color count:</b><br />"red"=new comments<br />"navy"=existing, modified<br />"black"=existing, not modified'.Tx_Extdeveval_Compatibility::viewArray($this->colorCount);

				// Output the file
			if (t3lib_div::_GP('_save_script'))	{
				if (@is_file($filepath) && t3lib_div::isFirstPartOfStr($filepath,PATH_site.$extDir))	{
					$output.='<b>SAVED TO: '.substr($filepath,strlen(PATH_site)).'</b>';
					t3lib_div::writeFile($filepath,implode('',$fileParts));
				} else {
					$output.='<b>NO FILE/NO PERMISSION!!!: '.substr($filepath,strlen(PATH_site)).'</b>';
				}
				$output.='<hr />';
				$output.='<input type="submit" name="_" value="RETURN" />';
			} else {
				$hash_new = md5(implode('',$fileParts));
				$hash_new_noWhiteSpace = md5(preg_replace('#' . $wsreg . '#', '', implode('', $fileParts)));

				$output.='
				'.$hash_current.' - Current file HASH<br />
				'.$hash_new.' - New file HASH<br />
				'.($hash_current!=$hash_new && $hash_current_noWhiteSpace == $hash_new_noWhiteSpace ? '<em>(Difference only concerns whitespace!)</em><br /><br />' : '<br />').'
				(If the hash strings are similar you don\'t need to save since nothing would be changed)<br />
				';


				$output.='
				<b><br />This is the substititions that will be carried out if you press the "Save" button in the bottom of this page:</b><hr />';
				$output.='<input type="submit" name="_save_script" value="SAVE!" />';
				$output.= '<pre>'.str_replace(chr(9),'&nbsp;&nbsp;&nbsp;',implode('',$visualParts)).'</pre>';

				$output.='<hr />';
				$output.='<input type="submit" name="_save_script" value="SAVE!" />';
				$output.='<br /><br /><b>Instructions:</b><br />';
				$output.='0) Make a backup of the script - what if something goes wrong? Are you prepared?<br />';
				$output.='1) Press the button if you are OK with the changes. RED comments are totally new - BLUE comments are existing comments but parsed/reformatted.<br />';
			}

			return $output;
		} else return 'ERROR: There was an internal error in process of splitting the PHP-script.';
	}

	/**
	 * Creates an interface where there user can select which "class." files to include in the ext_php_api.dat file which the function can create/update by a single click.
	 *
	 * @param	string		$extDir: Extension Directory, absolute path
	 * @param	array		$extPhpFiles: Array with PHP files (rel. paths) from the extension directory
	 * @param	string		The local/global/system extension main directory relative to PATH_site - normally set to "typo3conf/ext/" for local extensions. Used to pass on to analyseFile()
	 * @return	string		HTML output
	 */
	function updateDat($extDir,$extPhpFiles,$passOn_extDir)	{
		if (is_array($extPhpFiles))	{

				// GPvars:
			$doWrite = t3lib_div::_GP('WRITE');
			$gp_options = t3lib_div::_GP('options');


				// Find current dat file:
			$datArray='';
			if (@is_file($extDir.'ext_php_api.dat'))	{
				$datArray = unserialize(t3lib_div::getUrl($extDir.'ext_php_api.dat'));
				if (!is_array($datArray))	{
					$content.='<br /><br /><p><strong>ERROR:</strong> "ext_php_api.dat" file did not contain a valid serialized array!</p>';
				} else {
					$content.='<br /><br /><p> "ext_php_api.dat" has been detected ('.t3lib_div::formatSize(filesize($extDir.'ext_php_api.dat')).'bytes) and read.</p>';
				}
			} else $content='<br /><br /><p><strong>INFO:</strong> No "ext_php_api.dat" file found.</p>';
			if (!is_array($datArray))	$datArray = array();

				// Show files:
			$newDatArray = array();
			$newDatArray['meta']['title'] = $datArray['meta']['title'];
			$newDatArray['meta']['descr'] = $datArray['meta']['descr'];
			$inCheck = t3lib_div::_GP('selectThisFile');

			$lines = array();
			foreach ($extPhpFiles as $lFile)	{

					// Make MD5 hash of filepath:
				$lFile_MD5 = 'MD5_'.t3lib_div::shortMD5($lFile);

					// disable check for "class." by "1"
				if (1 || t3lib_div::isFirstPartOfStr(basename($lFile),'class.'))	{

						// Get API information about class-file:
					$newAnalyser = t3lib_div::makeInstance('tx_extdeveval_phpdoc');
					$newAnalyser->analyseFile($extDir.$lFile,$passOn_extDir,$gp_options['includeCodeAbstract']?1:0);

					if ((!is_array($inCheck) && isset($datArray['files'][$lFile_MD5])) || (is_array($inCheck) && in_array($lFile,$inCheck)))	{
						$newDatArray['files'][$lFile_MD5]=array(
							'filename' => $lFile,
							'filesize'=>filesize($extDir.$lFile),
							'header'=>$newAnalyser->headerInfo,
							'DAT' => $newAnalyser->fileInfo
						);
					}

						// Format that information:
					$clines=array();
					$cc=0;
					foreach($newAnalyser->fileInfo as $part)	{

							// Adding the function/class name to list:
						if (is_array($part['sectionText']) && count($part['sectionText']))	{
							$clines[]='';
							$clines[]=str_replace(' ','&nbsp;',htmlspecialchars('      SECTION: '.$part['sectionText'][0]));
						}

						if ($part['class'])	{
							$clines[]='';
							$clines[]='';
						}

							// Add function / class header:
						$line=$part['parentClass'] && !$part['class']?'    ':'';
						$line.=preg_replace('#\{$#', '', trim($part['header']));
						$line = str_replace(' ','&nbsp;',htmlspecialchars($line));

							// Only selected files can be analysed:
						if (is_array($newDatArray['files'][$lFile_MD5]))	{

								// This will analyse the comment applied to the function and create a status of quality.
							$status = $this->checkCommentQuality($part['cDat'], $part['class']?1:0);

								// Wrap in color if a warning applies!
							$color='';
							switch($status[2])	{
								case 1:
									$color='#666666';
								break;
								case 2:
									$color='#ff6600';
								break;
								case 3:
									$color='red';
								break;
							}
							if ($color)	{
								$line='<span style="color:'.$color.'; font-weight: bold;">'.$line.'</span><div style="margin-left: 50px; background-color: '.$color.'; padding: 2px 2px 2px 2px;">'.htmlspecialchars(implode(chr(10),$status[0])).'</div>';
							}

								// Another analysis to do is usage count for functions:
							$uCountKey = 'H_'.t3lib_div::shortMD5($part['header']);
							if ($doWrite && $gp_options['usageCount'] && is_array($newDatArray['files'][$lFile_MD5]))	{
								$newDatArray['files'][$lFile_MD5]['usageCount'][$uCountKey] = $this->countFunctionUsage($part['header'], $extPhpFiles, $extDir);
							}

								// If any usage is detected:
							if (is_array($datArray['files'][$lFile_MD5]['usageCount']))	{
								if ($datArray['files'][$lFile_MD5]['usageCount'][$uCountKey]['ALL']['TOTAL'])	{
									$line.='<div style="margin-left: 50px; background-color: #cccccc; padding: 2px 2px 2px 2px; font-weight: bold; ">Usage: '.$datArray['files'][$lFile_MD5]['usageCount'][$uCountKey]['ALL']['TOTAL'].'</div>';
									foreach ($datArray['files'][$lFile_MD5]['usageCount'][$uCountKey] as $fileKey => $fileStat)	{
										if (substr($fileKey,0,4)=='MD5_')	{
											$line.='<div style="margin-left: 75px; background-color: #999999; padding: 1px 2px 1px 2px;">File: '.htmlspecialchars($fileStat['TOTAL'].' - '.$fileStat['fileName']).'</div>';
										}
									}
								} else {
									$line.='<div style="margin-left: 50px; background-color: red; padding: 2px 2px 2px 2px; font-weight: bold; ">NO USAGE COUNT!</div>';
								}
							}
						}

						$clines[]=$line;
					}

						// Make HTML table row:
					$lines[]='<tr'.(is_array($datArray['files'][$lFile_MD5])?' class="bgColor5"':' class="nonSelectedRows"').'>
						<td><input type="checkbox" name="selectThisFile[]" value="'.htmlspecialchars($lFile).'"'.(is_array($datArray['files'][$lFile_MD5])?' checked="checked"':'').' /></td>
						<td nowrap="nowrap" valign="top">'.htmlspecialchars($lFile).'</td>
						<td nowrap="nowrap" valign="top">'.t3lib_div::formatSize(filesize($extDir.$lFile)).'</td>
						<td nowrap="nowrap">'.nl2br(implode(chr(10),$clines)).'</td>
					</tr>';
				}
			}
			$content.='
			<br /><br /><p><strong>PHP/INC files from extension:</strong></p>
			<table border="0" cellspacing="1" cellpadding="0">'.
						implode('',$lines).
						'</table>';
			$content.='
				<br />
				<strong>Package Title:</strong><br />
				<input type="text" name="title_of_collection" value="'.htmlspecialchars($datArray['meta']['title']).'"'.$GLOBALS['TBE_TEMPLATE']->formWidth().' /><br />
				<strong>Package Description:</strong><br />
				<textarea name="descr_of_collection"'.$GLOBALS['TBE_TEMPLATE']->formWidthText().' rows="5">'.t3lib_div::formatForTextarea($datArray['meta']['descr']).'</textarea><br />

				<input type="checkbox" name="options[usageCount]" value="1"'.($datArray['meta']['options']['usageCount']?' checked="checked"':'').' /> Perform an internal usage count of functions and classes (can be VERY time consuming!)<br />
				<input type="checkbox" name="options[includeCodeAbstract]" value="1"'.($datArray['meta']['options']['includeCodeAbstract']?' checked="checked"':'').' /> Include '.$this->includeContent.' bytes abstraction of functions (can be VERY space consuming)<br />

				<input type="submit" value="'.htmlspecialchars('Write/Update "ext_php_api.dat" file').'" name="WRITE" />
			';

#			$content.='<p>'.md5(serialize($datArray)).' MD5 - from current ext_php_api.dat file</p>';
#			$content.='<p>'.md5(serialize($newDatArray)).' MD5 - new, from the selected files</p>';

			if ($doWrite)	{
				$newDatArray['meta']['title']=t3lib_div::_GP('title_of_collection');
				$newDatArray['meta']['descr']=t3lib_div::_GP('descr_of_collection');
				$newDatArray['meta']['options']['usageCount'] = $gp_options['usageCount'];
				$newDatArray['meta']['options']['includeCodeAbstract'] = $gp_options['includeCodeAbstract'];
				t3lib_div::writeFile($extDir.'ext_php_api.dat',serialize($newDatArray));

				$content='<hr />';
				$content.='<p><strong>ext_php_api.dat file written to extension directory, "'.$extDir.'"</strong></p>';
				$content.='
					<input type="submit" value="Return..." name="_" />
				';
			}

		} else $content='<p>No PHP/INC files found extension directory.</p>';

		return $content;
	}

	/**
	 * Converts a "cDat" array into a JavaDoc comment
	 *
	 * @param	array		$cDat: This array contains keys/values which will be turned into a JavaDoc comment (see comment inside this function for the "syntax")
	 * @param	string		$commentLinesWhiteSpacePrefix: Prefix for the lines in the comment starting with " * " (normally a tab or blank string)
	 * @param	boolean		$isClass: Tells whether the comment is for a class.
	 * @return	string		The JavaDoc comment, lines are indented with one tab (except first)
	 */
	function generateComment($cDat,$commentLinesWhiteSpacePrefix,$isClass)	{
		/*	SYNTAX of cDat array:

			$cDat['text'] = '
			Lines of text

			More lines here.
			';

			$cDat['return']=array('string','Description value');
			$cDat['param'][]=array('string','Description value, param 1');
			$cDat['param'][]=array('string','Description value, param 2');
			$cDat['param'][]=array('string','Description value, param 3');
			$cDat['other'][]='@sometag	Another tag string...';
			$cDat['internal']=1;	// boolean
			$cDat['ignore']=1;	// boolean
		*/

		$commentLines=array();

		$commentText = trim($cDat['text']);
		if ($commentText)	{
			$textA = explode(chr(10),$commentText);
			foreach($textA as $v)	{
				$commentLines[] = rtrim($commentLinesWhiteSpacePrefix.' * '.$v);
			}
			$commentLines[] = rtrim($commentLinesWhiteSpacePrefix.' * ');
		}

		if (is_array($cDat['param']))	{
			foreach($cDat['param'] as $v)	{
				$commentLines[] = rtrim($commentLinesWhiteSpacePrefix.' * @param	'.$v[0].'		'.$v[1]);
			}
		}

		if (!$isClass && is_array($cDat['return']))	{
			$commentLines[] = rtrim($commentLinesWhiteSpacePrefix.' * @return	'.$cDat['return'][0].'		'.$cDat['return'][1]);
		}

		if ($cDat['ignore'])	{
			$commentLines[] = rtrim($commentLinesWhiteSpacePrefix.' * @ignore');
		}
		if ($cDat['access'])	{
			$commentLines[] = rtrim($commentLinesWhiteSpacePrefix.' * @access '.$cDat['access']);
		}

		if (is_array($cDat['other']))	{
			foreach($cDat['other'] as $v)	{
				$commentLines[] = rtrim($commentLinesWhiteSpacePrefix.' * '.$v);
			}
		}

		return '/**
'.implode(chr(10),$commentLines).'
'.$commentLinesWhiteSpacePrefix.' */';
	}

	/**
	 * Creates an array of param-tag parts (designed for a cDat array) from a string containing a PHP-function header
	 *
	 * @param	string		String with PHP-function header in, eg. '   function blablabla($this, $that="22")	{		'
	 * @return	array		The function arguments (here: $this, $that) in an array
	 */
	function tryToMakeParamTagsFromFunctionDefLine($v)	{
		$reg='';
		// Remove comments:
		$v = preg_replace('#/[*]+[^*]*[*]+/#', '', $v);
		// Fetch part between brackets:
		preg_match('#^[^\(]*\((.*)\)[^\)]*$#', $v, $reg);

		$paramA=array();
		if (trim($reg[1]))	{
			// Split argument parts:
			$parts = preg_split('#,[[:space:]]*[\$&]#', $reg[1]);

			foreach($parts as $vv)	{
				$varName='';
				list($varName) = t3lib_div::trimExplode('=', preg_replace('#^[\$&]#', '', $vv), 1);
				$paramA[]=array('[type]','$'.$varName.': ...');
			}
		}
		return $paramA;
	}

	/**
	 * Parses a JavaDoc comment into a cDat array with contents for the comment.
	 *
	 * @param	string		$content: The JavaDoc comment to parse (without initial "[slash]**")
	 * @param	array		Default array of parameters.
	 * @return	array		"cDat" array of the parsed JavaDoc comment.
	 */
	function parseFunctionComment($content,$arr)	{
		$pC=0;
		$outArr = array();
		$outArr['text']='';
		$outArr['param']=is_array($arr)?$arr:array();
		$outArr['return']=array('[type]','...');

		$linesInComment = explode(chr(10),$content);
		foreach($linesInComment as $v)	{
			$lineParts = explode('*',$v,2);
			if (count($lineParts)==2 && !trim($lineParts[0]))	{
				$lineContent = trim($lineParts[1]);
				if ($lineContent!='/')	{
					if (substr($lineContent,0,1)=='@')	{
						$lP = preg_split('#[[:space:]]+#', $lineContent, 3);
						switch ($lP[0])	{
							case '@param':
								$outArr['param'][$pC]=array(trim($lP[1]),trim($lP[2]));
								$pC++;
							break;
							case '@ignore':
								$outArr['ignore']=1;
							break;
							case '@access':
								$outArr['access']=$lP[1];
							break;
							case '@return':
								$outArr['return']=array(trim($lP[1]),trim($lP[2]));
							break;
							default:
								$outArr['other'][]=trim($lineContent);
								$outArr['other_index'][$lP[0]][]=trim($lP[1]).' '.trim($lP[2]);
							break;
						}
					} else {
						$outArr['text'].= chr(10) . preg_replace('#^[ ]#', '', $lineParts[1]);
					}
				}
			} else {
				$outArr['text'].=chr(10).$v;
			}
		}
		return $outArr;
	}

	/**
	 * Returns the whitespace before the [slash]** comment.
	 *
	 * @param	string		Input value
	 * @return	string		The prefix string
	 * @access private
	 */
	function getWhiteSpacePrefix($string)	{
		$reg=array();
		preg_match('#' . chr(10) . '([^' . chr(10) . '])$#', $string, $reg);
		return $reg[1];
	}

	/**
	 * Returns the class name if the input string is a class header.
	 *
	 * @param	string		Input value
	 * @return	string		If a class header, then return class name
	 * @access private
	 */
	function isHeaderClass($string)	{
		$reg = '';
		preg_match('#class[[:space:]]+([[:alnum:]_]+)[^{]*#', trim($string), $reg);
		return $reg[1];
	}

	/**
	 * Processes the script-header (with comments like license, author, class/function index)
	 *
	 * @param	string		$inStr: The header part.
	 * @return	string		Processed output
	 * @access private
	 */
	function splitHeader($inStr)	{
		$splitStr = md5(microtime());
		$string = $inStr;
		$string = preg_replace('#(' . chr(10) . '[[:space:]]*)(\/\*\*)#', '${1}' . $splitStr . '${2}', $string);
		$string = preg_replace('#(\*\/)([[:space:]]*' . chr(10) . ')#', '${1}' . $splitStr . '${2}', $string);

		$comments = explode($splitStr,$string);
		$funcCounter=0;

		if (md5($inStr)==md5(implode('',$comments)))	{
			foreach($comments as $k => $v)	{
				if (substr($v,0,3)=='/**' && substr($v,-2)=='*/')	{	// Checking that the content is solely a comment.
					$cDat = $this->parseFunctionComment(substr($v,3),array());
					if (is_array($cDat['other_index']['@author']))	{
						$this->headerInfo=$cDat;
					}
					if (t3lib_div::isFirstPartOfStr(trim($cDat['text']),'[CLASS/FUNCTION INDEX of SCRIPT]'))	{
						$lines=array();
						$cc = count($this->fileInfo)+5-substr_count($comments[$k],chr(10))+($this->sectionTextCounter*2)+($this->classCounter*2)+4;
						foreach($this->fileInfo as $part)	{
							if (is_array($part['sectionText']) && count($part['sectionText']))	{
								$lines[]=' *';
								$lines[]=' *              SECTION: '.rtrim($part['sectionText'][0]);
							}

							if ($part['class'])	{
								$lines[]=' *';
								$lines[]=' *';
							} else {
								$funcCounter++;
							}
							$line=$part['parentClass'] && !$part['class']?'    ':'';
							$line.=preg_replace('#\{$#', '', trim($part['header']));
							$line= str_pad($part['atLine']+$cc, 4, ' ', STR_PAD_LEFT).': '.$line;
							$lines[]=' * '.rtrim($line);
						}

						$comments[$k]=trim('
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
'.implode(chr(10),$lines).'
 *
 * TOTAL FUNCTIONS: '.$funcCounter.'
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

						');
					}
				}
			}
			$inStr=implode('',$comments);
		} else debug('MD5 error:');
		return $inStr;
	}

	/**
	 * Returns content to include in the ->fileInfo array (for API documentation)
	 *
	 * @param	string		$content: The function content.
	 * @param	boolean		$class: If class start
	 * @return	string		Processed content.
	 * @access private
	 */
	function includeContent($content, $class)	{
		if ($class)	return array($content,-1);

		if ($this->includeContent>0)	{
			if (strlen($content) > $this->includeContent+100)	{
				return array(substr($content,0,$this->includeContent*3/4).
					chr(10).
					'[...]'.
					chr(10).
					substr($content,-($this->includeContent*1/4)),1);
			} else return array($content,0);
		}
	}

	/**
	 * Tries to get the division comment above the function
	 *
	 * @param	string		$string: Content to test
	 * @return	mixed		Returns array with comment text lines if found.
	 * @access private
	 */
	function getSectionDivisionComment($string)	{
		$comment = t3lib_div::revExplode('**/',$string,2);
		if (trim($comment[1]) && preg_match('#\*\/$#', trim($comment[1]))) {
			$outLines=array();
			$cDat = $this->parseFunctionComment($comment[1],array());
			$textLines = t3lib_div::trimExplode(chr(10),$cDat['text'],1);
			foreach($textLines as $v)	{
				if (substr($v,0,1)!='*')	$outLines[]=$v;
			}
			return $outLines;
		}
	}

	/**
	 * My function is cool and clear
	 *
	 * @param	array		Array of function comment information; this includes the keys "text" "params" and "return" for instance.
	 * @param	boolean		If true, the information is for a class, not a function.
	 * @return	array		Array with message/severity and max-severity level
	 */
	function checkCommentQuality($datArray,$class=0)	{

			// Initialize:
		$messages=array();
		$severity=array();	// Add values 1-3, 3 is worts, 1 is cosmetic.

		if (is_array($datArray))	{	// If comment is found:

				// Analyse text:
			$text = trim($datArray['text']);

			if (!$text)	{
				$messages[]='Function/Class has no comment text at all. Please supply that!';
				$severity[]=3;
			} elseif (t3lib_div::isFirstPartOfStr($text,'[Describe function...]'))	{
				$messages[]='This function seems to be described with a default description like "[Describe function...]" - please apply a proper description!';
				$severity[]=3;
			} elseif(strlen($text)<$this->funcCommentLen) {
				$messages[]='Function/Class has a very short comment ("'.$text.'" - less than '.$this->funcCommentLen.' chars), which can hardly be sufficiently descriptive. Please correct';
				$severity[]=2;
			}

				// Analyse arguments:
			if (is_array($datArray['param']))	{
				foreach($datArray['param'] as $count => $var)	{
					$this->checkParameterComment($var,'Function argument number '.($count+1),$messages,$severity);
				}
			}

				// Analyse return value:
			if (!$class)	{
				$this->checkParameterComment($datArray['return'],'Return tag',$messages,$severity,TRUE);
			}
		} else {	// No comment at all:
			$messages[]='Function/Class has no comment at all, please add one.';
			$severity[]=3;
		}

			// Create output array:
		$output = array($messages, $severity, count($severity)?max($severity):0);

		return $output;
	}

	/**
	 * Checking function arguments / return value for quality
	 *
	 * @param	array		Array with keys 0/1 being type / comment of the argument being checked
	 * @param	string		Label identifying the argument/return, eg. "Function argument number 1" or "Return tag"
	 * @param	array		Array of return messages. Passed by reference!
	 * @param	array		Array of severity levels. Passed by reference!
	 * @param	boolean		If true, the comment is for the @return tag.
	 * @return	void		No return value needed - changes made to messages/severity arrays which are passed by reference.
	 * @see checkCommentQuality()
	 */
	function checkParameterComment($var,$label,&$messages,&$severity,$return=FALSE)	{
		if (trim($var[0])=='[type]')	{
			$messages[]=$label.' had type "[type]" (or does not exist) which is the default label applied by the documentation help module. Please enter a proper type for variable ('.$this->varTypeList.').';
			$severity[]=3;
		} elseif (!t3lib_div::inList($this->varTypeList,$var[0])) {
			$messages[]=$label.' had type "'.$var[0].'" which is not in the list of allowed variable types ('.$this->varTypeList.').';
			$severity[]=2;
		} elseif ($var[0]!='void' && !$return) {		// If "void", no comment needed.
			$varCommentWithoutVar = trim(preg_replace('#^\$[[:alnum:]_]*:#', '', trim($var[1])));
			if (strlen($varCommentWithoutVar)<$this->argCommentLen)	{
				$messages[]=$label.' has a very short comment ("'.$varCommentWithoutVar.'" - less than '.$this->argCommentLen.' chars), which can hardly be sufficiently descriptive. Please correct';
				$severity[]=2;
			}
		}
	}

	/**
	 * Counts the usage of a function/class in all files related to the extension!
	 *
	 * @param	string		Function or class header, eg. "function blablabla() {"
	 * @param	array		Array of files in the extension to search
	 * @param	string		Absolute directory prefix for files in $extPhpFiles
	 * @return	array		Count statistics in an array.
	 */
	function countFunctionUsage($functionHeader, $extPhpFiles, $extDir)	{
		$reg=array();
		$counter=array();

			// Search for class/function .
		if (preg_match('/(class|function)[[:space:]]+([[:alnum:]_]+)[[:space:]]*/i', $functionHeader, $reg))	{
			$pt = t3lib_div::milliseconds();

				// Reset counter array:
			$counter=array();

				// For each file found in the extension, search for the function/class usage:
			foreach($extPhpFiles as $fileName)	{

					// File MD5 for array keys:
				$lFile_MD5 = 'MD5_'.t3lib_div::shortMD5($fileName);

					// Detect function/class:
				switch(strtolower($reg[1]))	{
					case 'class':		// If it's a class:
						$res = $this->searchFile('t3lib_div::makeinstance[[:space:]]*\(["\']'.strtolower($reg[2]).'["\']\)', $fileName, $extDir);

						if ($res[0])	{
							$counter['ALL']['makeinstance']+=$res[0];
							$counter['ALL']['TOTAL']+=$res[0];

							$counter[$lFile_MD5]['fileName']=$fileName;
							$counter[$lFile_MD5]['makeinstance']+=$res[0];
							$counter[$lFile_MD5]['TOTAL']+=$res[0];
						}
					break;
					case 'function':	// If it's a function:

							// Instantiated usage:
						$res = $this->searchFile('->'.strtolower($reg[2]).'[[:space:]]*\(', $fileName, $extDir);

						if ($res[0])	{
							$counter['ALL']['objectUsage']+=$res[0];
							$counter['ALL']['TOTAL']+=$res[0];

							$counter[$lFile_MD5]['fileName']=$fileName;
							$counter[$lFile_MD5]['objectUsage']+=$res[0];
							$counter[$lFile_MD5]['TOTAL']+=$res[0];
						}

							// Non-instantiated usage:
						$res = $this->searchFile('::'.strtolower($reg[2]).'[[:space:]]*\(', $fileName, $extDir);

						if ($res[0])	{
							$counter['ALL']['nonObjectUsage']+=$res[0];
							$counter['ALL']['TOTAL']+=$res[0];

							$counter[$lFile_MD5]['fileName']=$fileName;
							$counter[$lFile_MD5]['nonObjectUsage']+=$res[0];
							$counter[$lFile_MD5]['TOTAL']+=$res[0];
						}
					break;
				}
			}

			$counter['_searchtime_milliseconds']= t3lib_div::milliseconds()-$pt;
			$counter['_functionHeader']=$functionHeader;
		}

		return $counter;
	}

	/**
	 * Searches a file for a regex
	 *
	 * @param	string		Regex to split the content with (based on preg_split())
	 * @param	string		The filename to search in (is cached each time it has been read)
	 * @param	string		Absolute path to the directory of the file (prefix for reading)
	 * @return	array		Array of count statistics. First key (0 - zero) contains the count information. (Rest is reserved for future use, like linenumbers)
	 */
	function searchFile($splitString, $fileName, $extDir)	{

			// Set/Get file content from cache:
		if (!isset($this->searchFile_fileCache[$fileName]))	{
			$this->searchFile_fileCache[$fileName] = strtolower(t3lib_div::getUrl($extDir.$fileName));	// strtolower for matching case insensitive...
		}

			// Make search (by splitting)
		$result = preg_split('#' . $splitString . '#', $this->searchFile_fileCache[$fileName]);

		if (count($result)>1)	{
			return array(count($result)-1);
		} else {
			return array(0);
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_phpdoc.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_phpdoc.php']);
}
?>
