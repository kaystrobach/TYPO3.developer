<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 17.10.14
 * Time: 18:08
 */

namespace KayStrobach\Developer\Command;


use KayStrobach\Developer\Services\FileChangeWatcherService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;

class WatcherCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
    /**
     * @param string $path
     */
    public function watchCommand($path) 
    {
        $fileChangeWatcherService = new FileChangeWatcherService();
        $fileChangeWatcherService->setPath($path);
        $fileChangeWatcherService->enableDebug();
        $fileChangeWatcherService->startWatching(1);
    }
} 