<?php

namespace PixelPolishers\Resolver\Variable;

class Parser
{
    private $data;
    private $stack;

    public function __construct()
    {
        $this->data = [];
        $this->stack = [];
    }

    public function set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function push($type, $value)
    {
        $this->stack[$type][] = $value;
    }

    public function pop($type)
    {
        array_pop($this->stack[$type]);
    }

    public function parse($input)
    {
        $regex = '/\$\(([^\)]+)\)/i';

        $count = 1;
        $output = $input;
        while ($count > 0) {
            $output = preg_replace_callback($regex, array($this, 'onParse'), $output, -1, $count);
        }

        return $output;
    }

    public function onParse(array $matches)
    {
        $result = null;

        if ($this->parseDate($matches[1], $result)) {
            return $result;
        }

        if ($this->parseVariables($matches[1], $result)) {
            return $result;
        }

        if ($this->parseStack($matches[1], $result)) {
            return $result;
        }

        return $matches[1];
    }

    private function parseDate($input, &$result)
    {
        if ($input === 'date.unixtime') {
            $result = time();
        } elseif (!preg_match('/^date\.([A-Za-z]+)$/', $input, $matches)) {
            $result = null;
        } else {
            $result = date($matches[1]);
        }

        return $result !== null;
    }

    private function parseStack($input, &$result)
    {
        foreach ($this->stack as $name => $stack) {
            if (strpos($input, $name) !== 0) {
                continue;
            }

            $object = $stack[count($stack) - 1];
            $field = substr($input, strlen($name) + 1);

            $method = 'get' . ucfirst($field);

            $result = call_user_func([$object, $method]);
            return true;
        }

        return false;
    }

    private function parseVariables($input, &$result)
    {
        if (!array_key_exists($input, $this->data)) {
            return false;
        }

        $result = $this->data[$input];
        return true;
    }
}
