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
 * Raw edit of records
 *
 * $Id: class.tx_extdeveval_cachefiles.php,v 1.6 2004/06/24 09:48:11 typo3 Exp $
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


require_once (PATH_t3lib.'class.t3lib_refindex.php');



/**
 * Raw edit of records
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_rawedit {

	/**
	 * The main function in the class
	 *
	 * @return	string		HTML content
	 */
	function main()	{
		$output = 'Enter [table]:[uid]:[fieldlist (optional)] <input name="table_uid" value="'.htmlspecialchars(t3lib_div::_POST('table_uid')).'" />';
		$output.='<input type="submit" name="_" value="REFRESH" /><br/>';

			// Show record:
		if (t3lib_div::_POST('table_uid'))	{
			list($table,$uid,$fieldName) = t3lib_div::trimExplode(':',t3lib_div::_POST('table_uid'),1);
			if ($GLOBALS['TCA'][$table])	{
				$rec = t3lib_BEfunc::getRecordRaw($table,'uid='.intval($uid),$fieldName?$fieldName:'*');

				if (count($rec))	{

					$pidOfRecord = $rec['pid'];
					$output.='<input type="checkbox" name="show_path" value="1"'.(t3lib_div::_POST('show_path')?' checked="checked"':'').'/> Show path and rootline of record<br/>';
					if (t3lib_div::_POST('show_path'))	{
						$output.='<br/>Path of PID '.$pidOfRecord.': <em>'.t3lib_BEfunc::getRecordPath($pidOfRecord, '', 30).'</em><br/>';
						$output.='RL:'.Tx_Extdeveval_Compatibility::viewArray(t3lib_BEfunc::BEgetRootLine($pidOfRecord)).'<br/>';
						$output.='FLAGS:'.
								($rec['deleted'] ? ' <b>DELETED</b>' : '').
								($rec['pid']==-1 ? ' <b>OFFLINE VERSION of '.$rec['t3ver_oid'].'</b>' : '').
									'<br/><hr/>';
					}
					
					if (t3lib_div::_POST('_EDIT'))	{
						$output.='<hr/>Edit:<br/><br/>';
						$output.='<input type="submit" name="_SAVE" value="SAVE" /><br/>';
						foreach($rec as $field => $value)	{
							$output.= '<b>'.htmlspecialchars($field).':</b><br/>';
							if (count(explode(chr(10),$value))>1)	{
								$output.= '<textarea name="record['.$table.']['.$uid.']['.$field.']" cols="100" rows="10">'.t3lib_div::formatForTextarea($value).'</textarea><br/>';
							} else {
								$output.= '<input name="record['.$table.']['.$uid.']['.$field.']" value="'.htmlspecialchars($value).'" size="100" /><br/>';
							}
						}
					} elseif (t3lib_div::_POST('_SAVE'))	{
						$incomingData = t3lib_div::_POST('record');
						$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,'uid='.intval($uid),$incomingData[$table][$uid]);
						$output.='<br/>Updated '.$table.':'.$uid.'...';
						$this->updateRefIndex($table,$uid);
					} else if (t3lib_div::_POST('_DELETE'))	{
						$GLOBALS['TYPO3_DB']->exec_DELETEquery($table,'uid='.intval($uid));
						$output.='<br/>Deleted '.$table.':'.$uid.'...';
						$this->updateRefIndex($table,$uid);
					} else {
						$output.='<input type="submit" name="_EDIT" value="EDIT" />';
						$output.='<input type="submit" name="_DELETE" value="DELETE" onclick="return confirm(\'Are you sure you wish to delete?\');" />';
						$output.='<br/>'.md5(implode($rec));
						$output.=Tx_Extdeveval_Compatibility::viewArray($rec);
					}
				} else {
					$output.='No record existed!';
				}
			}
		}

		return $output;
	}

	/**
	 * Update Reference Index (sys_refindex) for a record
	 *
	 * @param	string		Table name
	 * @param	integer		Record UID
	 * @return	void
	 */
	function updateRefIndex($table,$id)	{
		$refIndexObj = t3lib_div::makeInstance('t3lib_refindex');
		$result = $refIndexObj->updateRefIndexTable($table,$id);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_rawedit.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_rawedit.php']);
}
?>