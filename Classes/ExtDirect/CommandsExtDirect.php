<?php

namespace KayStrobach\Developer\ExtDirect;

use KayStrobach\Developer\Utility\ShellCaptureUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;


class CommandsExtDirect
{

    public function phpcs($extension) 
    {
        $cleanedPath = ExtensionManagementUtility::extPath($extension);
        $command = PATH_site . 'bin/phpcs --standard=TYPO3CMS -n ' . $cleanedPath;
        return ShellCaptureUtility::execute($command);
    }

    public function pdepend($extension) 
    {
        $cleanedPath = ExtensionManagementUtility::extPath($extension);
        $command = PATH_site . 'bin/pdepend --standard=TYPO3CMS -n ' . $cleanedPath;
        return ShellCaptureUtility::execute($command);
    }

    public function phpcb($extension) 
    {
        $cleanedPath = ExtensionManagementUtility::extPath($extension);
        $command = PATH_site . 'bin/phpcb --standard=TYPO3CMS -n ' . $cleanedPath;
        return ShellCaptureUtility::execute($command);
    }

    public function phpcpd($extension) 
    {
        $cleanedPath = ExtensionManagementUtility::extPath($extension);
        $command = PATH_site . 'bin/phpcpd ' . $cleanedPath;
        return ShellCaptureUtility::execute($command);
    }

    public function phpmd($extension) 
    {
        $cleanedPath = ExtensionManagementUtility::extPath($extension);
        $command = PATH_site . 'bin/phpmd --standard=TYPO3CMS -n ' . $cleanedPath;
        return ShellCaptureUtility::execute($command);
    }

    public function phpunit($extension) 
    {
        $cleanedPath = ExtensionManagementUtility::extPath($extension);
        $command = PATH_site . 'bin/phpunit -ctypo3/sysext/core/Build/UnitTests.xml ' . $cleanedPath;
        return ShellCaptureUtility::execute($command);
    }
}