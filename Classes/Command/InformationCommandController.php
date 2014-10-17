<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 17.10.14
 * Time: 08:09
 */

namespace KayStrobach\Developer\Command;


class InformationCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController {
	public function phpInfoCommand() {
		phpinfo();
	}
} 