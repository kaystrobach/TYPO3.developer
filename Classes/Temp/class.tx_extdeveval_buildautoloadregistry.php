<?php

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * This class contains methods to build TYPO3 autoloader registry.
 *
 * @author	Dmitry Dulepov	<dmitry@typo3.org>
 * @author Sebastian Kurfürst <sebastian@typo3.org>
 */
class tx_extdeveval_buildautoloadregistry {
	/**
	 * @var array
	 */
	protected $classNamePrefixes = array('tx_', 'Tx_', 'user_');

	/**
	 * @var array
	 */
	protected $processedClasses = array();

	/**
	 * @var array
	 */
	protected $messages = array();

	/**
	 * Build the autoload registry for a given extension and place it ext_autoload.php.
	 *
	 * @param	string	$extensionName	Name of the extension
	 * @param	string	$extensionPath	full path of the extension
	 * @return	string	HTML string which should be outputted
	 */
	public function createAutoloadRegistryForExtension($extensionName, $extensionPath) {
		$classNameToFileMapping = array();
		$globalPrefixes = array();

		$globalPrefixes[] = '$extensionPath = t3lib_extMgm::extPath(\'' . $extensionName . '\');';
		$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping, $extensionPath, '.*tslib.*', '$extensionPath . \'|\'');

