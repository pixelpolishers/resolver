<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio\Traits;

trait CharacterSetTrait
{
    protected function convertCharacterSet($characterSet)
    {
        switch ($characterSet) {
            case 'none':
                $result = 'NotSet';
                break;

            case 'ansi':
                $result = 'MultiByte';
                break;

            default:
            case 'unicode':
                $result = 'Unicode';
                break;
        }

        return $result;
    }
}
