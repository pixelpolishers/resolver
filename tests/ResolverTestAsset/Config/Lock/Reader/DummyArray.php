<?php

namespace PixelPolishers\ResolverTestAsset\Config\Lock\Reader;

use PixelPolishers\Resolver\Config\Lock\Reader\AbstractReader;

class DummyArray extends AbstractReader
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function read($path)
    {
        return $this->readFile($path);
    }

    protected function parseContent($path)
    {
        return $this->data;
    }
}
