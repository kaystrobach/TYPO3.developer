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
 * Moving localizations out of ll-XML files and into csh_* extensions.
 *
 * $Id: class.tx_extdeveval_llxmlsplit.php 63721 2012-06-22 14:12:37Z ohader $
 *
 * @author Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
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
 */

/**
 * Moving localizations out of ll-XML files and into csh_* extensions.
 *
 * @author     Kasper Skaarhoj <kasper@typo3.com>
 * @package    TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_llxmlsplit
{

    /**
     * Main function
     *
     * @param  string        Absolute path to the selected PHP file
     * @param  string        Extension dir (local, global, system) relative to PATH_site
     * @return string        HTML content.
     */
    function main($phpFile, $extensionDir)    
    {

        if (@is_file($phpFile)) {
            $fCheck = $this->checkLLfilename($phpFile);
            if (!$fCheck) {

                $fileContent = t3lib_div::xml2array(t3lib_div::getURL($phpFile));
                if (@is_array($fileContent)) {
                    return $this->renderForm($fileContent, $phpFile);
                } else { return 'ERROR: File is not XML: '.$fileContent; 
                }
            } else { return 'ERROR: '.$fCheck; 
            }
        }
    }

    /**
     * Function rendering the status form and executing the splitting operation
     *
     * @param  array        XML file content as array!
     * @param  string        The absolute file name the content is read from
     * @return string        HTML content.
     */
    function renderForm($fileContent,$phpFile)    
    {

         // Meta Data of file
        $content.=
         'Description: <b>'.htmlspecialchars($fileContent['meta']['description']).'</b>';

        if (!@is_writable($phpFile)) {
            return 'ERROR: File "'.$phpFile.'" was not writeable!';
        }


         // Initiate:
        $languages = explode('|', TYPO3_languages);
        $createFile = t3lib_div::_POST('createFile');
        $removePoint = t3lib_div::_POST('removePoint');
        $log = array();
        $saveOriginalBack = false;

        $tableRows = array();
        foreach($languages as $lKey)    {
            if ($lKey !='default') {

                $tableCells = array();

                 // Title:
                $tableCells[] = htmlspecialchars($lKey);

                 // Content:
                $tableCells[] = is_array($fileContent['data'][$lKey]) ?
                  count($fileContent['data'][$lKey]).' labels locally' :
                  htmlspecialchars($fileContent['data'][$lKey]);

                 // Status:
                if (!is_array($fileContent['data'][$lKey]) && strlen(trim($fileContent['data'][$lKey]))) {
                     // An external file WAS configured - we only give status then:
                    $absFileName = t3lib_div::getFileAbsFileName($fileContent['data'][$lKey]);
                    if ($absFileName) {
                        if (@is_file($absFileName)) {
                            $tableCells[] = 'External file exists, OK.';
                        } else {
                            $tableCells[] = '<b>ERROR:</b> External file did not exist, should exist!
											<hr>REMOVE POINTER: <input type="checkbox" name="removePoint['.$lKey.']" value="1" />';

                            if (t3lib_div::_GP('_create_') && $removePoint[$lKey]) {
                                $log[] = 'Removing pointer "'.$fileContent['data'][$lKey].'"';
                                unset($fileContent['data'][$lKey]);
                                $saveOriginalBack = true;
                            }
                        }
                    } else {
                        $tableCells[] = 'External file path did not resolve, maybe extension is not loaded.';
                    }
                } else {
                     // No external file yet:
                    $fileName = t3lib_div::llXmlAutoFileName($phpFile, $lKey);
                      // 	str_replace('###LANGKEY###',$lKey,$fileContent['meta']['ext_filename_template']);
                    $absFileName = t3lib_div::getFileAbsFileName($fileName);
                    if ($absFileName) {
                        if (@is_file($absFileName)) {
                             $tableCells[] = 'External Automatic file exists, OK.';
                        } else {
                            if (t3lib_div::_GP('_create_')) {
                                if ($createFile[$lKey]) {
                                    $OK = 1;
                                    $dirname = dirname($absFileName);
                                    if (!@is_dir($dirname)) {
                                        $OK = 0;

                                        if (t3lib_div::isFirstPartOfStr($dirname, PATH_site.'typo3conf/l10n/')) {
                                            $err = t3lib_div::mkdir_deep(PATH_site.'typo3conf/l10n/', substr($dirname, strlen(PATH_site.'typo3conf/l10n/')));
                                            if ($err) {
                                                $log[] = 'Creating directory '.$dirname.' failed';
                                            } else {
                                                $OK = 1;
                                            }
                                        } else { $log[] = 'Creating directory '.$dirname.' failed (2)'; 
                                        }
                                    }

                                    if ($OK) {

                                         // Creating data for external file:
                                        $extArray = array();

                                         // Setting language specific information in the XML file array:
                                        $extArray['data'][$lKey] = is_array($fileContent['data'][$lKey]) ? $fileContent['data'][$lKey] : array();
                                        $extArray['orig_hash'][$lKey] = is_array($fileContent['orig_hash'][$lKey]) ? $fileContent['orig_hash'][$lKey] : array();
                                        $extArray['orig_text'][$lKey] = is_array($fileContent['orig_text'][$lKey]) ? $fileContent['orig_text'][$lKey] : array();

                                         // Create XML and save file:
                                        $XML = $this->createXML($extArray, true);

                                         // Write file:
                                        t3lib_div::writeFile($absFileName, $XML);

                                         // Checking if the localized file was saved as it should be:
                                        if (md5(t3lib_div::getUrl($absFileName)) == md5($XML)) {
                                            $log[] = 'Saved external XML, validated OK';

                                             // Prepare SAVING original:
                                             // Setting reference to the external file:
                                            unset($fileContent['data'][$lKey]);

                                             // Unsetting the hash and original text for this language as well:
                                            unset($fileContent['orig_hash'][$lKey]);
                                            unset($fileContent['orig_text'][$lKey]);

                                            $saveOriginalBack = true;
                                        } else {
                                            $log[] = 'ERROR: MD5 sum of saved external file did not match XML going in!';
                                        }
                                    }
                                }
                            } else {
                                $tableCells[] = '
									<input type="checkbox" name="createFile['.$lKey.']" value="1" checked="checked" />
								File did not exist, will be created if checkbox selected.<br/>
								('.$fileName.')';
                            }
                        }
                    } else {
                        $tableCells[] = 'Template file path did not resolve, maybe extension is not loaded.';
                    }
                }

                 // Compiling row:
                $tableRows[] = '
					<tr>
						<td>'.implode(
                    '</td>
						<td>', $tableCells
                ).'
						</td>
					</tr>';
            }
        }


        if (t3lib_div::_GP('_create_')) {

             // SAVING ORIGINAL FILE BACK:
            if ($saveOriginalBack) {
                $log[] = 'Saving original back now...';

                $XML = $this->createXML($fileContent);
                t3lib_div::writeFile($phpFile, $XML);

                 // Checking if the main file was saved as it should be:
                if (md5(t3lib_div::getUrl($phpFile)) == md5($XML)) {
                    $log[] = 'Validated OK';
                } else {
                    $log[] = 'ERROR: MD5 sum did not match!!!';
                }
            }

            $content.='<h3>LOG:</h3>'.implode('<br/>', $log).'
					<hr/>
					<input type="submit" name="" value="Back" />';
        } else {
            $content.='<table border="1" cellpadding="1" cellspacing="1">'.implode('', $tableRows).'</table>
					<br/>
					<input type="submit" name="_create_" value="Update" />';
        }

         // Meta Data of file
        $content.=
         '<h3>Meta Data and default labels of file:</h3>'.
         'Meta data:'.
         (is_array($fileContent['meta']) ? Tx_Extdeveval_Compatibility::viewArray($fileContent['meta']) : '').
         'Default labels:'.
         (is_array($fileContent['data']['default']) ? Tx_Extdeveval_Compatibility::viewArray($fileContent['data']['default']) : '');



        return $content;
    }











    /****************************
    *
    * Helper functions
    *
    ****************************/

    /**
     * Checking for a valid locallang*.xml filename.
     *
     * @param  string        Absolute reference to the xml locallang file.
     * @return string        Empty (false) return value means "OK" while otherwise is an error string.
     */
    function checkLLfilename($phpFile)    
    {
        $basename = basename($phpFile);
        if (!t3lib_div::isFirstPartOfStr($basename, 'locallang') || preg_match('#\.[a-z][a-z]\.xml$#', $basename)) {
            return 'Filename didn\'t start with "locallang" or had a language suffix.';
        }
    }


    /**
     * Creates XML string from input array
     * (Copy from EXT:llxmltranslate/mod1/index.php)
     *
     * @param  array        locallang-XML array
     * @param  boolean        If set, then the XML will have a document tag for an external file.
     * @return string        XML content
     * @see    EXT:llxmltranslate/mod1/index.php
     */
    function createXML($outputArray,$ext=false)    
    {

         // Options:
        $options = array(
         // 'useIndexTagForAssoc'=>'key',
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
        $XML.= t3lib_div::array2xml($outputArray, '', 0, $ext ? 'T3locallangExt' : 'T3locallang', 0, $options);

        return $XML;
    }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_llxmlsplit.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_llxmlsplit.php'];
}
?>