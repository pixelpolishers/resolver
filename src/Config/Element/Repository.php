<?php

namespace PixelPolishers\Resolver\Config\Element;

class Repository
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var string[]
     */
    private $params;

    /**
     * @param string $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getParam($name)
    {
        return $this->params[$name];
    }

    /**
     * @return string[]
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @param string[] $params
     */
    public function setParams($params)
    {
        $this->params = $params;
    }
}
