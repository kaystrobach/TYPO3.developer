<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 02.10.14
 * Time: 18:31
 */

namespace KayStrobach\Developer\Utility;


class ShellCaptureUtility {
	public static function execute($command) {
		$handle = popen($command, 'r');
		$read   = '';
		while(!feof($handle)) {
			$read .= fread($handle, 4096);
		}
		pclose($handle);
		return $read;
	}
} 