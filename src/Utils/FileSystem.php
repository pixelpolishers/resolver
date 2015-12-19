<?php

namespace PixelPolishers\Resolver\Utils;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Finder;

class FileSystem
{
    public static function createDirectory($path, $mode = 0777)
    {
        if (is_dir($path)) {
            return;
        }

        $parentPath = dirname($path);

        if (!is_dir($parentPath)) {
            self::createDirectory($parentPath, $mode);
        }

        mkdir($path, $mode, true);
    }

    public static function emptyDirectory($path)
    {
        $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($ri as $file) {
            if ($file->isDir()) {
                self::removeDirectory($file->getPathname());
            } else {
                self::removeFile($file->getPathname());
            }
        }
    }

    public static function getRelativePath($fromPath, $toPath)
    {
        $from = explode(DIRECTORY_SEPARATOR, realpath($fromPath));
        $to = explode(DIRECTORY_SEPARATOR, realpath($toPath));

        while (isset($from[0]) && isset($to[0])) {
            if ($from[0] !== $to[0]) {
                break;
            }

            array_shift($from);
            array_shift($to);
        }

        $result = array_merge(
            array_fill(0, count($from), '..'),
            $to
        );

        return implode(DIRECTORY_SEPARATOR, $result);
    }

    public static function rename($oldPath, $newPath)
    {
        rename($oldPath, $newPath);
    }

    public static function removeDirectory($path)
    {
        self::emptyDirectory($path);

        return rmdir($path);

    }

    public static function removeFile($path)
    {
        unlink($path);
    }

    public static function getDirectoryContent($path)
    {
        $finder = Finder::create()
            ->ignoreVCS(false)
            ->ignoreDotFiles(false)
            ->depth(0)
            ->in($path);

        return iterator_to_array($finder);
    }
}
