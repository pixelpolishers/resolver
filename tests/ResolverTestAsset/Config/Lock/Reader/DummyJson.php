<?php

namespace PixelPolishers\ResolverTestAsset\Config\Lock\Reader;

use PixelPolishers\Resolver\Config\Lock\Reader\Json;

class DummyJson extends Json
{
    public function read($path)
    {
        return $this->parseContent($path);
    }
}
