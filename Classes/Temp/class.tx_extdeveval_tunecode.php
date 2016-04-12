<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Ren� Fritz (r.fritz@colorcube.de)
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
 * @author    Ren� Fritz <r.fritz@colorcube.de>
 */
class tx_extdeveval_tunecode
{

    /**
     * Main function in this class.
     *
     * @param  string        The absolute path to an existing PHP file which should be analysed
     * @param  [type]                                                                           $extDir: ...
     * @param  [type]                                                                           $parms:  ...
     * @return string        HTML content from the function
     */
    function tune($filepath, $extDir, $parms)    
    {

        $changed = false;
        $output='';

         // Output the file
        if (t3lib_div::_GP('_save_script')) {
            if (@is_file($filepath) && t3lib_div::isFirstPartOfStr($filepath, PATH_site.$extDir)) {
                $output.='<b>SAVED TO: '.substr($filepath, strlen(PATH_site)).'</b>';
                $content=t3lib_div::getUrl($filepath);
                if ($parms['tuneXHTML']) {
                    include PATH_t3lib.'class.t3lib_parsehtml.php';
                    $XHTML_clean = t3lib_div::makeInstance('t3lib_parsehtml');
                    $content=$XHTML_clean->XHTML_clean($content);
                }
                if ($parms['tuneBeautify']) {
                    $content = $this->tuneBeautify($content);
                }
                if ($parms['tuneQuotes']) {
                    $content = $this->tuneQuotes($content);
                }
                $content = $this->substMarkers($content, '', '');
                t3lib_div::writeFile($filepath, $content);
            } else {
                $output.='<b>NO FILE/NO PERMISSION!!!: '.substr($filepath, strlen(PATH_site)).'</b>';
            }
            $output.='<br><br>';
        }

         // Getting the content from the phpfile.
        $content = t3lib_div::getUrl($filepath);

        if ($parms['tuneXHTML']) {

            $trans = array ('<'.'?php' => '###tx_extdeveval_tunecode_PHPB###',
             '?'.'>' => '###tx_extdeveval_tunecode_PHPE###');
            $content = strtr($content, $trans);

            $hash_current = md5($content);
            include PATH_t3lib.'class.t3lib_parsehtml.php';
            $XHTML_clean = t3lib_div::makeInstance('t3lib_parsehtml');
            $content = $XHTML_clean->XHTML_clean($content);

            if ($hash_current != md5($content)) {
                $output.='<img src="'.$GLOBALS['BACK_PATH'].'gfx/zoom2.gif" width="12" height="12" border="0" alt=""> <b>XHTML changes were made.</b><br>';
                $changed = true;
            }
            $trans = array ('###tx_extdeveval_tunecode_PHPB###' => '<'.'?php',
             '###tx_extdeveval_tunecode_PHPE###' => '?'.'>');
            $content = strtr($content, $trans);
        }

        if ($parms['tuneBeautify']) {
            $hash_current = md5($content);
            $content = $this->tuneBeautify($content);

            if ($hash_current != md5($content)) {
                $output.='<img src="'.$GLOBALS['BACK_PATH'].'gfx/zoom2.gif" width="12" height="12" border="0" alt=""> <b>Code beautified</b><br>';
                $changed = true;
            }
        }

        if ($parms['tuneQuotes']) {
            $hash_current = md5($content);
            $content = $this->tuneQuotes($content);

            if ($hash_current != md5($content)) {
                $output.='<img src="'.$GLOBALS['BACK_PATH'].'gfx/zoom2.gif" width="12" height="12" border="0" alt=""> <b>Quotes were changed.</b><br>';
                $changed = true;
            }
        }

        if ($changed) {
            $output.='<b><br>This is the substititions that will be carried out if you press the "Save" button in the bottom of this page:</b>';
        } else {
            $output.='<b><br>Nothing changed.</b>';
        }
        $output.='<hr><pre style="font-size:11px;">'.str_replace("\t", '&nbsp;&nbsp;&nbsp;', $this->substMarkers(htmlspecialchars($content))).'</pre>';

        $output.='<hr>';

        $ret = @eval('return true; '.str_replace('<'.'?', '', str_replace('<'.'?php', '', $this->substMarkers($content, '', ''))));
        if (!$ret) {
            $output.='<span style="color: red; font-weight:bold;">WARNING</span><br><strong>The tuned code may have a syntax error.<br>Save at your own risk!</strong><br>';
        }
        if ($changed) {
            $output.='<input type="submit" name="_save_script" value="SAVE!"><br>';

            $output.='<br><br><b>Instructions:</b><br>';
            $output.='0) Make a backup of the script - what if something goes wrong? Are you prepared?<br>';
            $output.='1) If the substititions shown in red above is OK, then press the "SAVE" button.<br>';
        }
        return $output;
    }


