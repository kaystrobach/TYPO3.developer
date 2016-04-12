<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 17.10.14
 * Time: 18:49
 */

namespace KayStrobach\Developer\Services;


use TYPO3\CMS\Core\Utility\GeneralUtility;

class FileChangeWatcherService
{
    /**
     * @var string
     */
    protected $path = null;

    /**
     * @var array
     */
    protected $callbacks = array();

    /**
     * @param string $path
     */
    public function setPath($path) 
    {
        $this->path = $path;
    }

    public function enableDebug() 
    {
        $this->addCallback(array($this, 'debugCallback'), 'init');
        $this->addCallback(array($this, 'debugCallback'), 'create');
        $this->addCallback(array($this, 'debugCallback'), 'modify');
        $this->addCallback(array($this, 'debugCallback'), 'delete');
        $this->addCallback(array($this, 'debugCallback'), 'all');
    }

    /**
     * add a callback to the callback list
     *
     * @param $callable
     * @param string   $eventType
     */
    public function addCallback($callable, $eventType = 'modify') 
    {
        $this->callbacks[$eventType][] = $callable;
    }

    /**
     * user to trigger callbacks
     *
     * @param $eventType
     * @param $file
     */
    protected function triggerCallback($eventType, $file) 
    {
        if(array_key_exists($eventType, $this->callbacks)) {
            foreach($this->callbacks[$eventType] as $callable) {
                call_user_func($callable, $file, $eventType);
            }
        }
        if(array_key_exists('all', $this->callbacks)) {
            foreach($this->callbacks['all'] as $callable) {
                call_user_func($callable, $file, $eventType);
            }
        }
    }

    /**
     * Debug callback
     *
     * @param $file
     * @param $eventType
     */
    protected function debugCallback($file, $eventType) 
    {
        echo  $eventType . ': ' . $file . LF;
    }

    /**
     * @param int $sleepTime
     */
    public function startWatching($sleepTime = 1) 
    {
        $filesWithStat = array();
        $firstRun = true;
        while(1) {
            clearstatcache();
            $files = GeneralUtility::getAllFilesAndFoldersInPath(array(), $this->path, '', false, 99, '\..*');
            foreach ($files as $file) {
                $fileStat = stat($file);
                if (!array_key_exists($file, $filesWithStat)) {
                    if(!$firstRun) {
                        $this->triggerCallback('create', $file);
                    } else {
                        $this->triggerCallback('init', $file);
                    }
                    $filesWithStat[$file] = $fileStat;
                } elseif ($filesWithStat[$file] !== $fileStat) {
                    $this->triggerCallback('modify', $file);
                    $filesWithStat[$file] = $fileStat;
                }
            }

            $deletedFiles = array_diff(array_keys($filesWithStat), array_values($files));
            if (count($deletedFiles) > 0) {
                foreach ($deletedFiles as $file) {
                    $this->triggerCallback('delete', $file);
                    unset($filesWithStat[$file]);
                }
            }
            sleep($sleepTime);
            if($firstRun) {
                $firstRun = false;
            }
        }
    }
} 