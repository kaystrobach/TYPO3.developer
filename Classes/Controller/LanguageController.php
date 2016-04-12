<?php

namespace KayStrobach\Developer\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class LanguageController
 *
 * Does language related stuff
 *
 * @package KayStrobach\Developer\Controller
 */
class LanguageController extends ActionController
{
    /**
     * @var \TYPO3\CMS\Core\Resource\FileRepository
     * @inject
     */
    protected $fileRepository;

    /**
     * @var \KayStrobach\Developer\Services\LocallangToXliffService
     * @inject
     */
    protected $locallangToXliffService;

    /**
     *
     */
    public function indexAction() 
    {
    }

    /**
     * will allow xliff transformation
  *
     * @param string $fileReferenceId
     */
    public function xmlToXlfAction($fileReferenceId = null) 
    {
        $this->view->assign('fileReferenceId', $fileReferenceId);

        if($fileReferenceId !== null) {
            $newXlfFilesCreated = array();
            list($type, $fileUid) = explode(':', trim($fileReferenceId));
            /**
 * @var \TYPO3\CMS\Core\Resource\File $file 
*/
            $file = $this->fileRepository->findByIdentifier((integer)$fileUid);
            $content = $file->getContents();
            $this->locallangToXliffService->setXml($content);

            /**
 * @var \TYPO3\CMS\Core\Resource\Folder $parentFolder 
*/
            $parentFolder = $file->getParentFolder();


            foreach($this->locallangToXliffService->getDefinedLanguages() as $language) {
                $xlfFileName = $this->locallangToXliffService->getPrefixForFile($language) . $file->getNameWithoutExtension() . '.xlf';
                $newXlfFilesCreated[] = $xlfFileName;
                $tempFile = GeneralUtility::tempnam('locallang');
                file_put_contents($tempFile, $this->locallangToXliffService->getXlfForLangKey($language));
                $parentFolder->addFile(
                    $tempFile,
                    $xlfFileName
                );
                GeneralUtility::unlink_tempfile($tempFile);

            }
            $this->addFlashMessage('Processed ' . $file->getStorage()->getName() . ': ' . $file->getIdentifier() . '<pre>' . implode(LF, $newXlfFilesCreated) . '</pre>');
        }
    }


}