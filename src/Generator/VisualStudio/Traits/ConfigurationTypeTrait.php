<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio\Traits;

trait ConfigurationTypeTrait
{
    protected function convertConfigurationType($configType)
    {
        switch ($configType) {
            case 'dynamic-library':
                $result = 'DynamicLibrary';
                break;

            case 'static-library':
                $result = 'StaticLibrary';
                break;

            case 'application':
            default:
                $result = 'Application';
                break;
        }

        return $result;
    }
}
