<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Kasper Skårhøj (kasper@typo3.com)
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
 * Contains a class, tx_extdeveval_tmpl, which can dump the template tables
 *
 * $Id: class.tx_extdeveval_tmpl.php 653 2004-06-24 09:48:11Z kasper $
 *
 * @author Kasper Skaarhoj <kasper@typo3.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *   53: class tx_extdeveval_tmpl
 *   65:     function main()
 *  129:     function getTemplateOutput($row)
 *
 * TOTAL FUNCTIONS: 2
 * (This index is automatically created/updated by the extension "extdeveval")
 */


/**
 * Class for dumping template tables.
 *
 * @author     Kasper Skaarhoj <kasper@typo3.com>
 * @package    TYPO3
 * @subpackage tx_extdeveval
 */
class tx_extdeveval_tmpl
{

    // Internal, GPvars:
    var $table;
    var $hide;
    var $uid;

    /**
     * Main function, launching the dump functionality.
     *
     * @return string        HTML content for the module.
     */
    function main()    
    {

         // Set GPvar:
        $this->table = t3lib_div::_GP('table');
        $this->uid = t3lib_div::_GP('uid');
        $this->hide = t3lib_div::_GP('hide');

         // Select / format content to display:
        if ($this->table=='sys_template' || $this->table=='static_template') {

            $where = ($this->table=='sys_template') ? 'NOT deleted' : '1=1';
            if (intval($this->uid))    $where.=' AND uid='.intval($this->uid);
            $query = 'SELECT uid,pid,constants,config,title FROM '.addslashes($this->table).' WHERE '.$where.' ORDER BY title';

            $res = mysql(TYPO3_db, $query);
            $out='';
            while($row = mysql_fetch_assoc($res))    {
                $out.= $this->getTemplateOutput($row);
            }

             // Output and exit, if set:
            if ($this->hide) {
                echo'<pre>'.htmlspecialchars($out).'</pre>';
                exit;
            }
        }


        // Create output:
        $content.='
			<select name="table">
				<option value="static_template"'.($this->table=='static_template' ? 'selected="selected"' :'').'>static_template</option>
				<option value="sys_template"'.($this->table=='sys_template' ? 'selected="selected"' :'').'>sys_template</option>
			</select><br />

			<p>Specific Uid: </p>
			<input type="text" name="uid" size="5" /><br />

			<p>Hide this control:</p>
			<input type="checkbox" name="hide" value="1" /><br />

			<input type="submit" />
			<hr />';

        if ($out) {
            $content.='

			<p>MD5: '.md5($out).'</p>
			<hr />

			<pre>'.htmlspecialchars($out).'</pre>
			';
        }

         // Return content:
        return $content;
    }

    /**
     * Renders the content of a static / sys template row.
     *
     * @param  array        Record from static_template or sys_template table.
     * @return string        Plain text content. Must be htmlspecialchars()'ed before output.
     */
    function getTemplateOutput($row)    
    {
        $title = 'TITLE: '.$row['title'].'                                                                      ';
        $info = 'PID: '.$row['pid'].'  UID: '.$row['uid'].'                                                                    ';

        $out='';
        $out.="[*******************************************************************]\n";
        $out.="[*** ".substr($title, 0, 59)." ***]\n";
        $out.="[*** ".substr($info, 0, 59)." ***]\n";
        $out.="[*******************************************************************]\n";
        $out.="[***                          CONSTANTS                          ***]\n";
        $out.="[*******************************************************************]\n";

        $out.=$row['constants'];
        $out.="\n\n";

        $out.="[*******************************************************************]\n";
        $out.="[***                           SETUP                             ***]\n";
        $out.="[*******************************************************************]\n";

        $out.=$row['config'];
        $out.="\n\n";

        return $out;
    }
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_tmpl.php']) {
    include_once $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_tmpl.php'];
}
?>