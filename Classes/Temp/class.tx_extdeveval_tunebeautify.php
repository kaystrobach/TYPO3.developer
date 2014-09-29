<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Jens Bierkandt <jens@bierkandt.org>                        |
// +----------------------------------------------------------------------+
//
// $Id: class.tx_extdeveval_tunebeautify.php 43358 2011-02-09 18:33:19Z ohader $
// The header is for PEAR, if someday this code is put inside the rep...
/**
* Php Beautify: A tool to beautify php source code
*
* Copyright 2002, Jens Bierkandt, jens@bierkandt.org
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*
*
*
* !!!!!!!!
* slightly modified for TYPO3 by Ren� Fritz <r.fritz@colorcube.de>
*
*
*
*
*
* @package php_beautify
* @author Jens Bierkandt <jens@bierkandt.org>
*/
////////////////////////////////
// Main
////////////////////////////////

/**
* Require for PEAR class
*/
require_once "PEAR.php";
DEFINE('BEAUT_BRACES_PEAR', '0');
DEFINE('BEAUT_BRACES_C', '1');
DEFINE('BEAUT_INDENT_TYPE', 's'); // t for tabs
DEFINE('BEAUT_VERSION', '0.5.0, 08.05.2003');

/**
 * Class to beautify php code
 *
 */