    /**
     * tune php source code
     * done with some regex
     * I'm sure this could be much more elegant and better, but I'm unsure how
     *
     * @param  [type] $content: ...
     * @return [type]        ...
     */
    function tuneQuotes($content) 
    {

        $bm = '###tx_extdeveval_tunecode_WRAP1###';
        $am = '###tx_extdeveval_tunecode_WRAP2###';

        $pregs = array (
          // $var["index"]
         '/\["([^"$\\\\]*)"\]/' => "[".$bm."'$1'".$am."]",
          // ."text"] ."text")
         '/(\.[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*[)\]])/' => "$1".$bm."'$2'".$am."$3",
          // ["text". ("text".
         '/([(\[][[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*\.)/' => "$1".$bm."'$2'".$am."$3",
          // echo("text")
         '/\([[:space:]]*"([^"$\'\\\\]*)"[[:space:]]*\)/' => "(".$bm."'$1'".$am.")",
          // function("text", ...
         '/(\([[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*,)/' => "$1".$bm."'$2'".$am."$3",
          // function(... ,"text")
         '/(,[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*\))/' => "$1".$bm."'$2'".$am."$3",

          // ="text";
         '/(=[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*;)/' => "$1".$bm."'$2'".$am."$3",
          // ="text";
         '/(\.[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*;)/' => "$1".$bm."'$2'".$am."$3",
          // ="text".
         '/(=[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*\.)/' => "$1".$bm."'$2'".$am."$3",
          // ="text")
         '/(=[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*\))/' => "$1".$bm."'$2'".$am."$3",
          // ."text".
         '/(\.[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*\.)/' => "$1".$bm."'$2'".$am."$3",
          // ."text".
         '/(,[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*,)/' => "$1".$bm."'$2'".$am."$3",

          // case "text")
         '/(case[[:space:]]*)"([^"$\'\\\\]*)"([[:space:]]*:)/' => "$1".$bm."'$2'".$am."$3",

          // "index" =>"text"
         '/"([^"$\'\\\\]*)"([[:space:]]*=>[[:space:]]*)"([^"$\'\\\\]*)"/' => $bm."'$1'$2'$3'".$am,
          // =>"text"
         '/(=>[[:space:]]*)"([^"$\'\\\\]*)"/' => "$1".$bm."'$2'".$am,
          // "index" =>
         '/"([^"$\'\\\\]*)"([[:space:]]*=>[[:space:]]*)/' => $bm."'$1'".$am."$2",
        );

        // 		$pregs = array (
        // 			'/"([^"$\\\\]*)"/sU' => $bm."'$1'".$am,
        // 		);
        $content = preg_replace(array_keys($pregs), array_values($pregs), $content);

        return $content;
    }

    /**
     * [Describe function...]
     *
     * @param  [type] $content: ...
     * @return [type]        ...
     */
    function tuneBeautify($content) 
    {
        include_once PATH_tx_extdeveval . 'mod1/class.tx_extdeveval_tunebeautify.php';

        /**
 * @var $beautify tx_extdeveval_tuneBeautify 
*/
        $beautify = t3lib_div::makeInstance('tx_extdeveval_tuneBeautify');
        return $beautify->beautify($content);
    }

    /**
     * [Describe function...]
     *
     * @param  [type] $content: ...
     * @param  [type] $bs:      ...
     * @param  [type] $as:      ...
     * @return [type]        ...
     */
    function substMarkers($content, $bs='<span style="color: red; font-weight:bold;">', $as='</span>') 
    {

        $bm = '###tx_extdeveval_tunecode_WRAP1###';
        $am = '###tx_extdeveval_tunecode_WRAP2###';

        $trans = array ($bm => $bs,
         $am => $as
         );
        $content = strtr($content, $trans);
        return $content;
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_tunecode.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_tunecode.php'];
}
?>

