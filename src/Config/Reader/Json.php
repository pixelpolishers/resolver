<?php

namespace PixelPolishers\Resolver\Config\Reader;

class Json extends AbstractReader
{
    protected function parseContent($path)
    {
        $content = file_get_contents($path);

        return json_decode($content, true);
    }
}
