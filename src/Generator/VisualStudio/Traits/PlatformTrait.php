<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio\Traits;

trait PlatformTrait
{
    protected function convertPlatform($platform)
    {
        switch (strtolower($platform)) {
            case 'win64':
                $result = 'Win64';
                break;

            case 'win32':
                $result = 'Win32';
                break;

            default:
                throw new RuntimeException(sprintf('Cannot add project with platform "%s" to solution.', $platform));
        }

        return $result;
    }
}
