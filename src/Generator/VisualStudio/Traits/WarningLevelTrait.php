<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio\Traits;

trait WarningLevelTrait
{
    protected function convertWarningLevel($warningLevel)
    {
        switch ($warningLevel) {
            case 1:
                $result = 'Level1';
                break;

            case 2:
                $result = 'Level2';
                break;

            case 3:
                $result = 'Level3';
                break;

            case 4:
                $result = 'Level4';
                break;

            default:
                $result = 'TurnOffAllWarnings';
                break;
        }

        return $result;
    }
}
