<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license       http://jtl-url.de/jtlshoplicense
 */

/**
 * Class FileSystemHelper
 * @since 5.0.0
 */
class FileSystemHelper
{
    /**
     * @param string $dir
     * @return bool
     */
    public static function delDirRecursively(string $dir): bool
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        $res      = true;
        foreach ($iterator as $fileInfo) {
            $fileName = $fileInfo->getFilename();
            if ($fileName !== '.gitignore' && $fileName !== '.gitkeep') {
                $func = ($fileInfo->isDir() ? 'rmdir' : 'unlink');
                $res  = $res && $func($fileInfo->getRealPath());
            }
        }

        return $res;
    }
}