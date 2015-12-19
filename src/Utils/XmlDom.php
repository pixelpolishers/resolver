<?php

namespace PixelPolishers\Resolver\Utils;

use DOMElement;

class XmlDom
{
    public static function createElement(DOMElement $parent, $name, $value = null, array $attributes = [])
    {
        $dom = $parent->ownerDocument;

        $element = $parent->appendChild($dom->createElement($name));

        if ($value !== null) {
            $element->appendChild($dom->createTextNode($value));
        }

        foreach ($attributes as $name => $value) {
            $element->setAttribute($name, $value);
        }

        return $element;
    }
}
