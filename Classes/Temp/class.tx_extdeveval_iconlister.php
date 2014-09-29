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
 * Contains a class, tx_extdeveval_iconlister, which can display all icon combinations of a table
 *
 * $Id: class.tx_extdeveval_iconlister.php 63722 2012-06-22 14:24:10Z ohader $
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   58: class tx_extdeveval_iconlister
 *   69:     function main()
 *   93:     function renderTableIcons()
 *  281:     function renderTableMenu()
 *  311:     function renderOptionsMatrix()
 *  377:     function addTestRecordFields($recFields)
 *  393:     function addCheckBox($label)
 *  403:     function renameIconsInTypo3Temp()
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


/**
 * Class for displaying/generating all icons from a table
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @package TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_iconlister {

		// GPvars:
	var $tableName = '';		// Table name to show icons from
	var $optionsMatrix = array();		// Options for what to show.

	/**
	 * Main function, branching out to rendering functions
	 *
	 * @return	string		HTML content for the module.
	 */
	function main()	{

			// Set GPvar:
		$this->tableName = t3lib_div::_GP('tableName');
		$this->optionsMatrix = t3lib_div::_GP('optionsMatrix');

			// Render table menu:
		$content.=$this->renderTableMenu();

			// Render icons from specific table, if any is set:
		if ($this->tableName)	{
			$content.=$this->renderOptionsMatrix();
			$content.=$this->renderTableIcons();
		}

			// Return content:
		return $content;
	}

	/**
	 * Rendering the table icons
	 *
	 * @return	string		HTML
	 */
	function renderTableIcons()	{
		if (is_array($GLOBALS['TCA'][$this->tableName])) {

				// Set the default:
			$this->testRecords=array();
			$this->testRecords[]=array();
			$tableCols=array();

				// Set hidden:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['disabled'] && $this->optionsMatrix['Hidden']) {
				$this->addTestRecordFields(array(
					$GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['disabled'] => 1
				));
				$tableCols['Hidden'] = $GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['disabled'];
			}
				// Set starttime:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['starttime'] && $this->optionsMatrix['Starttime']) {
				$this->addTestRecordFields(array(
					$GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['starttime'] => time() + 60
				));
				$tableCols['Starttime'] = $GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['starttime'];
			}
				// Set endtime:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['endtime'] && $this->optionsMatrix['Endtime']) {
				$this->addTestRecordFields(array(
					$GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['endtime'] => time() + 60
				));
