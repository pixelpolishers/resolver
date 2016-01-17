<?php

namespace PixelPolishers\ResolverTestAsset\Config\Reader;

use PixelPolishers\Resolver\Config\Reader\Json;

class DummyJson extends Json
{
    public function read($path)
    {
        return $this->parseContent($path);
    }
}
