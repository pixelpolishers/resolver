<?php

namespace PixelPolishers\Resolver\Config\Lock;

use PixelPolishers\Resolver\Package\PackageInterface;

class Config implements ConfigInterface
{
    /**
     * @var PackageInterface[]
     */
    private $constraints;

    /**
     * @return PackageInterface[]
     */
    public function getConstraints()
    {
        return $this->constraints;
    }

    /**
     * @param PackageInterface[] $constraints
     */
    public function setConstraints($constraints)
    {
        $this->constraints = $constraints;
    }
}