/*				$this->addTestRecordFields(array(
					$GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['endtime'] => time() - 60
				));
	*/			$tableCols['Endtime'] = $GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['endtime'];
			}
				// Set fe_group:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['fe_group'] && $this->optionsMatrix['Access']) {
				$this->addTestRecordFields(array(
					$GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['fe_group'] => 1
				));
				$tableCols['Access'] = $GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['fe_group'];
			}

				// If "pages" table, add "extendToSubpages"
			if ($this->tableName=='pages' && $this->optionsMatrix['Incl.Sub'])	{
				$this->addTestRecordFields(array(
					'extendToSubpages' => 1
				));
				$tableCols['Incl.Sub'] = 'extendToSubpages';
			}

				// Set "delete" flag:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['delete'] && $this->optionsMatrix['Del.']) {
				$this->testRecords[]=array(
					$GLOBALS['TCA'][$this->tableName]['ctrl']['delete'] => 1
				);
				$tableCols['Del.'] = $GLOBALS['TCA'][$this->tableName]['ctrl']['delete'];
			}

				// _NO_ICON_FOUND
			if ($this->optionsMatrix['_NO_ICON_FOUND'])	{
				$this->testRecords[]=array(
					'_NO_ICON_FOUND' => 1
				);
			}

			if ($this->tableName=='pages')	{
				$tempArray=array();

				if ($this->optionsMatrix['Doktype'])	{
					foreach ($GLOBALS['PAGES_TYPES'] as $doktype => $dat) {
						if ($dat['icon'])	{
							foreach($this->testRecords as $rec)	{
								$tempArray[] = array_merge($rec,array('doktype' => $doktype));
							}
						}
					}
					$tableCols['Doktype'] = 'doktype';
				}

				if ($this->optionsMatrix['Module'])	{
					foreach ($GLOBALS['ICON_TYPES'] as $module => $dat)	{
						if ($dat['icon'])	{
							foreach($this->testRecords as $rec)	{
								$tempArray[] = array_merge($rec,array('module' => $module));
							}
						}
					}
					$tableCols['Module'] = 'module';
				}

				$this->testRecords = array_merge($tempArray,$this->testRecords);
			} elseif (is_array($GLOBALS['TCA'][$this->tableName]['ctrl']['typeicons']) && $this->optionsMatrix['TypeIcon']) {
				$tempArray=array();

				foreach ($GLOBALS['TCA'][$this->tableName]['ctrl']['typeicons'] as $typeVal => $dat) {
					foreach ($this->testRecords as $rec) {
						$tempArray[] = array_merge($rec, array($GLOBALS['TCA'][$this->tableName]['ctrl']['typeicon_column'] => $typeVal));
					}
				}
				$tableCols['TypeIcon'] = $GLOBALS['TCA'][$this->tableName]['ctrl']['typeicon_column'];

				$this->testRecords = array_merge($tempArray,$this->testRecords);
			}



				// Render table:
			$tRows=array();
			$sortRows=array();

				// Draw header:
			$tCells=array();
			$tCells[]='Icon:';
			$tCells[]='Name:';

			foreach($tableCols as $label => $field)	{
				$tCells[]=$label.':';
			}

			$tRows[]='
				<tr class="bgColor5" style="font-weight: bold;">
					<td>'.implode('</td>
					<td>',$tCells).'</td>
				</tr>';

				// Traverse fake records, render icons:
			foreach($this->testRecords as $row)	{
				$tCells=array();
				$icon = t3lib_iconWorks::getIconImage($this->tableName,$row,$GLOBALS['BACK_PATH']);
				$tCells[]=$icon;

				$attrib = t3lib_div::get_tag_attributes($icon);
				$fileName = substr($attrib['src'],strlen($GLOBALS['BACK_PATH']));
				$tCells[]=$fileName;
				$sortRows[]=$fileName;

				foreach($tableCols as $label => $field)	{
					switch($label)	{
						case 'Hidden':
						case 'Access':
						case 'Del.':
						case 'Incl.Sub':
							$tCells[] = $row[$field] ? 'YES' : '';
						break;
						case 'Endtime':
						case 'Starttime':
							$tCells[] = $row[$field] ? t3lib_BEfunc::date($row[$field]) : '';
						break;
						default:
							$tCells[] = $row[$field];
						break;
					}
				}

				$tRows[]='
					<tr class="bgColor4">
						<td>'.implode('</td>
						<td>',$tCells).'</td>
					</tr>';
			}

				// Create table with icons:
			$output=
				$this->tableName.':
				<table border="0" cellpadding="0" cellspacing="1">
					'.implode('',$tRows).'
				</table>';


				// Show unique filenames involved:
			sort($sortRows);
			$sortRows = array_unique($sortRows);
			$output.='<br /><p><strong>Unique icons:</strong></p>'.count($sortRows);
			$output.='<br/><br/>';
			$output.='<p><strong>Filenames:</strong></p>'.Tx_Extdeveval_Compatibility::viewArray($sortRows);

				// DEVELOPMENT purposes, do NOT rename if you don't know what you are doing!
			#$this->renameIconsInTypo3Temp();
		}

		return $output;
	}

	/**
	 * Rendering the table select menu:
	 *
	 * @return	string		HTML
	 */
	function renderTableMenu()	{
			// Create menu options:
		$opt=array();
		$opt[]='
				<option value=""></option>';
		foreach ($GLOBALS['TCA'] as $tableName => $cfg) {
			$opt[]='
				<option value="'.htmlspecialchars($tableName).'"'.($this->tableName==$tableName?' selected="selected"':'').'>'.htmlspecialchars($GLOBALS['LANG']->sL($cfg['ctrl']['title'])).'</option>';
		}

			// Compile selector box menu:
		$content = '
			<p><strong>Select table:</strong></p>
			<select onchange="'.htmlspecialchars('document.location = \'' . t3lib_BEfunc::getModuleUrl('tools_txextdevevalM1') . '&tableName=\'+this.options[this.selectedIndex].value').'">
				'.implode('',$opt).'
			</select>
			<hr />
			';

			// Return selector box menu:
		return $content;
	}

	/**
	 * Render the list of checkboxes with options for which kind of renderings of icons should be done:
	 *
	 * @return	string		HTML
	 */
	function renderOptionsMatrix()	{
		if (is_array($GLOBALS['TCA'][$this->tableName])) {

				// Set the default:
			$options=array();

				// Set hidden:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['disabled']) {
				$options[]=$this->addCheckBox('Hidden');
			}
				// Set starttime:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['starttime']) {
				$options[]=$this->addCheckBox('Starttime');
			}
				// Set endtime:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['endtime']) {
				$options[]=$this->addCheckBox('Endtime');
			}
				// Set fe_group:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['enablecolumns']['fe_group']) {
				$options[]=$this->addCheckBox('Access');
			}

				// If "pages" table, add "extendToSubpages"
			if ($this->tableName=='pages')	{
				$options[]=$this->addCheckBox('Incl.Sub');
			}

				// Set "delete" flag:
			if ($GLOBALS['TCA'][$this->tableName]['ctrl']['delete'])	{
				$options[]=$this->addCheckBox('Del.');
			}

				// Set "_NO_ICON_FOUND" flag:
			$options[]=$this->addCheckBox('_NO_ICON_FOUND');




			if ($this->tableName=='pages')	{
				$options[]=$this->addCheckBox('Doktype');
				$options[]=$this->addCheckBox('Module');
			} elseif (is_array($GLOBALS['TCA'][$this->tableName]['ctrl']['typeicons'])) {
				$options[]=$this->addCheckBox('TypeIcon');
			}

			$content='
				<p>Select options to render:</p>
			'.implode('<br />',$options).'
			<br />
			<input type="submit" name="set" value="Set options" />
			<hr />
			';

			return $content;
		}
	}

	/**
	 * This will traverse the current pseudo records and replicate them all, adding the new array supplied.
	 *
	 * @param	array		Array with a field set to value according to what is tested.
	 * @return	void
	 */
	function addTestRecordFields($recFields)	{

		$tempArray=array();
		foreach($this->testRecords as $rec)	{
			$tempArray[] = array_merge($rec,$recFields);
		}

		$this->testRecords = array_merge($this->testRecords,$tempArray);
	}

	/**
	 * Create checkbox for options-matrix.
	 *
	 * @param	string		Label for checkbox.
	 * @return	string		Checkbox <input> tag.
	 */
	function addCheckBox($label)	{
		return '<input type="checkbox" name="optionsMatrix['.$label.']" value="1"'.($this->optionsMatrix[$label]?' checked="checked"':'').' /> '.$label;
	}

	/**
	 * Rename "icon_" files in typo3temp/
	 * Function for development purposes.
	 *
	 * @return	void
	 */
	function renameIconsInTypo3Temp()	{
		$files = t3lib_div::getFilesInDir(PATH_site.'typo3temp/','gif,png',1);
		foreach($files as $filename)	{
			if (t3lib_div::isFirstPartOfStr(basename($filename),'icon_'))	{
				$dir = dirname($filename).'/';

				$reg=array();
				if (preg_match('#icon_[[:alnum:]]+_([[:alnum:]_]+).(gif|png).(gif|png)#', basename($filename), $reg))	{
					if (@is_file($filename))	{
						$newFile = $dir.$reg[1].'.'.$reg[3];
						debug($newFile,1);
						rename($filename,$newFile);
					}
				}
			}
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_iconlister.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_iconlister.php']);
}
?>