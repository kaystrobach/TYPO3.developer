<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 08.07.15
 * Time: 13:52
 */

namespace KayStrobach\Developer\Utility;


class DirectoryStructureCheck
{

    /**
     * @var array
     */
    protected static $directoriesToCheck = array(
    'Classes',
    'Classes/Controller',
    'Classes/ViewHelpers',
    'Documentation',
    'Resources/Private/Language',
    'Resources/Private/Layouts',
    'Resources/Private/Partials',
    'Resources/Private/Templates',
    'Resources/Private/Partials',
    'Resources/Public',
    );

    protected static $filesToCheck = array(
    'ext_emconf.php',
    'ext_icon.gif',
    'ext_localconf.php',
    'ext_tables.php',
    );

    /**
     * @param string $extensionRootDirectory
     * @return array
     */
    public static function checkDirectories($extensionRootDirectory) 
    {
        return self::checkExistence($extensionRootDirectory, self::$directoriesToCheck);
    }

    /**
     * @param string $extensionRootDirectory
     * @return array
     */
    public static function checkFiles($extensionRootDirectory) 
    {
        return self::checkExistence($extensionRootDirectory, self::$filesToCheck);
    }

    /**
     * @param $extensionRootDirectory
     * @param $items
     * @return array
     */
    public static function checkExistence($extensionRootDirectory, $items) 
    {
        $checkedItems = array();
        foreach($items as $item) {
            if(file_exists($extensionRootDirectory . $item)) {
                $checkedItems[$item] = true;
            } else {
                $checkedItems[$item] = false;
            }
        }
        return $checkedItems;
    }

}