class tx_extdeveval_tune_phpBeautify extends PEAR {
	//public variables
	/**
	* Spaces to indent
	* @var int
	*/
	var $indent_width = 4;
	/**
	* Wrap or not the code at line defined by {@link $max}
	* @var bool
	*/
	var $max_line = FALSE;
	/**
	* Max chars per line, if {@link $max_line} is true
	* @var int
	*/
	var $max = 40;
	/**
	* If true, delete empty lines
	* @var bool
	*/
	var $del_line = FALSE;
	/**
	* Highlight for the html version of the beautify code
	* @var bool
	*/
	var $highlight = FALSE;
	/**
	* Type of braces parse. Can be BEAUT_BRACES_PEAR or BEAUT_BRACES_C
	* @var int
	*/
	var $braces = BEAUT_BRACES_PEAR;
	/**
	* Name of the file to read. Can be "php://stdin".
	* @var string
	*/
	var $file = '';
	/**
	* Find and list the functions at the beggining of the script
	* @var bool
	*/
	var $find_functions = false;
	/**
	* Verify the integrity of the beautify version
	* @var bool
	*/
	var $verify = true;
	/**
	* default indentation mode
	*/
	var $indent_mode = BEAUT_INDENT_TYPE;
	/**
	* Indent the long comments.
	* Add a space to all long comment lines
	* so, the text start in the column of the *
	* Idea of Michael H.E. Roth <mher@users.sourceforge.net>
	* @var bool
	*/
	var $indent_long_comments = false;
	//friend variables (?)
	var $version = BEAUT_VERSION;
	//private variables
	/**
	* Set to true when the parser found a double quoted string
	* @var bool
	* @access private
	*/
	var $_marks = false;
	/**
	* Set to true when the parser found a single quoted string
	* @var bool
	* @access private
	*/
	var $_marks1 = false;
	/**
	* @var int
	* @access private
	*/
	var $_new_line_counter = 0;
	/**
	* @var int
	* @access private
	*/
	var $_indent = 0;
	/**
	* Keeps the string for output
	* @var string
	* @access private
	*/
	var $_allstr = '';
	/**
	* @var bool
	* @access private
	*/
	var $_long_comment = false;
	/**
	* @var bool
	* @access private
	*/
	var $_long_comment_first = false;
	/**
	* @var bool
	* @access private
	*/
	var $_long_comment_last = false;
	/**
	* var bool
	* @access private
	*/
	var $_do_indent = false;
	/**
	* @var bool
	* @access private
	*/
	var $_no_beautify = false;
	/**
	* var string
	* @access private
	*/
	var $_ehtml = '';
	/**
	* var string
	* @access private
	*/
	var $_outstr = '';
	/**
	* var bool
	* @access private
	*/
	var $_comment = false;
	/**
	* var bool
	* @access private
	*/
	var $_brackets = 0;
	/**
	* var bool
	* @access private
	*/
	var $_indent_next = false;
	/**
	* Keeps the original file for verification
	* @var string
	* @access private
	*/
	var $_original = ''; // Keeps the original file for verification
	/**
	 * Constructor
	 * Create a phpBeautify object, based on an array of settings.
	 * This array have the keys for the names of the public variables
	 * of the class.
	 *
	 * @param	array		an array of settings
	 * @return	[type]		...
	 * @author Claudio Bustos
	 */
	function phpBeautify($settings) {
		// seteo
		$this->PEAR();
		$this->setErrorHandling(PEAR_ERROR_DIE, E_USER_ERROR);
		//ingreso las variables en setting
		extract($settings, EXTR_OVERWRITE);
		if (isset($indent_width)) {
			$this->indent_width = $indent_width;
		}
		if (isset($indent_mode)) {
			$this->indent_mode = $indent_mode;
		}
		if (isset($max_line)) {
			$this->max_line = $max_line;
		}
		if (isset($max)) {
			$this->max = $max;
		}
		if (isset($del_line)) {
			$this->del_line = $del_line;
		}
		if (isset($highlight)) {
			$this->highlight = $highlight;
		}
		if (isset($braces)) {
			$this->braces = $braces;
		}
		if (isset($file)) {
			$this->file = $file;
		}
		if (isset($find_functions)) {
			$this->find_functions = $find_functions;
		}
		if (isset($verify)) {
			$this->find_functions = $verify;
		}
		if (isset($indent_long_comments)) {
			$this->indent_long_comments = $indent_long_comments;
		}
	}
	/**
	 * Returns the Version of the program
	 *
	 * @return	string
	 */
	function getVersion() {
		return BEAUT_VERSION;
	}
	/**
	 * Returns a string with the beautify php code.
	 * To get a Html version of the code, use {@link toHtml()}
	 *
	 * @return	mixed		you can obtain the string with the code or a PEAR_ERROR is something bad happens.
	 */
	function beautify() {
		$this->_main();
		$rs = $this->_output();
		if ($this->verify) {
			$this->_verify($rs);
		}
		if ($this->find_functions) {
			$this->_findfunctions($rs);
		}
		return $rs;
	}
	/**
	 * Send to the screen the phpCode in HTML format (how I say that in english?)
	 * Dependly of {@link $highlight}, you get a pure text version or with colors.
	 * To get a string with the code, use {@link beautify()}
	 *
	 * @return	[type]		...
	 */
	function toHTML() {
		if ($this->highlight) {
			header('Content-Type: text/html');
			highlight_string($this->beautify());
		} // endif
		else
			{
			//header('Content-Type: text/plain');
			echo "<code><pre>\n".htmlentities($this->beautify())."\n</pre></code>";
		}
	}
	/**
	* Open a file or the standard input for _main() function
	* @access private
	* @param string filename
	* @return resource a file pointer to filename
	*/
	function &_open_file($file) {
		if (!file_exists($file) AND $file  != 'php://stdin') {
			return $this->raiseError('File '.$file." does not exist\n", 1);
		}
		$fp = fopen($file, 'r');
		if (!is_resource($fp)) {
			return $this->raiseError('Could not open '.$file."file \n", 1);
		} else {
			return $fp;
		}
	}
	/**
	 * Parse the code.
	 * Returns TRUE is everything is OK or a PEAR_ERROR object instead
	 *
	 * @param	[type]		$code: ...
	 * @return	mixed		can be a bool or a PEAR_ERROR
	 * @access private
	 * @author Jens Bierkandt
	 */
	function _main($code) {
/*
This is the only place where the original class is modified

Was necessary because a file was readed but I want to pass a string

		// open the file
		$fp = $this->_open_file($this->file);
		if (PEAR::isError($fp)) {
			return $fp;
		}
		// Main loop
		while (!feof($fp)) {
			// Get a line from the file
			$str = fgets($fp, 2500);
*/
		if(!is_array($code)) {
			$code = explode ("\n", $code);
		}

		while (list(,$str)=each($code)) {


			// check if we are allowed to process line
			if (trim($str) == '// BEAUTIFY') {
				// Do beautify :-)
				$this->_no_beautify = false;
				continue;
			}
			if (trim($str) == '// NO_BEAUTIFY') {
				// Do not beautify :-(
				$this->_no_beautify = true;
				continue;
			}

			$this->_original  .= $str;

			// End of ehtml?
			if (trim($str) == $this->_ehtml && $this->_ehtml != '') {
				$this->_allstr .= $str;
				$this->_ehtml = '';
				continue;
			}

			// Still in ehtml mode?
			if ($this->_ehtml) {
				$this->_allstr .= $str;
				continue;
			}

			//skip no php and // NO_BEAUTIFY lines
			if ($this->_no_beautify AND trim($str)  != "<?" AND trim($str)  != '<?php') {
				$this->_allstr  .= $str;
				continue;
			}
			$this->_outstr = '';
			$this->_comment = false;
			$this->_brackets = 0;
			// Kill nasty tabs
			$str = trim(str_replace("\t", ' ', $str));
			// Don't delete empty lines if required by user
			if (!$this->del_line)
				if (preg_match("/^(\s)*$/", $str)  != 0) {
				$this->_out(' ');
				continue;
			}
			if ($this->_long_comment) {
				$this->_comment = true;
			}
			// Extract the characters in an array
			$a = null;
			for ($i = 0; $i < strlen($str); $i++) {
				$a[$i] = substr($str, $i, 1);
			}
			// Do pre-processing on every char
			for ($i = 0; $i < strlen($str); $i++) {
				// Check, if we deal with php-code
				if (!$this->_new_line_counter and ($i+1) < sizeof($a)) {
					if ($a[$i+1] == "?" AND $a[$i] == '<') {
						if ($this->_outstr) $this->_out(trim($this->_outstr));
							$this->_out('<?php');
						$this->_indent++;
						$this->_new_line_counter++;
						if (($i+4) < sizeof($a)) {
							if ($a[$i+2] == "p" AND $a[$i+3] == "h" AND $a[$i+4] == 'p')
								$i = $i+3;
						}
						$i++;
						$this->_no_beautify = false;
						continue;
					}
				}

				// Kill all chars below 32
				if (ord($a[$i]) < 32) $a[$i] = ' ';
				if (!$this->_marks AND !$this->_marks1) {
					if ($i > 0) {
						// check if line is long comment initiated with /*
						if ($a[$i]=="*" AND $a[$i-1]=="/" AND !$this->_comment) {
						$this->_long_comment=true;
						$this->_long_comment_first = true;
						$this->_comment=true;
						}
						// check if line is finishing long comment with */
						if ($a[$i]== "/" AND $a[$i-1] == '*') {
							$this->_long_comment = false;
							$this->_long_comment_last = true;
						}
						// check if line is comment with //
						if ($a[$i] == "/" AND $a[$i-1] == "/" or $this->_comment) {
							$this->_comment = true;
							$this->_outstr  .= $a[$i];
							continue;
						}
					} // end if ($i > 0)
					// check if line is comment with old #
					if ($a[$i] == '#') {
						$this->_comment = true;
						$this->_outstr  .= $a[$i];
						continue;
					}
					// add space before chars = < >
					if ($i > 0 AND !$this->_comment) {
						if (($a[$i] == "=" OR $a[$i] == "<" OR $a[$i] == ">" OR $a[$i] == '*')
							AND preg_match("/([ |\!|\=|\.|\<|\>|\-|\+|\*|\/]+)/", $a[$i-1]) == 0) {
							$this->_outstr  = rtrim($this->_outstr).' ';
						}
					}
					// add space behind =
					if ($i > 0 AND !$this->_comment) {
						if (($a[$i-1] == "="OR $a[$i-1] == '*')
							AND preg_match('/([ |=|>]+)/', $a[$i]) == 0) {
							$this->_outstr  = rtrim($this->_outstr).' ';
						}
					}
					// add space before two-digit-chars && || !
					if (($i+2) < sizeof($a) AND !$this->_comment) {
						if ($a[$i+1] == "&"AND $a[$i] == "&" AND $a[$i+2]  != ' ') {
							$this->_outstr  = rtrim($this->_outstr).' ';
						}
						if ($a[$i+1] == "|" AND $a[$i] == "|" AND $a[$i+2]  != ' ') {
							$this->_outstr  = rtrim($this->_outstr).' ';
						}
					}
				} //end if ($no_mark)
				// ignore all in between ""
				// echo$a[$i].'|';
				if ($a[$i] == "\"" AND !($this->_marks) AND !($this->_comment) AND !($this->_marks1)) {
					//turn on
					$this->_marks = true;
					if ($i > 0 AND $a[$i-1] == chr(92)) {
						$this->_marks = false;
						// echo"!off1!";
					}
					// echo"<on1>";
				} else {
					if ($a[$i] == "\"" AND $this->_marks AND !($this->_comment) AND !($this->_marks1)) {
						//turn off
						$this->_marks = false;
						if ($i > 0 AND $a[$i-1] == chr(92) and $a[$i-2]!=chr(92)) {
							$this->_marks = true;
							// echo"!on1!";
						}
						// echo"<off1>";
					}
				}
				// ignore all in between ' '
				if ($a[$i] == chr(39) AND !($this->_marks) AND !($this->_marks1) AND !($this->_comment)) {
					//turn on
					$this->_marks1 = true;
					if ($i > 0 AND $a[$i-1] == chr(92)) {
						$this->_marks1 = false;
						//      $this->_outstr.='off2';
					}
					//else $this->_outstr.='on2';
				} else {
					if ($a[$i] == chr(39) AND !($this->_marks) AND $this->_marks1 AND !($this->_comment)) {
						//turn off
						$this->_marks1 = false;
						if ($i > 0 AND $a[$i-1] == chr(92)) {
							$this->_marks1 = true;
							//        $this->_outstr.='on2';
						}
						//else $this->_outstr.='off2';
					}
				}
				// do further processing if code is not ignored
				if (!($this->_marks) AND !($this->_marks1) AND !($this->_comment)) {

					// check if we have a "<<<"
					if ($i+3 < sizeof($a)) {
						if ($a[$i] == "<" AND $a[$i+1] == "<" AND $a[$i+2] == '<') {

							// rest of line is the trigger for the end
							for ($z=$i+3;$z<sizeof($a);$z++) {
								$this->_ehtml .= $a[$z];
							}
							$this->_out($this->_outstr.'<<<'.$this->_ehtml);
							$this->_ehtml = trim($this->_ehtml);
							continue 2;
						}
					}

					// add space behind chars , < >
					if ($i+1 < sizeof($a)) {
						if (($a[$i] == "," OR $a[$i] == "<" OR $a[$i] == '>')
							AND preg_match('/([ |!|=|.|<|>]+)/', $a[$i+1]) == 0) {
							$this->_outstr  .= $a[$i].' ';
							continue;
						}
					}
					// add spaces before chars . ! + - / * (if they belong to math function)
					if ($i+1 < sizeof($a)) {
						if (($a[$i] == "." OR $a[$i] == "!" OR $a[$i] == "+" OR $a[$i] == "-" OR $a[$i] == "/" OR $a[$i] == '*')
							AND preg_match("/([\=]+)/", $a[$i+1]) == 1) {
							$this->_outstr  = rtrim($this->_outstr).' ';
						}
					}
					// add space behind chars && ||
					if ($i > 0 and ($i+1) < sizeof($a)) {
						if ($a[$i-1] == "&" and $a[$i] == "&" and $a[$i+1]  != ' ') {
							$this->_outstr  .= $a[$i].' ';
							continue;
						}
						if ($a[$i-1] == "|" and $a[$i] == "|" and $a[$i+1]  != ' ') {
							$this->_outstr  .= $a[$i].' ';
							continue;
						}
					}
					if (($i+1) < sizeof($a)) {
						// check if php code ends
						if ($a[$i+1] == ">" AND $a[$i] == '?') {
							$this->_new_line_counter = 0;
							if ($this->_outstr) $this->_out(trim($this->_outstr));
								$this->_indent--;
							$this->_out('?>');
							//<?
							$i++;
							$this->_no_beautify = true;
							continue;
						}
						// Delete some odd spaces before ')'
						if ($a[$i] == " " AND $a[$i+1] == ')') {
							$this->_outstr  .= $a[$i];
							while ($a[$i+1] == ' ') $i++;
							$this->_brackets--;
							continue;
						}
						// Delete some odd spaces behind '('
						if (($a[$i] == '(') AND $a[$i+1] == ' ') {
							$this->_outstr  .= $a[$i];
							while ($a[$i+1] == ' ') $i++;
							$this->_brackets++;
							continue;
						}
					}
					// check, if ; is last letter in line, check if ; is from for function
					if ($a[$i] == '(') $this->_brackets++;
					if ($a[$i] == ')') $this->_brackets--;
					/*
					if (substr($this->_outstr, 0, 3) == 'for') {
					$this->_brackets = true;
					}
					*/
					// needed for for(;;;)
					// this prevent breaking direct calling function inside a
					// class
					// example  className::function()
					if (($i+2) < sizeof($a)) {
						if ($a[$i] == ":" and $a[$i+1] == ':') {
							$this->_outstr  .= $a[$i].$a[$i+1].$a[$i+2];
							$i  += 2;
							continue;
						}
					}
					if ((($a[$i] == ';') or ($a[$i] == ':')) AND !($this->_brackets)) {
						// echo $a[$i];
						if ($i+2 < sizeof($a)) {
							if ($a[$i+1] == "/" OR $a[$i+2] == '/') {
								// if comment in same line
								$this->_outstr  .= $a[$i];
								continue;
							}
							if ($a[$i] == ';') {
								// add newline
								$this->_out(trim($this->_outstr).$a[$i]);
								continue;
							}
							// If a tertinary conditional operator is found:
							if (preg_match('#\$[^=]+=[^?]+\?[^?:]+#', $this->_outstr)) {
								$this->_outstr  .= $a[$i];
								continue;
							}
						}
						$this->_out($this->_outstr.$a[$i]);
						continue;
					}
					// if for (;;;)
					if ($this->_brackets AND ($a[$i] == ';') AND ($a[$i]  != ' ')) {
						$this->_outstr  .= '; ';
						continue;
					}
					// check if }
					if ($a[$i] == '}') {
						if ($i > 0) $this->_out(trim($this->_outstr)); // there was code before bracket->newline
						if ($i < sizeof($a)-1) {
							if ($a[$i+1] == ';') {
								$this->_indent--;
								$this->_outstr  .= '}';
								continue;
							}
						}
						$this->_indent--;
						if ($i < sizeof($a)-3) // check if something like } //
						{
							if ($a[$i+3] == "/" AND $a[$i+2] == '/') {
								$this->_comment = true;
								$this->_outstr  .= $a[$i];
								continue;
							}
						}
						$this->_out('}');
						continue;
					}
					// check if {
					if ($a[$i] == '{') {
						if ($i > 0) $this->_out(trim($this->_outstr)); // there was code before bracket->newline
						$this->_out('{');
						$this->_indent++;
						continue;
					}
					// check for double spaces
					$checkstr = substr($this->_outstr, strlen($this->_outstr)-1);
					if (($a[$i] == ' ') AND ($checkstr == ' ')) {
						$this->_outstr = substr($this->_outstr, 0, strlen($this->_outstr)-1);
					}

					// change ( ! to (!
					$this->_outstr = preg_replace("/(\(\s!)+/", '(!', $this->_outstr);
				}
				$this->_outstr  .= $a[$i];
			}
			$this->_out(trim($this->_outstr));
		} // end main loop
		return TRUE;
	}
	////////////////////////////////
	// Internal functions
	////////////////////////////////
	/**
	 * Format and add output to $this->_allstr
	 *
	 * @param	string		a outstr string
	 * @return	[type]		...
	 * @access private
	 * @author Jens Bierkandt
	 */
	function _out($outstr) {
		if ($this->del_line) $outstr = trim($outstr);
			if ($outstr == '') return;
		// additional beautifying
		$outstr = preg_replace('/( )*->( )*/', '->', $outstr);
		//->without surrounding spaces
		// space behind some key words
		$outstr = preg_replace("/^if\s*\(/", 'if (', $outstr);
		$outstr = preg_replace("/^while\s*\(/", 'while (', $outstr);
		// no free brackets
		// TODO: check, that ( ) doesn't change to ()
		//$outstr = preg_replace("/\(( )+/", '(', $outstr);
		//$outstr = preg_replace("/( )+\)/", ')', $outstr);
		// linebreak after $max_line
		if ($this->max_line) {
			if (strlen($outstr)+strlen($this->_getindent()) > $this->max_line) {
				$b = 0;
				while (strlen($outstr)+strlen($this->_getindent()) > $this->max_line) {
					if ($b > 0) $this->_indent++;
					$subout = substr($outstr, 0, $this->max_line-strlen($this->_getindent()));
					$end = strrpos($subout, ' ');
					if ($end == false) // check if breakable by a space
					{
						$end = $this->max_line-strlen($this->_getindent()); // if not, break it after $max_line for now
					}
					$subout = substr($subout, 0, $end);
					$this->_allstr  .= $this->_getindent().trim($subout)."\n";
					$outstr = substr($outstr, $end);
					if ($outstr == '') {
						if ($b > 0) $this->indent--;
						continue 2;
					}
					if ($b > 20) {
						$this->_indent--;
						continue; // just in case we got stuck ;-)
					}
					if ($b > 0) $this->_indent--;
					$b++;
				}
				if ($b > 0) $this->_indent++;
				$this->_allstr  .= $this->_getindent().trim($outstr)."\n";
				if ($b > 0) $this->_indent--;
				$this->_outstr = '';
				return;
			}
		}
		//check if newline is requested
		//add before char
		if ($this->_indent_next) $this->_indent++;
		if ($this->_do_indent) $this->_allstr  .= $this->_getindent();
			$this->_allstr  .= $outstr;
		if ($this->_indent_next) {
			$this->_indent--;
			$this->_indent_next = 0;
		}
		//check if newline is requested
		//after char
		$this->_do_indent = 0;
		if ((preg_match("/(;|,|:|\s|{|}|\(|\)|else|do)$/", $outstr)) OR $this->_ehtml OR $this->_comment OR !$this->_new_line_counter or $this->max_line OR $this->_marks OR $this->_marks1) {
			$this->_allstr  .= "\n";
			$this->_do_indent = 1;
		} else {
			$this->_allstr  .= ' ';
		}
		// Indent one line if expression is without brackets or inside quotation makrs
		if (!($this->_comment) AND ((preg_match("/^(if \(.*\)|else)$/", $this->_outstr)) OR preg_match("/(, *)$/", $this->_outstr) OR $this->_marks OR $this->_marks1)) {
			$this->_indent_next = true;
		}
		$this->_outstr = '';
		return;
	}
	/**
	 * Return a string with parsed code.
	 *
	 * @return	string
	 * @access private
	 * @author Jens Bierkandt
	 */
	function _output() // print all
	{
		// if selected "braces PEAR-style", delete newline before {
		if ($this->braces == BEAUT_BRACES_PEAR) {
			// Put { in upper line
			$this->_allstr = preg_replace("/\)\n([ \t])*{/", ') {', $this->_allstr);
			// compress to } else {
			$this->_allstr = preg_replace("/}\n([ \t])*else\n([ \t])*{/", '} else {', $this->_allstr);
			$this->_allstr = preg_replace("/}\n.*?elseif/", '} elseif', $this->_allstr);
			// Do while loop
			$this->_allstr = preg_replace("/do\n([ \t])*{/", 'do {', $this->_allstr);
		}
		return $this->_allstr;
	}
	/**
	 * Put spaces on a line
	 *
	 * @return	string		a string with spaces, accordly to $this->_indent
	 * @access private
	 */
	function _getindent() {
		$str = '';
		if ($this->_indent < 0) $this->_indent = 0;
		if ($this->indent_mode == 't') {
		for ($i = 0; $i < $this->_indent; $i++) {
				$str  .= "\t";
		}
		} else {
			$str = str_repeat(' ', $this->_indent * $this->indent_width);
		}
		if ($this->indent_long_comments and ($this->_long_comment OR $this->_long_comment_last)) {
			if (!($this->_long_comment_first)) {
				$str  .= ' ';
			} else {
				$this->_long_comment_first = false;
			}
			$this->_long_comment_last = false;
		}
		return $str;
	}
	/**
	 * A nasty function, that verify that no code are altered.
	 *
	 * The idea is delete the space chars (spaces, tabs and new lines)
	 * from the original and the copy and compare it
	 * If something is different, the program altered the original
	 * and is better not use it.
	 *
	 * TODO: a better way for make the test. Not discrimine parts of the code
	 * that needs spaces (like SQL querys inside quotes);
	 *
	 * @return	mixed		bool(TRUE) or PEAR_ERROR (Wrong!)
	 * @access private
	 * @author Claudio Bustos
	 */
	function _verify() {
		$test1 = preg_replace("/\s*/", '', $this->_original);
		$test1 = str_replace('<?php', '', $test1);
		$test1 = str_replace('<?', '', $test1);
		$test2 = preg_replace("/\s*/", '', $this->_allstr);
		$test2 = str_replace('<?php', '', $test2);
		$test2 = str_replace('<?', '', $test2);
		if (md5($test1)  != md5($test2)) {
			return $this->raiseError("Original and beauty version aren't equal. Please find the differences and send them to the author ;-)");
		} else {
			return true;
		}
	}
	/**
	 * A function that list the functions (redundance rules!) inside the file
	 *
	 * When the var find_functions is set to TRUE
	 * this function return the functions in the file
	 * and put it on the begining
	 *
	 * @param	string		the beautified code
	 * @return	[type]		...
	 * @author Jay Schauer - Claudio Bustos (Adaptation)
	 */
	function _findfunctions(&$rs) {
		$this->_indent++;
		$functionlist = "<?php\n";
		$functionlist .= $this->_getindent()."/* Functions in this file */\n";
		$functionlist .= $this->_getindent()."/**************************/\n\n";
		$thestr = $rs;
		$foundone = 0;
		/* use array to capture and sort functions */
		while (preg_match ("/(function )(\S*\(\S*\))/", $thestr, $regs)) {
			$foundone++;
			$functioncapture[] = $this->_getindent().'// '.trim($regs[2])."\n";
			$replacestr = $regs[1].$regs[2];
			$thestr = str_replace($replacestr, '', $thestr);
		}
		if ($foundone) {
			natcasesort($functioncapture);
			$functionlist  .= implode('', $functioncapture);
			$functionlist  .= '?'.">\n";
			$rs = $functionlist.$rs;
		}
		$this->_indent--;
		// this following adds space between functions in the main code
		//               $rs=str_replace('function',chr(13).'function',$rs);
		//this following unindents case statements in the main code*/
		//                $rs=str_replace('   case ','case ',$rs);
		//               return $rs;
	}
}

	/**
	 * [Describe function...]
	 *
	 */
class tx_extdeveval_tuneBeautify extends tx_extdeveval_tune_phpBeautify {

	// Spaces to indent
	var $indent_width = 4;

	// Wrap or not the code at line defined by {@link $max}
	var $max_line = FALSE;

	// If true, delete empty lines
	var $del_line = FALSE;


	// Type of braces parse. Can be BEAUT_BRACES_PEAR or BEAUT_BRACES_C
	var $braces = BEAUT_BRACES_PEAR;

	// Verify the integrity of the beautify version
# unused	var $verify = true;

	// default indentation mode
	var $indent_mode = 't'; // tabs

	/**
	* Indent the long comments.
	* Add a space to all long comment lines
	* so, the text start in the column of the *
	* Idea of Michael H.E. Roth <mher@users.sourceforge.net>
	*/
	var $indent_long_comments = false;


	function beautify(&$str) {
		$this->_main($str);
		return $this->_output();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_tunebeautify.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/extdeveval/mod1/class.tx_extdeveval_tunebeautify.php']);
}
?>

