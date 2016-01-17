<?php

namespace PixelPolishers\Resolver\Config\Type;

final class Platform
{
    const WIN32 = 'win32';
    const WIN64 = 'win64';

    public static function isWindows($platform)
    {
        return $platform === self::WIN32 || $platform === self::WIN64;
    }
}
