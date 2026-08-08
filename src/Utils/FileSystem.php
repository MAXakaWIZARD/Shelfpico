<?php

declare(strict_types=1);

namespace App\Utils;

use DirectoryIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileSystem
{
    /**
     * returns extension of specified file
     */
    public static function getFileExt(string $filePath): string
    {
        return pathinfo($filePath, PATHINFO_EXTENSION);
    }

    /**
     * copies source to dest, fully recursive
     */
    public static function copy(
        string $sourcePath,
        string $destPath,
        int $folderPermission = 0755,
        int $filePermission = 0644
    ): bool {
        //source=file & dest=dir => copy file to dest-dir
        /*
        source=file & dest=file / not there yet =>
        copy file from source to dest and overwrite a file there, if present
        */

        //source=dir & dest=dir => copy all content from source to dir
        //source=dir & dest=file => do nothing

        $result = true;

        //$sourcePath is file
        if (is_file($sourcePath)) {
            if (is_dir($destPath)) {
                $targetFileName = $destPath . '/' . basename($sourcePath);
            } else {
                // $destPath is (new) filename
                $targetFileName = $destPath;
            }

            if (!@copy($sourcePath, $targetFileName)) {
                $result = false;
            }

            if ($result) {
                @chmod($targetFileName, $filePermission);
            }
        } elseif (is_dir($sourcePath)) {
            self::checkDir($destPath, $folderPermission);

            if (is_dir($destPath)) {
                $flags = FilesystemIterator::SKIP_DOTS;
                $iterator = new RecursiveDirectoryIterator($sourcePath, $flags);
                $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

                foreach ($iterator as $item) {
                    $targetNewPath = $destPath . str_replace($sourcePath, '', $item->getPathname());

                    if ($item->isDir()) {
                        //dir found, create it
                        if (!@mkdir($targetNewPath, $folderPermission, true)) {
                            $result = false;
                        }
                    } else {
                        if (!@copy($item->getPathname(), $targetNewPath)) {
                            $result = false;
                        }
                    }
                }
            } else {
                //failed to create destination dir
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * this function is optimized for copying just one dir
     * with large amount of files
     *
     * return true if ALL files copied successfully
     */
    public static function copyDirWithFiles(
        string $sourcePath,
        string $destinationPath,
        int $folderPermission = 0755,
        int $filePermission = 0644
    ): bool {
        $result = true;

        if (is_dir($sourcePath)) {
            if (!is_dir($destinationPath)) {
                //destinatio dir does not exist, try to create
                @mkdir($destinationPath, $folderPermission, true);
            }

            if (is_dir($destinationPath)) {
                $iterator = new DirectoryIterator($sourcePath);
                foreach ($iterator as $item) {
                    if ($item->isFile()) {
                        $targetFilePath = $destinationPath . '/' . $item->getBasename();

                        if (copy($item->getPathname(), $targetFilePath)) {
                            chmod($targetFilePath, $filePermission);
                        } else {
                            $result = false;
                        }
                    }
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * returns files count in specified dir, not recursive
     */
    public static function getFilesCount(string $directory, bool $recursive = true): int
    {
        $count = 0;

        if ($recursive) {
            $dirIterator = new RecursiveDirectoryIterator($directory);
            $iterator = new RecursiveIteratorIterator($dirIterator);
        } else {
            $iterator = new DirectoryIterator($directory);
        }

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * deletes all files with specified extension in specified dir,
     * fully recursive
     */
    public static function dropFilesByExt(string $directory, array $extensions = [], bool $recursive = true): void
    {
        if ($recursive) {
            $dirIterator = new RecursiveDirectoryIterator($directory);
            $iterator = new RecursiveIteratorIterator($dirIterator);
        } else {
            $iterator = new DirectoryIterator($directory);
        }

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $filePath = $item->getPathname();
                $fileExt = self::getFileExt($filePath);

                if (in_array($fileExt, $extensions)) {
                    //file mathces by ext, drop it
                    self::dropFile($filePath);
                }
            }
        }
    }

    /**
     * deletes specified dir, fully recursive
     */
    public static function dropDir(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $flags = FilesystemIterator::SKIP_DOTS;
        $iterator = new RecursiveDirectoryIterator($path, $flags);
        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }
        return @rmdir($path);
    }

    /**
     * safely removes file (checks, if exists)
     */
    public static function dropFile(string $filePath): bool
    {
        if (is_file($filePath)) {
            return unlink($filePath);
        } else {
            //file does not exist
            return true;
        }
    }

    /**
     * safely removes file (checks, if exists)
     */
    public static function renameFile(string $oldPath, string $newPath): bool
    {
        if (is_file($oldPath)) {
            return rename($oldPath, $newPath);
        } else {
            //file does not exist
            return false;
        }
    }

    /**
     * performs recursive permisson change
     */
    public static function recursiveChmod(string $directory, int $mode = 0777): void
    {
        $flags = FilesystemIterator::SKIP_DOTS;
        $iterator = new RecursiveDirectoryIterator($directory, $flags);
        $iterator = new RecursiveIteratorIterator($iterator);

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                chmod($item->getPathname(), $mode);
            } elseif ($item->isDir()) {
                chmod($item->getPathname(), $mode);
            }
        }
    }

    /**
     * search for files with specified extension or extensions in specified dir
     */
    public static function searchFiles(
        string $directory,
        array $extensions = [],
        bool $recursive = true,
        bool $returnBasenames = false // return only basenames instead of full paths
    ): array {
        $foundFiles = [];

        $filterFilesFlag = count($extensions) > 0;

        if ($recursive) {
            $iterator = new RecursiveDirectoryIterator($directory);
            $iterator = new RecursiveIteratorIterator($iterator);
        } else {
            $iterator = new DirectoryIterator($directory);
        }

        foreach ($iterator as $item) {
            if ($item->isFile()) {
                $filePath = $item->getPathname();
                $fileExt = self::getFileExt($filePath);

                $returnValue = ($returnBasenames) ? $item->getBasename() : $item->getPathname();

                if ($filterFilesFlag) {
                    if (in_array($fileExt, $extensions)) {
                        $foundFiles[] = $returnValue;
                    }
                } else {
                    //no filter, add all files
                    $foundFiles[] = $returnValue;
                }
            }
        }

        $iterator = null;

        return $foundFiles;
    }

    /**
     * returns array with subdirectories names, only 1 level depth
     */
    public static function getSubdirectoriesNames(string $directory): array
    {
        $dirs = [];

        $iterator = new DirectoryIterator($directory);
        foreach ($iterator as $item) {
            if ($item->isDir() && !$item->isDot()) {
                $dirs[] = $item->getBasename();
            }
        }

        $iterator = null;

        return $dirs;
    }

    public static function getSubfolders(string $directory): array
    {
        $subfolders = [];

        $iterator = new DirectoryIterator($directory);

        //search for subfolders
        foreach ($iterator as $item) {
            if ($item->isDir() && !$item->isDot()) {
                $subfolders[] = $item->getPathname();
            }
        }

        return $subfolders;
    }

    /**
     * checks if directory exists and creates it, if not
     * nested directories supported
     */
    public static function checkDir(string $path, int $permissions = 0777): bool
    {
        if (is_dir($path)) {
            return true;
        } else {
            if (@mkdir($path, $permissions, true)) {
                return chmod($path, $permissions);
            } else {
                return false;
            }
        }
    }
}
