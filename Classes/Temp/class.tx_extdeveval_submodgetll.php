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
 * Class for substituting empty getLL() function calls with ones with a key (auto-made) and the value formatted for entry into a locallang file
 *
 * $Id: class.tx_extdeveval_submodgetll.php 43222 2011-02-07 15:40:53Z ohader $
 *
 * @author Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *   53: class tx_extdeveval_submodgetll
 *   66:     function analyseFile($filepath,$extDir)
 *  182:     function llKey($f,$value)
 *  208:     function makeLLArrayPart($llArr)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 */

/**
 * Class for substituting empty getLL() function calls with ones with a key (auto-made) and the value formatted for entry into a locallang file
 *
 * @author     Kasper Skaarhoj <kasper@typo3.com>
 * @package    TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_submodgetll
{

    var $keepValue = 0;        // If set, the getLL functions (for pi_ at least) will have their second parameter set to the value, otherwise blank (recommended)
    var $module=1;            // Whether the class looks for "$this->pi_getLL(" or "$LANG->getLL"
    var $prefix='';

    /**
     * Main function in this class.
     *
     * @param  string        The absolute path to an existing PHP file which should be analysed
     * @param  string        The local/global/system extension main directory relative to PATH_site - normally set to "typo3conf/ext/" for local extensions
     * @return string        HTML content from the function
     */
    function analyseFile($filepath,$extDir)    
    {
         // Getting the content from the phpfile.
        $content = t3lib_div::getUrl($filepath);
        $this->prefix = strtolower(preg_replace('#[^[:alnum:]]*#', '', t3lib_div::_GP('prefix')));

         // String to explode filecontent with + exploding
        if (strstr($content, '$LANG->getLL(')) {
            $expStr = '$LANG->getLL(\'\',\'';
            $expStrSubst = '$LANG->getLL(\'%s\',\'1';
        } elseif (strstr($content, '$this->pi_getLL(')) {
            $expStr = '$this->pi_getLL(\'\',\'';
            $expStrSubst = '$this->pi_getLL(\'%s';
        } else { return '<strong>Could not detect any getLL() functions in the file! Sorry.</strong>'; 
        }

        $fileParts = explode($expStr, $content);

         // Init arrays/vars
        $valueArray=array();
        $mappingArray=array();
        $splitParts = array();
        $splitParts[]=$fileParts[0];
        $f='';

         // For each element in exploded result, find the function:
        foreach($fileParts as $k => $v)    {
            // Try to find function definition - used to create meaningful prefixes:
            $reg =array();
            preg_match('#.*[[:space:]]+function[[:space:]]+([[:alnum:]_]+)[[:space:]]*\(#',  $v, $reg);
            if ($reg[1]) {    $f=$reg[1];    // setting the most RECENT function name if new function name is found.
            }
             // Processing splitted content
            if ($k) {    // only >0 keys...
                $subP = preg_split("|[^\\\\]')|", $v, 2);
                if (count($subP)>1) {
                    $value = substr($v, 0, -(strlen($subP[1])+2));        // Get the value (without stripping "'" chars! - which is the point to keep since later when we write the locallang array we don't have to add them!
                    $splitPartsKey = count($splitParts);        // This is supposed to point to the key in splitParts array for this entry!

                    $valueArray[] = array('key' => $splitPartsKey, 'value' => $value, 'function' => $f);

                    $splitParts[]=$expStr.substr($v, 0, -strlen($subP[1]));
                    $splitParts[]=$subP[1];
                } else {
                    $splitParts[]='';
                    $splitParts[]=$expStr.$v;
                }
            }
        }

         // OK, so we have gathered information in $valueArray now. Lets process that:
        $llArray=array();
        foreach ($valueArray as $k => $v)    {
             // Get llKey
            $llKey = $this->llKey($v['function'], $v['value']);

             // If set in llArray AND the values does NOT match, add a random prefix.
            if (isset($llArray[$llKey]) && strcmp($llArray[$llKey], $v['value'])) {
                $llKey.='_'.substr(md5(microtime()), 0, 4);
            }

             // Set value (which IS addslashes for "'"... since we didn't remove them)
            $llArray[$llKey] = $v['value'];

             // Then we change the part of splitParts where this getLL function was found.
            $splitParts[$v['key']]=sprintf($expStrSubst, $llKey).
              ($this->keepValue ? $llArray[$llKey] : '').
              "')";
        }

        $output='';
         // Output the file
        if (t3lib_div::_GP('_save_script')) {
            if (@is_file($filepath) && t3lib_div::isFirstPartOfStr($filepath, PATH_site.$extDir)) {
                $output.='<b>SAVED TO: '.substr($filepath, strlen(PATH_site)).'</b>';
                t3lib_div::writeFile($filepath, implode('', $splitParts));
            } else {
                $output.='<b>NO FILE/NO PERMISSION!!!: '.substr($filepath, strlen(PATH_site)).'</b>';
            }
        } else {
            $lines=array();
            foreach($splitParts as $k => $v)    {
                if ($k%2) {
                    $lines[]='<span style="color: red; font-weight:bold;">'.htmlspecialchars($v).'</span>';
                } else {
                    if (strlen($v)>1000) {
                        $lines[]=htmlspecialchars(substr($v, 0, 450)).chr(10).'<span style="color:green; font-weight:bold;">[...]</span>'.htmlspecialchars(chr(10).substr($v, -500));
                    } else {
                        $lines[]=htmlspecialchars($v);
                    }
                }
            }
            $output.='
			<b><br>This is the substititions that will be carried out if you press the "Save" button in the bottom of this page:</b><hr>
			<pre style="font-size:11px;">'.str_replace(chr(9), '&nbsp;&nbsp;&nbsp;', implode('', $lines)).'</pre>';
        }

        $output.='<hr>';
        $output.='<input type="submit" name="_save_script" value="SAVE!"><br>';
        $output.='<input type="text" name="prefix" value="'.htmlspecialchars(t3lib_div::_GP('prefix')).'" maxlength="10"><input type="submit" name="_" value="Update with prefix">';

        $output.='<br><br><b>Instructions:</b><br>';
        $output.='0) Make a backup of the script - what if something goes wrong? Are you prepared?<br>';
        $output.='1) If the substititions shown in red above is OK, then press the "SAVE" button.<br>';
        $output.='2) After the file is saved you MUST add the key/value pairs from the textarea below to the locallang-file used by the PHP-script which was modified.<br>';
        $output.='<textarea rows="30" wrap="off" '.$GLOBALS['TBE_TEMPLATE']->formWidthText(48, '', 'off').'>'.
        htmlspecialchars($this->makeLLArrayPart($llArray)).chr(10).chr(10).chr(10).
        htmlspecialchars($this->makeLLArrayPart_xml($llArray)).
        '</textarea>';


        return $output;
    }

    /**
     * Generates a suggested locallang key based on input function name and the value
     *
     * @param  string $f:     Function name. Will use first 10 chars.
     * @param  string $value: The label value. Will take the first three words and use.
     * @return string        Output suggestion for locallang key
     */
    function llKey($f,$value)    
    {
        $llKey='';
        $llKey.= $this->prefix ? $this->prefix.'_' : '';
        $llKey .= strtolower($f ? substr(preg_replace('#[^[:alnum:]]*#', '', $f), 0, 10) . '_' : '');    // First, the prefix from function (max 20 chars)

        $parts = t3lib_div::trimExplode(' ', preg_replace('#[^[:alnum:]]#', ' ', strtolower($value)), 1);

        $vAcc='';
        foreach($parts as $k => $vParts)    {
            if ($k) {
                $vAcc.= strtoupper(substr($vParts, 0, 1)).substr($vParts, 1);
            } else { $vAcc.= $vParts; 
            }
            if ($k==3) {
                break;
            }
        }

        return $llKey.$vAcc;
    }

    /**
     * Compiles a part of a PHP-array structure from the input array of locallang key/value pairs
     *
     * @param  array $llArr: locallang key/value pairs (where any single-quotes in the value would already be escaped!)
     * @return string        String ready to insert into a locallang files definition of the "default" language.
     */
    function makeLLArrayPart($llArr)    
    {
        $lines = array();
        foreach($llArr as $k => $v)    {
            $lines[]="
		'".$k."' => '".$v."',";
        }
        return implode('', $lines);
    }

    /**
     * Compiles a part of a PHP-array structure from the input array of locallang-XML key/value pairs
     *
     * @param  array $llArr: locallang key/value pairs (where any single-quotes in the value would already be escaped!)
     * @return string        String ready to insert into a locallang files definition of the "default" language.
     */
    function makeLLArrayPart_xml($llArr)    
    {
        $lines = array();
        foreach($llArr as $k => $v)    {
            $lines[]='
			<label index="'.htmlspecialchars(stripslashes($k)).'">'.htmlspecialchars(stripslashes($v)).'</label>';
        }
        return implode('', $lines);
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_submodgetll.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_submodgetll.php'];
}
?>