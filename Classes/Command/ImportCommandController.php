<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 17.10.14
 * Time: 08:24
 */

namespace KayStrobach\Developer\Command;


class ImportCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
    /**
     * will import an sql file
     */
    public function importSqlCommand() 
    {
        $this->outputLine('Will import an sqlfile lateron');
    }


} 