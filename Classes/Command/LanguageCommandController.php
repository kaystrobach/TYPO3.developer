<?php

namespace KayStrobach\Developer\Command;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class LanguageCommandController extends \TYPO3\CMS\Extbase\Mvc\Controller\CommandController
{
    /**
     * @var \KayStrobach\Developer\Services\LocallangToXliffService
     * @inject
     */
    protected $locallangToXliffService;

    /**
     * converts a directory with locallang.xml files to a xlf files
     *
     * @param  string $path
     * @return void
     */
    public function convertDirToXlfCommand($path) 
    {
        if(is_dir($path)) {
            $files = GeneralUtility::getAllFilesAndFoldersInPath(array(), $path, 'xml');
            foreach($files as $file) {
                $this->convertToXlfCommand($file);
            }
        } else {
            $this->outputLine('Dir ' . $path . ' is either not a directory, or does not exist');
        }

    }

    /**
     * converts a locallang.xml file to an xlf file
     *
     * @param  string $filename
     * @return void
     */
    public function convertToXlfCommand($filename) 
    {
        if(file_exists($filename) && is_file($filename)) {
            $content = file_get_contents($filename);
            $parentFolder = dirname($filename);
            $this->locallangToXliffService->setXml($content);
            foreach($this->locallangToXliffService->getDefinedLanguages() as $language) {
                $xlfFileName = $parentFolder . '/' . basename($filename, '.xml') . $this->locallangToXliffService->getFileExtension($language);
                $newXlfFilesCreated[] = $xlfFileName;
                if(!file_exists($xlfFileName)) {
                    GeneralUtility::writeFile($xlfFileName, $this->locallangToXliffService->getXlfForLangKey($language));
                    $this->outputLine('Created ' . $xlfFileName);
                } else {
                    $this->outputLine('Skipped ' . $xlfFileName);
                }
            }
        } else {
            $this->outputLine('File ' . $filename . ' is either not a file, or does not exist');
        }
    }
} 