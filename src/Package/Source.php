<?php

namespace PixelPolishers\Resolver\Package;

class Source
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $reference;

    /**
     * Initializes a new instance of this class.
     *
     * @param string $type
     * @param string $url
     * @param string $reference
     */
    public function __construct($type, $url, $reference)
    {
        $this->type = $type;
        $this->url = $url;
        $this->reference = $reference;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getReference()
    {
        return $this->reference;
    }
}