		// Processes special 'classes/' or 'Classes/' folder and use a shorter notation for that:
		// (files will be overridden in the class name to file mapping)
		$extensionClassesDirectory = $this->getExtensionClassesDirectory($extensionPath);
		if ($extensionClassesDirectory !== FALSE) {
			$globalPrefixes[] = '$extensionClassesPath = $extensionPath . \'' . $extensionClassesDirectory . '\';';
			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping, $extensionPath . $extensionClassesDirectory, '.*tslib.*', '$extensionClassesPath . \'|\'');
		}

		$extensionPrefix = $this->getExtensionPrefix($extensionName);
		$errors = array();
		foreach ($classNameToFileMapping as $className => $fileName) {
			if ($this->isValidClassNamePrefix($className, $extensionPrefix) === FALSE) {
				$errors[] = $className . ' does not start with Tx_' . $extensionPrefix . ', tx_' . $extensionPrefix . ' or user_' . $extensionPrefix . ' and is not added to the autoloader registry.';
				unset($classNameToFileMapping[$className]);
			}
		}
		$autoloadFileString = $this->generateAutoloadPHPFileDataForExtension($classNameToFileMapping, $globalPrefixes);
		if (@file_put_contents($extensionPath . 'ext_autoload.php', $autoloadFileString)) {
			t3lib_div::fixPermissions($extensionPath . 'ext_autoload.php');
			$errors[] = 'Wrote the following data: <pre>' . htmlspecialchars($autoloadFileString) . '</pre>';
		} else {
			$errors[] = '<b>' . $extensionPath . 'ext_autoload.php could not be written!</b>';
		}
		return implode('<br />', $errors);
	}
	/**
	 * Build the autoload registry for the core.
	 * That includes:
	 * - t3lib/
	 * - tslib/
	 * - the "lang" sysext
	 *
	 * @return	string	HTML string which should be outputted
	 */
	public function createAutoloadRegistryForCore() {
		$classNameToFileMapping = array();

			// for >= TYPO3 6.0
		if ($this->isAtLeastVersion('6.0.0')) {
				// Defines order of variables in output file:
			$classNameToFileMapping['t3libClasses'] = array();
			$classNameToFileMapping['typo3Classes'] = array();

			foreach (array('classes', 'interfaces') as $folder) {
				$this->buildAutoloadRegistryForSinglePath(
					$classNameToFileMapping['typo3Classes'],
					PATH_typo3 . $folder . DIRECTORY_SEPARATOR, '',
					'PATH_typo3 . \'' . $folder . DIRECTORY_SEPARATOR . '|\'',
					FALSE
				);
			}

			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping['typo3Classes'], PATH_typo3, '', 'PATH_typo3 . \'|\'', FALSE, 'php,inc');
			$this->removeClasseNamesFromAutoloadRegistry($classNameToFileMapping['typo3Classes'], '#^(sc_|Typo3_Bootstrap)#i');
			$this->removeDuplicateClasseNamesFromAutoloadRegistry($classNameToFileMapping['typo3Classes']);
			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping['t3libClasses'], PATH_t3lib, '', 'PATH_t3lib . \'|\'');
			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping['typo3Classes'], t3lib_extMgm::extPath('lang'), '', 't3lib_extMgm::extPath(\'lang\') . \'|\'');
			$autoloadFileString = $this->generateAutoloadPHPFileDataForCore($classNameToFileMapping);

			// for >= TYPO3 4.5
		} elseif ($this->isExtensionCmsIncluded()) {
			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping['t3libClasses'], PATH_t3lib, '', 'PATH_t3lib . \'|\'');
			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping['t3libClasses'], t3lib_extMgm::extPath('lang'), '', 't3lib_extMgm::extPath(\'lang\') . \'|\'');
			$autoloadFileString = $this->generateAutoloadPHPFileDataForCore($classNameToFileMapping);

			// for < TYPO3 4.5
		} else {
			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping, PATH_t3lib, '', 'PATH_t3lib . \'|\'');
			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping, PATH_tslib, '', 'PATH_tslib . \'|\'');
			$this->buildAutoloadRegistryForSinglePath($classNameToFileMapping, t3lib_extMgm::extPath('lang'), '', 't3lib_extMgm::extPath(\'lang\') . \'|\'');
			$autoloadFileString = $this->generateAutoloadPHPFileDataForExtension($classNameToFileMapping);
		}

		if (!count($classNameToFileMapping)) {
			return '<b>Error. No classes found.</b>';
		}
		if (!@file_put_contents(PATH_t3lib . 'core_autoload.php', $autoloadFileString)) {
			return '<b>' . PATH_t3lib . 'core_autoload.php could not be written!</b>';
		}

		return PATH_t3lib . 'core_autoload.php successfully written.' . ($this->messages ? '<br/><br/>' . implode('<br/>', $this->messages) : '');
	}

	/**
	 * Generate autoload PHP file data. Takes an associative array with class name to file mapping, and outputs it as PHP.
	 * Does NOT escape the values in the associative array. Includes the <?php ... ?> syntax and an optional global prefix.
	 *
	 * @param	array	$classNameToFileMapping class name to file mapping
	 * @param	array	$globalPrefixes	Global prefixes which are prepended to all code.
	 * @return	string	The full PHP string
	 */
	protected function generateAutoloadPHPFileDataForExtension($classNameToFileMapping, array $globalPrefixes = array()) {
		$output = '<?php' . PHP_EOL;
		$output .= '// DO NOT CHANGE THIS FILE! It is automatically generated by extdeveval::buildAutoloadRegistry.' . PHP_EOL;
		$output .= '// This file was generated on ' . date('Y-m-d H:i') . PHP_EOL;
		$output .= PHP_EOL;
		$output .= implode(PHP_EOL, $globalPrefixes) . PHP_EOL;
		$output .= 'return array(' . PHP_EOL;
		foreach ($classNameToFileMapping as $className => $quotedFileName) {
			$output .= '	\'' . $className . '\' => ' . $quotedFileName . ',' . PHP_EOL;
		}
		$output .= ');' . PHP_EOL;
		$output .= '?>';
		return $output;
	}

	/**
	 * Generate autoload PHP file data. Takes an associative array with class name to file mapping, and outputs it as PHP.
	 * Does NOT escape the values in the associative array. Includes the <?php ... ?> syntax and an optional global prefix.
	 *
	 * @param	array	$classNameToFileMapping class name to file mapping
	 * @return	string	The full PHP string
	 */
	protected function generateAutoloadPHPFileDataForCore(array $classNameToFileMapping) {
		$output = '<?php' . PHP_EOL;
		$output .= '// DO NOT CHANGE THIS FILE! It is automatically generated by extdeveval::buildAutoloadRegistry.' . PHP_EOL;
		$output .= '// This file was generated on ' . date('Y-m-d H:i') . PHP_EOL;
		$output .= PHP_EOL;

		foreach ($classNameToFileMapping as $sectionName => $sectionClasses) {
			$output .= '$' . $sectionName . ' = array(' . PHP_EOL;
			foreach ($sectionClasses as $className => $quotedFileName) {
				$output .= '	\'' . $className . '\' => ' . $quotedFileName . ',' . PHP_EOL;
			}
			$output .= ');' . PHP_EOL;
			$output .= PHP_EOL;
		}

		$output .= '$tslibClasses = require(PATH_typo3 . \'sysext/cms/ext_autoload.php\');' . PHP_EOL;
		$output .= PHP_EOL;
		$output .= 'return array_merge($' . implode(', $', array_keys($classNameToFileMapping)) . ', $tslibClasses);' . PHP_EOL;
		$output .= '?>';
		return $output;
	}

	/**
	 * Generate the $classNameToFileMapping for a given filePath.
	 *
	 * @param	array	$classNameToFileMapping	(Reference to array) All values are appended to this array.
	 * @param	string	$path	Path which should be crawled
	 * @param	string	$excludeRegularExpression	Exclude regular expression, to exclude certain files from being processed
	 * @param	string	$valueWrap	Wrap for the file name
	 * @param	boolean $includeSubFolders Whether to include sub folders
	 * @param	string $fileExtensionList List of file extensions to consider
	 * @return void
	 */
	protected function buildAutoloadRegistryForSinglePath(&$classNameToFileMapping, $path, $excludeRegularExpression = '', $valueWrap = '\'|\'', $includeSubFolders = TRUE, $fileExtensionList = 'php') {
		$recursivityLevels = ($includeSubFolders ? 99 : 0);

		$extensionFileNames = t3lib_div::removePrefixPathFromList(
			t3lib_div::getAllFilesAndFoldersInPath(
				array(),
				$path,
				$fileExtensionList,
				FALSE,
				$recursivityLevels,
				$excludeRegularExpression
			),
			$path
		);

		foreach ($extensionFileNames as $extensionFileName) {
			$classNamesInFile = $this->extractClassNames($path . $extensionFileName);
			if (!count($classNamesInFile)) continue;
			foreach ($classNamesInFile as $className) {
					// Register processed classes and the accordant file name:
				if (!isset($this->processedClasses[strtolower($className)])
					|| !in_array($path . $extensionFileName, $this->processedClasses[strtolower($className)])) {
					$this->processedClasses[strtolower($className)][] = $path . $extensionFileName;
				}

				$classNameToFileMapping[strtolower($className)] = str_replace('|', $extensionFileName, $valueWrap);
			}
		}

		ksort($classNameToFileMapping);
	}

	/**
	 * Removes classes that match a given regular expression pattern.
	 *
	 * @param array $classNameToFileMapping
	 * @param string $regularExpression
	 * @return void
	 */
	protected function removeClasseNamesFromAutoloadRegistry(array &$classNameToFileMapping, $regularExpression) {
		foreach ($classNameToFileMapping as $className => $fileReference) {
			if (preg_match($regularExpression, $className)) {
				unset($classNameToFileMapping[$className]);
			}
		}
	}

	/**
	 * Removes duplicate class definitions.
	 *
	 * @param array $classNameToFileMapping
	 */
	protected function removeDuplicateClasseNamesFromAutoloadRegistry(array &$classNameToFileMapping) {
		foreach ($classNameToFileMapping as $className => $_) {
			if (isset($this->processedClasses[$className]) && count($this->processedClasses[$className]) > 1) {
				unset($classNameToFileMapping[$className]);
				$this->messages[] = 'Ignored duplicate class definition for <b>' . $className . '</b> (defined in ' . implode(', ', $this->processedClasses[$className]) . ')';
			}
		}
	}

	/**
	 * Extracts class names from the given file.
	 *
	 * @param	string	$filePath	File path (absolute)
	 * @return	array	Class names
	 */
	protected function extractClassNames($filePath) {
		$fileContent = php_strip_whitespace($filePath);
		$classNames = array();
		if (function_exists('token_get_all')) {
			$tokens = token_get_all($fileContent);
			while(1) {
				// look for "class" or "interface"
				$token = $this->findToken($tokens, array(T_ABSTRACT, T_CLASS, T_INTERFACE));
				// fetch "class" token if "abstract" was found
				if ($token === 'abstract') {
					$token = $this->findToken($tokens, array(T_CLASS));
				}
				if ($token === false) {
					// end of file
					break;
				}
				// look for the name (a string) skipping only whitespace and comments
				$token = $this->findToken($tokens, array(T_STRING), array(T_WHITESPACE, T_COMMENT, T_DOC_COMMENT));
				if ($token === false) {
					// unexpected end of file or token: remove found names because of parse error
					t3lib_div::sysLog('Parse error in "' . $filePath . '".', 'Core', 2);
					$classNames = array();
					break;
				}
				$token = t3lib_div::strtolower($token);
				// exclude XLASS classes
				if (strncmp($token, 'ux_', 3)) {
					$classNames[] = $token;
				}
			}
		} else {
			// TODO: parse PHP - skip coments and strings, apply regexp only on the remaining PHP code
			$matches = array();
			preg_match_all('/^[ \t]*(?:(?:abstract|final)?[ \t]*(?:class|interface))[ \t\n\r]+([a-zA-Z][a-zA-Z_0-9]*)/mS', $fileContent, $matches);
			$classNames = array_map('t3lib_div::strtolower', $matches[1]);
		}
		return $classNames;
	}

	/**
	 * Find tokens in the tokenList
	 *
	 * @param	array	$tokenList	list of tokens as returned by token_get_all()
	 * @param	array	$wantedTokens	the tokens to be found
	 * @param	array	$intermediateTokens	optional: list of tokens that are allowed to skip when looking for the wanted token
	 * @return	mixed
	 */
	protected function findToken(array &$tokenList, array $wantedTokens, array $intermediateTokens = array()) {
		$skipAllTokens = count($intermediateTokens) ? false : true;

		$returnValue = false;
		// Iterate with while since we need the current array position:
		while (list(,$token) = each($tokenList)) {
			// parse token (see http://www.php.net/manual/en/function.token-get-all.php for format of token list)
			if (is_array($token)) {
				list($id, $text) = $token;
			} else {
				$id = $text = $token;
			}
			if (in_array($id, $wantedTokens)) {
				$returnValue = $text;
				break;
			}
			// look for another token
			if ($skipAllTokens || in_array($id, $intermediateTokens)) {
				continue;
			}
			break;
		}
		return $returnValue;
	}

	/**
	 * Determines whether a class name starts with tx_, Tx_ or user_.
	 *
	 * @param string $className Name of the class to be checked
	 * @param string $extensionPrefix The extension prefix (e.g. ttnews, not tt_news)
	 * @return boolean
	 */
	protected function isValidClassNamePrefix($className, $extensionPrefix) {
		foreach ($this->classNamePrefixes as $classNamePrefix) {
			if (strpos($className, $classNamePrefix . $extensionPrefix) === 0) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Gets the extension prefix (e.g. tt_news will be ttnews).
	 *
	 * @param string $extensionName name of the extension
	 * @return string
	 */
	protected function getExtensionPrefix($extensionName) {
		return str_replace('_', '', $extensionName);
	}

	/**
	 * Gets the directory that holds classes.
	 *
	 * @param string $extensionPath full path of the extension
	 * @return mixed The classes directory (if any) or FALSE otherwise
	 */
	protected function getExtensionClassesDirectory($extensionPath) {
		$extensionClassesDirectory = FALSE;

			// Check if 'Classes/' directory exists and make sure this is the proper case (workaround for
			// insensitive file systems like HFS with default settings)
		if (@is_dir($extensionPath . 'Classes/') && substr(realpath($extensionPath . 'Classes'), -7) === 'Classes') {
			$extensionClassesDirectory = 'Classes/';
		} elseif (@is_dir($extensionPath . 'classes/')) {
			$extensionClassesDirectory = 'classes/';
		}

		return $extensionClassesDirectory;
	}

	/**
	 * Determins whether the autoload registry of the system extension cms is included.
	  
	 * @return boolean
	 */
	protected function isExtensionCmsIncluded() {
		return $this->isAtLeastVersion('4.5.0');
	}

	/**
	 * Determines whether a given TYPO3 version is used.
	 *
	 * @param string $version
	 * @return boolean
	 */
	protected function isAtLeastVersion($version) {
		return (Tx_Extdeveval_Compatibility::convertVersionNumberToInteger(TYPO3_version) >= Tx_Extdeveval_Compatibility::convertVersionNumberToInteger($version));
	}
}
?>