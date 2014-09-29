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
 * Contains a class, tx_extdeveval_apidisplay, which can display the content of a ext_php_api.dat file.
 *
 * $Id: class.tx_extdeveval_apidisplay.php 43222 2011-02-07 15:40:53Z ohader $
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   57: class tx_extdeveval_apidisplay
 *   76:     function main($SERcontent,$phpFile)
 *   97:     function renderAPIdata($apiDat,$phpFile)
 *  186:     function makeHeader($dat)
 *  202:     function renderFileContent($fDat)
 *  398:     function splitFunctionHeader($v)
 *  419:     function outputStandAloneDisplay($title,$content)
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Class for rendering the API data from "ext_php_api.dat" files as HTML
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_apidisplay {

		// External, static:
	var $showPrivateIgnoreFunc=1;

		// Internal, static: GPvar:
	var $showAPI;			// If true, the display of the API HTML will be rendered in a standAlone HTML document (the one which opens in a new window!)

		// Internal, dynamic:
	var $fileSizeTotal=0;			// Counter
	var $funcClassesTotal=0;		// Counter

	/**
	 * Main function, branching out to rendering functions
	 *
	 * @param	string		Serialized PHP array with the API data in
	 * @param	string		Specifically, which PHP file from the extension to list API for (if blank, all files are shown).
	 * @return	string		HTML content for the module.
	 */
	function main($SERcontent,$phpFile)	{

			// Setting GPvar:
		$this->showAPI = t3lib_div::_GP('showAPI');

			// Unserialize content:
		$apiDat = unserialize($SERcontent);
		if (is_array($apiDat))	{
			return $this->renderAPIdata($apiDat,$phpFile);
		} else {
			return '<strong>ERROR:</strong> XML data could not be parsed: '.$apiDat;
		}
	}

	/**
	 * Renders the API data array into browser HTML
	 *
	 * @param	array		API data array
	 * @param	string		Specific PHP file to display if any. Blank will display all files in the extension.
	 * @return	string		HTML output.
	 */
	function renderAPIdata($apiDat,$phpFile)	{

			// Initialize:
		$content = '';

			// If there is an array of files, then render the API:
		if (is_array($apiDat['files']))	{

				// The array key used for each file:
			$phpFileKey = 'MD5_'.t3lib_div::shortMD5($phpFile);
			$superIndexAcc='';
			$indexAcc='';
			$detailsAcc='';

				// Checking for specific or all rendering:
			if ($phpFile && is_array($apiDat['files'][$phpFileKey]))	{		// IF only single PHP file:
				$standAloneTitle = $apiDat['files'][$phpFileKey]['filename'];

					// Create header, showing title and description as saved in the API data:
				$content.=$this->makeHeader(array(
					'title' => $standAloneTitle,
					'descr' => $apiDat['files'][$phpFileKey]['header']['text']
				));

					// Render API for the file:
				list(,$indexAcc,$detailsAcc) = $this->renderFileContent($apiDat['files'][$phpFileKey]);
				$nFiles = 1;
			} else {	// ... Otherwise render API listing for all:

					// Create header, showing title and description as saved in the API data:
				$content.=$this->makeHeader($apiDat['meta']);

					// Traverse the files in the array, render API and add the content to the index and content accumulation variables:
				foreach($apiDat['files'] as $fKey => $fDat)	{

						// Render API for the file:
					list($superIndex,$index,$details) = $this->renderFileContent($fDat);

						// Add index/body:
					$superIndexAcc.=$superIndex;
					$indexAcc.=$index;
					$detailsAcc.=$details;
				}
				$nFiles = count($apiDat['files']);
				$standAloneTitle = $apiDat['meta']['title'];
			}

				// Finally, concatenate the content of index and body:
			$content.='
			<p>&nbsp;</p>
			<p>
			Files: <strong>'.$nFiles.'</strong><br/>
			Total filesizes: <strong>'.t3lib_div::formatSize($this->fileSizeTotal).'bytes</strong><br/>
			Functions and classes: <strong>'.$this->funcClassesTotal.'</strong><br/>
			</p>

			'.($superIndexAcc ? '<div id="s-index">'.$superIndexAcc.'</div>' : '').'

			<div id="c-index">'.$indexAcc.'</div>

			<h2 class="c-details">Detailed descriptions:</h2>
			<div id="c-body">'.$detailsAcc.'</div>';
		} else {	// IF no files found in extension, show this message:

				// Create header, showing title and description as saved in the API data:
			$content.=$this->makeHeader($apiDat['meta']);

				// Print error message:
			$content.='<br /><strong>ERROR:</strong> No files listed.';
		}

			// If a link was clicked to open the rendering in a new window:
		if ($this->showAPI)	{
			$this->outputStandAloneDisplay($standAloneTitle, $content);
		} else {	// Display in backend content frame, then add link to open in new window:
			$aOnClick = "return top.openUrlInWindow('".t3lib_div::linkThisScript(array('showAPI'=>1))."','ShowAPI');";
			$content='<div id="c-openInNewWindowLink"><a href="#" onclick="'.htmlspecialchars($aOnClick).'">Open API in new window.</a></div>'.$content;
		}

			// Return content:
		return '<div id="c-APIdoc">'.$content.'</div>';
	}

	/**
	 * Creates the header of the API listing.
	 *
	 * @param	array		Array with meta data for the API data
	 * @return	string		HTML content of the header.
	 */
	function makeHeader($dat)	{

		$content.='
			<h2>'.htmlspecialchars($dat['title']).'</h2>';
		$content.='
			<p class="c-headerDescription">'.nl2br(htmlspecialchars(trim($dat['descr']))).'</p>';

		return $content;
	}

	/**
	 * Renders the API listing for a single file, represented by the input array
	 *
	 * @param	array		Array with API information for a single file.
	 * @return	array		Array with superindex / index / body content (keys 0/1)
	 */
	function renderFileContent($fDat)	{

			// Set anchor value:
		$anchor = md5($fDat['filename']);

		$this->fileSizeTotal+=$fDat['filesize'];
		$this->funcClassesTotal+=(is_array($fDat['DAT'])?count($fDat['DAT']):'0');

			// Create file header content:
		$superIndex.='
			<h3><a href="#s-'.$anchor.'">'.htmlspecialchars($fDat['filename']).'</a></h3>
		';

		$index.='
			<h3><a name="s-'.$anchor.'"></a><a href="#'.$anchor.'">'.htmlspecialchars($fDat['filename']).'</a></h3>
			<p class="c-fileDescription">'.nl2br(htmlspecialchars(trim($fDat['header']['text']))).'</p>';

		$content.='

					<!--
						API content for file: '.htmlspecialchars($fDat['filename']).'
					-->
					<div class="c-header">
						<a name="'.$anchor.'"></a>
						<h3><a href="#top">'.htmlspecialchars($fDat['filename']).'</a></h3>
						<p class="c-fileDescription">'.nl2br(htmlspecialchars(trim($fDat['header']['text']))).'</p>

						<table border="0" cellpadding="0" cellspacing="1" class="c-details">
							<tr>
								<td class="c-Hcell">Filesize:</td>
								<td>'.t3lib_div::formatSize($fDat['filesize']).'</td>
							</tr>
							<tr>
								<td class="c-Hcell">Func/Classes:</td>
								<td>'.(is_array($fDat['DAT'])?count($fDat['DAT']):'N/A').'</td>
							</tr>'.(is_array($fDat['header']['other']) ? '
							<tr>
								<td class="c-Hcell">Tags:</td>
								<td>'.nl2br(htmlspecialchars(implode(chr(10),$fDat['header']['other']))).'</td>
							</tr>' :'').'
						</table>
					</div>
			';

			// If there are classes/functions in the file, render API for those:
		if (is_array($fDat['DAT']))	{

				// Traverse list of classes/functions:
			foreach($fDat['DAT'] as $k => $v)	{

				if (is_array($v['sectionText']) && count($v['sectionText']))	{
							// Section header:
						$index.='

							<h3 class="section">'.nl2br(htmlspecialchars(trim(implode(chr(10),$v['sectionText'])))).'</h3>
							';
				}

					// Check, if the access tag is set to private (and if so, do not show):
				if (($v['cDat']['access']!='private' && !$v['cDat']['ignore']) || $this->showPrivateIgnoreFunc)	{

						// Set anchor value first:
					$anchor = md5($fDat['filename'].':'.$v['header'].$v['parentClass']);
					$headerString = preg_replace('#\{[[:space:]]*$#', '', $v['header']);
					$tClass = 'c-'.(t3lib_div::isFirstPartOfStr(strtolower($v['header']),'class') ? 'class' : 'function');

						// Add header for function (title / description etc):
					$index.='
						<h4 class="'.$tClass.'"><a href="#'.$anchor.'">'.htmlspecialchars($headerString).'</a></h4>';
					$content.='
					<!--
						Description for "'.htmlspecialchars($headerString).'"
					-->
					<div class="'.$tClass.'">
						<a name="'.$anchor.'"></a>
						<h4><a href="#top">'.htmlspecialchars($headerString).'</a></h4>
						<p class="c-funcDescription">'.nl2br(htmlspecialchars(trim($v['cDat']['text']))).'</p>
						';

						// Render details for the function/class:
						// Parameters:
					$tableRows=array();
					if (is_array($v['cDat']['param']))	{

							// Get argument names of current function:
						$funcHeadParams = $this->splitFunctionHeader($v['header']);

							// For each argument, render a row in the table:
						foreach($v['cDat']['param'] as $k2 => $pp)	{
							$tableRows[]='
							<tr>
								<td class="c-Hcell">'.htmlspecialchars($funcHeadParams[$k2]).'</td>
								<td class="c-vType">'.htmlspecialchars($pp[0]).'</td>
								<td class="c-vDescr">'.htmlspecialchars(trim($pp[1])).'</td>
							</tr>';
						}
					}
						// Add "return" value:
					$tableRows[]='
							<tr>
								<td class="c-Hcell">Returns: </td>
								<td class="c-vType">'.htmlspecialchars($v['cDat']['return'][0]).'</td>
								<td class="c-vDescr">'.htmlspecialchars(trim($v['cDat']['return'][1])).'</td>
							</tr>';

						// Add other tags:
					if (is_array($v['cDat']['other']))	{
						foreach($v['cDat']['other'] as $k2 => $pp)	{
							$tableRows[]='
							<tr>
								<td>&nbsp;</td>
								<td colspan="2" class="c-vDescr">'.htmlspecialchars($pp).'</td>
							</tr>';
						}
					}

						// Usage counts, if set:
					$uCKey = 'H_'.t3lib_div::shortMD5($v['header']);
					if (is_array($fDat['usageCount'][$uCKey]))	{
							// Add "TOTAL" usage:
						$tableRows[]='
							<tr>
								<td colspan="3"></td>
							</tr>
							<tr>
								<td class="c-Hcell">Total Usage:</td>
								<td class="c-vType">'.intval($fDat['usageCount'][$uCKey]['ALL']['TOTAL']).'</td>
								<td class="c-vDescr">&nbsp;</td>
							</tr>';

							// Add usage for single files:
						foreach($fDat['usageCount'][$uCKey] as $k3 => $v3)	{
							if (substr($k3,0,4)=='MD5_')	{
								$tableRows[]='
							<tr>
								<td class="c-vType">&nbsp;</td>
								<td class="c-vType">'.intval($fDat['usageCount'][$uCKey][$k3]['TOTAL']).'</td>
								<td class="c-vDescr">'.htmlspecialchars($fDat['usageCount'][$uCKey][$k3]['fileName']).'</td>
							</tr>';

							}
						}
					}


						// Add it all together:
					$content.='
						<table border="0" cellpadding="0" cellspacing="1" class="c-details">
							'.implode('
							',$tableRows).'
						</table>';


						// Adding todo to index:
					if (is_array($v['cDat']['other_index']['@todo']))	{
						$index.='<p class="c-indexTags"><span class="typo3-red"><strong>@todo:</strong> '.nl2br(htmlspecialchars(implode(chr(10),$v['cDat']['other_index']['@todo']))).'</span></p>';
					}

						// Adding package tags to index:
					if (is_array($v['cDat']['other_index']['@package']))	{
						$index.='<p class="c-indexTags"><span class="typo3-dimmed"><strong>@package:</strong> '.nl2br(htmlspecialchars(implode(chr(10),$v['cDat']['other_index']['@package']))).'</span></p>';
					}
					if (is_array($v['cDat']['other_index']['@subpackage']))	{
						$index.='<p class="c-indexTags"><span class="typo3-dimmed"><strong>@subpackage:</strong> '.nl2br(htmlspecialchars(implode(chr(10),$v['cDat']['other_index']['@subpackage']))).'</span></p>';
					}


						// Sample Content of function/class:
					if (is_array($v['content']))	{
						$content.='
						<div class="php-content">
							<pre>'.
								highlight_string('<?php'.chr(10).chr(10).'	'.trim($v['header'].chr(10).$v['content'][0]).chr(10).chr(10).'?>', 1).
							'</pre>
						</div>
						';
					}

						// End with </div>
					$content.='
					</div>
					';
				}
			}
		}

			// Return index and content variables:
		return array($superIndex,$index,$content);
	}

	/**
	 * Creates an array of the arguments for the input function header
	 *
	 * @param	string		String with PHP-function header in, eg. '   function blablabla($this, $that="22")	{		'
	 * @return	array		The function arguments (here: $this, $that) in an array
	 */
	function splitFunctionHeader($v)	{
		$reg='';
		preg_match('#^[^\(]*\((.*)\)[^\)]*$#',$v,$reg);

		$paramA=array();
		if (trim($reg[1]))	{
			$token = md5(microtime());
			$reg[1] = preg_replace('#,[[:space:]]*([\$&])#', $token . '${1}', $reg[1]);
			$parts = explode($token,$reg[1]);
			return $parts;
		}
		return $paramA;
	}

	/**
	 * Will output a stand-alone HTML page with $title and content.
	 *
	 * @param	string		The title of the page
	 * @param	string		The content on the page!
	 * @return	void		Exits before return!
	 */
	function outputStandAloneDisplay($title,$content)	{

			// Create a XHTML document with the API in:
		$docContent = '<?xml version="1.0" encoding="utf-8"?>
<?xml-stylesheet href="#internalStyle" type="text/css"?>
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<style type="text/css" id="internalStyle">
		/*<![CDATA[*/

			BODY, TD { font-family: arial, helvetica, verdana; font-size: 12px; }

			H2 { background-color: #999999; text-align: center; padding: 20px 2px 20px 2px; }
			H2.c-details {margin-top: 100px; }
			H3 { background-color: #cccccc; padding: 2px 2px 2px 10px;}
			H4 { background-color: #eeeeee;  padding: 2px 2px 2px 10px;}
			A { text-decoration: none; color: black; }
			P.c-headerDescription { font-style: italic; }
			TABLE TR TD {padding: 2px 3px 2px 3px; }
			TABLE TR {background-color: #ddddcc; }
			TABLE { margin: 5px 0px 10px 0px;}
			DIV#c-body DIV.c-function TABLE.c-details TR TD.c-Hcell,
					DIV#c-body DIV.c-class TABLE.c-details TR TD.c-Hcell {background-color: #ccdddd; font-weight: bold; }

			DIV#c-body TABLE.c-details {margin-top: 5px; width: 90%; }
			DIV#c-body DIV.c-function  TABLE.c-details {width: 100%; }
			DIV#c-body TABLE.c-details TR TD.c-Hcell {width: 25%;}
			DIV#c-body TABLE.c-details TR TD.c-vDescr {width: 75%;}
			.typo3-red { color: red; }
			.typo3-dimmed { color: #333333; }
			DIV#c-index P.c-indexTags { margin: 0px 0px 0px 110px; font-size:11px;}
			DIV#c-index H4.c-function { margin-left: 80px; }

			DIV#s-index {margin-top: 20px;}
			DIV#s-index H3 {background-color: #ccbbbb; margin: 0px 0px 0px 30px;}


			DIV#c-index { margin-left: 30px; }
			DIV#c-index H4 { margin-left: 60px; margin-top: 0px; margin-bottom: 1px;}
			DIV#c-index P.c-fileDescription { margin-top: 0px; margin-bottom: 5px; }
			DIV#c-index H3 { margin-bottom: 5px;}
			DIV#c-body H4 { background-color: #cccccc; }
			DIV#c-body DIV.c-class P.c-funcDescription {font-style: italic;}
			DIV#c-body DIV.c-class H4 { margin-bottom: 5px;}
			DIV#c-body DIV.c-class { margin-left: 30px; margin-top: 30px;}
			DIV#c-body H4 { margin-bottom: 5px;}
			DIV#c-body DIV.c-header P.c-fileDescription {font-style: italic;}
			DIV#c-body DIV.c-function { margin-left: 60px;  margin-top: 30px;  margin-right: 100px; }
			DIV#c-body DIV.c-function P.c-funcDescription {font-style: italic; margin: 5px 0px 5px 0px;}
			DIV#c-body DIV.c-header { margin-top: 100px; }
			DIV#c-body DIV.c-header H3 { background-color: #998888; font-size: 18px;}

			DIV#c-APIdoc H3.section { margin-left: 100px; }
		/*]]>*/
	</style>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="GENERATOR" content="TYPO3 3.6.0-dev, http://typo3.com, &#169; Kasper Sk&#229;rh&#248;j 1998-2003, extensions are copyright of their respective owners." />
	<title>API: '.htmlspecialchars($title).'</title>
</head>
<body>
<div id="c-APIdoc">
'.$content.'
</div>
</body>
</html>
';

		echo $docContent;
		exit;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_apidisplay.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_apidisplay.php']);
}
?>