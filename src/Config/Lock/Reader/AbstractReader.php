<?php

namespace PixelPolishers\Resolver\Config\Lock\Reader;

use Composer\Semver\VersionParser;
use InvalidArgumentException;
use PixelPolishers\Resolver\Config\Lock\Config;
use PixelPolishers\Resolver\Package\Package;
use PixelPolishers\Resolver\Package\Source;

abstract class AbstractReader implements ReaderInterface
{
    private $versionParser;

    public function __construct()
    {
        $this->versionParser = new VersionParser();
    }

    public function read($path)
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('The path "%s" does not exists.', $path));
        }

        return $this->readFile($path);
    }

    protected function readFile($path)
    {
        $data = $this->parseContent($path);

        $config = new Config();

        $this->parseData($config, $data);

        return $config;
    }

    abstract protected function parseContent($path);

    protected function parseData(Config $config, $data)
    {
        if (array_key_exists('constraints', $data)) {
            $constraints = $this->parseConstraints($data['constraints']);

            $config->setConstraints($constraints);
        }
    }

    protected function parseConstraints(array $data)
    {
        $result = [];

        foreach ($data as $name => $item) {
            $package = new Package();
            $package->setName($name);
            $package->setVersion($this->versionParser->parseConstraints($item['version']));
            $package->setSource(new Source(
                $item['source']['type'],
                $item['source']['url'],
                $item['source']['reference']
            ));
            $package->setDevelopmentPackage($item['development']);

            $result[] = $package;
        }

        return $result;
    }
}
