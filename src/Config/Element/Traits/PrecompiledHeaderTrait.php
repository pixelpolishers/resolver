<?php

namespace PixelPolishers\Resolver\Config\Element\Traits;

use PixelPolishers\Resolver\Config\Element\PrecompiledHeader;

trait PrecompiledHeaderTrait
{
    /*
     * @var PrecompiledHeader|null
     */
    private $precompiledHeader;

    /**
     * @return PrecompiledHeader
     */
    public function getPrecompiledHeader()
    {
        return $this->precompiledHeader;
    }

    /**
     * @param PrecompiledHeader|null $precompiledHeader
     */
    public function setPrecompiledHeader(PrecompiledHeader $precompiledHeader = null)
    {
        $this->precompiledHeader = $precompiledHeader;
    }
}
