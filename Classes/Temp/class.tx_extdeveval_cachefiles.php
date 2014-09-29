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
 * Confirmed removal of temp_CACHED_ files
 *
 * $Id: class.tx_extdeveval_cachefiles.php 63721 2012-06-22 14:12:37Z ohader $
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   53: class tx_extdeveval_cachefiles
 *   60:     function cacheFiles()
 *  106:     function removeCacheFiles()
 *  125:     function removeALLtempCachedFiles()
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Confirmed removal of temp_CACHED_ files
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_cachefiles {

	/**
	 * The main function in the class
	 *
	 * @return	string		HTML content
	 */
	function cacheFiles()	{
		$content = '';

			// CURRENT:
		$content.='<strong>1: The current cache files:</strong>'.
			Tx_Extdeveval_Compatibility::viewArray(t3lib_extMgm::currentCacheFiles());

			// REMOVING?
		if (t3lib_div::_GP('REMOVE_temp_CACHED'))	{
			$number = $this->removeCacheFiles();
			$content.= '<hr /><p><strong>2: Tried to remove '.$number.' cache files.</strong></p>';
		}
		if (t3lib_div::_GP('REMOVE_temp_CACHED_ALL'))	{
			$content.= '<hr /><p><strong>2: Removing ALL "temp_CACHED_*" files:</strong></p>'.
				$this->removeALLtempCachedFiles();
		}



		$files = t3lib_div::getFilesInDir(PATH_typo3conf,'php');

		$tRows=array();
		foreach ($files as $f)	{
			$tRows[]='<tr>
				<td>'.htmlspecialchars($f).'</td>
				<td>'.t3lib_div::formatSize(filesize(PATH_typo3conf.$f)).'</td>
			</tr>';
		}

		$content.='<br /><strong>3: PHP files (now) in "'.PATH_typo3conf.'":</strong><br />
		<table border="1">'.implode('',$tRows).'</table>

		<input type="submit" name="REMOVE_temp_CACHED" value="REMOVE current temp_CACHED files" />
		<input type="submit" name="REMOVE_temp_CACHED_ALL" value="REMOVE ALL temp_CACHED_* files" />
		<input type="submit" name="_" value="Refresh" />
		';


		return $content;
	}

	/**
	 * Unlink (delete) cache files - only the two current ones!
	 *
	 * @return	integer		Number of files which were tried to be removed.
	 */
	function removeCacheFiles()	{
		$cacheFiles=t3lib_extMgm::currentCacheFiles();
		$out=0;
		if (is_array($cacheFiles))	{
			reset($cacheFiles);
			while(list(,$cfile)=each($cacheFiles))	{
				unlink($cfile);
				clearstatcache();
				$out++;
			}
		}
		return $out;
	}

	/**
	 * Unlink (delete) cache files - ALL, including those not current, made by another sitepath.
	 *
	 * @return	string		Status Message
	 */
	function removeALLtempCachedFiles()	{
		$path = PATH_typo3conf;
		if (is_dir($path))	{
			$filesInDir=t3lib_div::getFilesInDir($path,'php',1);
			reset($filesInDir);
			while(list($kk,$vv)=each($filesInDir))	{
				if (t3lib_div::isFirstPartOfStr(basename($vv),'temp_CACHED_'))	{
					if (strstr(basename($vv),'ext_localconf.php') || strstr(basename($vv),'ext_tables.php'))	{
						$content.='REMOVED: '.$vv.'<br />';
						unlink($vv);
						if (file_exists($vv))	$content.='<strong><font color="red">ERROR: File still exists, so could not be removed anyways!</font></strong><br />';
					}
				}
			}
		}
		return $content;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_cachefiles.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_cachefiles.php']);
}
?>