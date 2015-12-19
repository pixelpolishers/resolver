<?php

namespace PixelPolishers\Resolver\Config\Element\Traits;

use PixelPolishers\Resolver\Config\Element\Paths;

trait PathsTrait
{
    /*
     * @var Paths|null
     */
    private $paths;

    /**
     * @return Paths|null
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * @param Paths|null $paths
     */
    public function setPaths(Paths $paths = null)
    {
        $this->paths = $paths;
    }
}
