<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 01.10.14
 * Time: 09:44
 */

namespace KayStrobach\Developer\Services;


use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\Utility\IconUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TableIconService
{
    /**
     * @var array
     */
    protected $testRecords;

    /**
     * @var array
     */
    protected  $optionsMatrix = array();

    public function getIconsForAllTables() 
    {
        $buffer = array();
        foreach(array_keys($GLOBALS['TCA']) as $tableName) {
            $buffer[] = array(
             'tableName' => $tableName,
             'rendering' => $this->renderTableIcons($tableName)
            );
        }
        return $buffer;
    }

    /**
     * @param $tableName
     * @return string
     */
    public function renderTableIcons($tableName) 
    {
        if (is_array($GLOBALS['TCA'][$tableName])) {

            $optionsMatrix = $this->renderOptionsMatrix($tableName);

            // Set the default:
            $this->testRecords = array();
            $this->testRecords[] = array();
            $tableCols = array();

            // Set hidden:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'] && $optionsMatrix['Hidden']) {
                $this->addTestRecordFields(
                    array(
                    $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'] => 1
                    )
                );
                $tableCols['Hidden'] = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'];
            }
            // Set starttime:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['starttime'] && $optionsMatrix['Starttime']) {
                $this->addTestRecordFields(
                    array(
                    $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['starttime'] => time() + 60
                    )
                );
                $tableCols['Starttime'] = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['starttime'];
            }
            // Set endtime:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['endtime'] && $optionsMatrix['Endtime']) {
                $this->addTestRecordFields(
                    array(
                    $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['endtime'] => time() + 60
                    )
                );
                /*				$this->addTestRecordFields(array(
                  $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['endtime'] => time() - 60
                 ));
                 */
                $tableCols['Endtime'] = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['endtime'];
            }
            // Set fe_group:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'] && $optionsMatrix['Access']) {
                $this->addTestRecordFields(
                    array(
                    $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'] => 1
                    )
                );
                $tableCols['Access'] = $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group'];
            }

            // If "pages" table, add "extendToSubpages"
            if ($tableName == 'pages' && $optionsMatrix['Incl.Sub']) {
                $this->addTestRecordFields(
                    array(
                    'extendToSubpages' => 1
                    )
                );
                $tableCols['Incl.Sub'] = 'extendToSubpages';
            }

            // Set "delete" flag:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['delete'] && $optionsMatrix['Del.']) {
                $this->testRecords[] = array(
                 $GLOBALS['TCA'][$tableName]['ctrl']['delete'] => 1
                );
                $tableCols['Del.'] = $GLOBALS['TCA'][$tableName]['ctrl']['delete'];
            }

            // _NO_ICON_FOUND
            if ($optionsMatrix['_NO_ICON_FOUND']) {
                $this->testRecords[] = array(
                 '_NO_ICON_FOUND' => 1
                );
            }

            if ($tableName == 'pages') {
                $tempArray = array();

                if ($optionsMatrix['Doktype']) {
                    foreach ($GLOBALS['PAGES_TYPES'] as $doktype => $dat) {
                        if ($dat['icon']) {
                            foreach ($this->testRecords as $rec) {
                                $tempArray[] = array_merge($rec, array('doktype' => $doktype));
                            }
                        }
                    }
                    $tableCols['Doktype'] = 'doktype';
                }

                $this->testRecords = array_merge($tempArray, $this->testRecords);
            } elseif (is_array($GLOBALS['TCA'][$tableName]['ctrl']['typeicons']) && $optionsMatrix['TypeIcon']) {
                $tempArray = array();

                foreach ($GLOBALS['TCA'][$tableName]['ctrl']['typeicons'] as $typeVal => $dat) {
                    foreach ($this->testRecords as $rec) {
                        $tempArray[] = array_merge($rec, array($GLOBALS['TCA'][$tableName]['ctrl']['typeicon_column'] => $typeVal));
                    }
                }
                $tableCols['TypeIcon'] = $GLOBALS['TCA'][$tableName]['ctrl']['typeicon_column'];

                $this->testRecords = array_merge($tempArray, $this->testRecords);
            }


            // Render table:
            $tRows = array();
            $sortRows = array();

            // Draw header:
            $tCells = array();
            $tCells[] = 'Icon:';
            $tCells[] = 'Name:';

            foreach ($tableCols as $label => $field) {
                $tCells[] = $label . ':';
            }

            $tRows[] = '<thead>
				<tr class="bgColor5" style="font-weight: bold;">
					<td>' . implode(
                '</td>
					<td>', $tCells
            ) . '</td>
				</tr></thead>';

            // Traverse fake records, render icons:
            foreach ($this->testRecords as $row) {
                $tCells = array();
                $icon = IconUtility::getIconImage($tableName, $row, $GLOBALS['BACK_PATH']);
                $tCells[] = $icon;

                $attrib = GeneralUtility::get_tag_attributes($icon);
                $fileName = substr($attrib['src'], strlen($GLOBALS['BACK_PATH']));
                $tCells[] = $fileName;
                $sortRows[] = $fileName;

                foreach ($tableCols as $label => $field) {
                    switch ($label) {
                    case 'Hidden':
                    case 'Access':
                    case 'Del.':
                    case 'Incl.Sub':
                        $tCells[] = $row[$field] ? IconUtility::getSpriteIcon('status-status-checked') : '';
                        break;
                    case 'Endtime':
                    case 'Starttime':
                        $tCells[] = $row[$field] ? BackendUtility::date($row[$field]) : '';
                        break;
                    default:
                        $tCells[] = $row[$field];
                        break;
                    }
                }

                $tRows[] = '
					<tr class="bgColor4">
						<td>' . implode(
                    '</td>
						<td>', $tCells
                ) . '</td>
					</tr>';
            }

            $files = '<ul><li>' . implode('</li><li>', $sortRows) . '</li></ul>';

            // Create table with icons:
            $output =
             '<table class="t3-table">
					' . implode('', $tRows) . '
					<tfoot><tr><td colspan="' . count($tRows) . '"> Unique icons: ' . count($sortRows) . $files . '</td></tr></tfoot>
				</table>';
        }

        return $output;
    }

    /**
     * This will traverse the current pseudo records and replicate them all, adding the new array supplied.
     *
     * @param  array        Array with a field set to value according to what is tested.
     * @return void
     */
    function addTestRecordFields($recFields)    
    {

        $tempArray=array();
        foreach($this->testRecords as $rec)    {
            $tempArray[] = array_merge($rec, $recFields);
        }

        $this->testRecords = array_merge($this->testRecords, $tempArray);
    }

    /**
     * Render the list of checkboxes with options for which kind of renderings of icons should be done:
     *
     * @return string        HTML
     */
    function renderOptionsMatrix($tableName)    
    {
        if (is_array($GLOBALS['TCA'][$tableName])) {

            // Set the default:
            $options=array();

            // Set hidden:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled']) {
                $options['Hidden'] = true;
            }
            // Set starttime:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['starttime']) {
                $options['Starttime'] = true;
            }
            // Set endtime:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['endtime']) {
                $options['Endtime'] = true;
            }
            // Set fe_group:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['fe_group']) {
                $options['Access'] = true;
            }

            // If "pages" table, add "extendToSubpages"
            if ($tableName=='pages') {
                $options['Incl.Sub'] = true;
            }

            // Set "delete" flag:
            if ($GLOBALS['TCA'][$tableName]['ctrl']['delete']) {
                $options['Del.'] = true;
            }

            // Set "_NO_ICON_FOUND" flag:
            $options['_NO_ICON_FOUND'] = true;




            if ($tableName=='pages') {
                $options['Doktype'] = true;
                $options['Module'] = true;
            } elseif (is_array($GLOBALS['TCA'][$tableName]['ctrl']['typeicons'])) {
                $options['TypeIcon'] = true;
            }

            return $options;
        }
    }
} 