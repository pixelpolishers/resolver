<?php

namespace PixelPolishers\Resolver\Config;

use PixelPolishers\Resolver\Config\Lock\ConfigInterface as LockConfigInterface;
use PixelPolishers\Resolver\Config\Reader\Json as ConfigReader;
use PixelPolishers\Resolver\Config\Lock\Reader\Json as LockConfigReader;

class Loader
{
    /**
     * @var ConfigInterface[]
     */
    private $cache;

    /**
     * @var LockConfigInterface[]
     */
    private $lockCache;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var LockConfigReader
     */
    private $configLockReader;

    /**
     * Initializes a new instance of the Loader class.
     */
    public function __construct()
    {
        $this->cache = [];
        $this->lockCache = [];
        $this->configReader = new ConfigReader();
        $this->configLockReader = new LockConfigReader();
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig($package = null)
    {
        if (!array_key_exists($package, $this->cache)) {
            $path = $this->getWorkingDirectory($package) . DIRECTORY_SEPARATOR . 'resolver.json';

            $this->cache[$package] = $this->configReader->read($path);
        }

        return $this->cache[$package];
    }

    /**
     * @return LockConfigInterface
     */
    public function getLock($package = null)
    {
        if (!array_key_exists($package, $this->lockCache)) {
            $path = $this->getWorkingDirectory($package) . DIRECTORY_SEPARATOR . 'resolver.lock';

            $this->lockCache[$package] = $this->configLockReader->read($path);
        }

        return $this->lockCache[$package];
    }

    /**
     * @param string|null $package
     * @return string
     */
    public function getWorkingDirectory($package = null)
    {
        $path = getcwd();

        if ($package) {
            $defaultConfig = $this->getConfig();
            $fullName = $defaultConfig->getVendor() . '/' . $defaultConfig->getName();
            if ($fullName !== $package) {
                $path .= DIRECTORY_SEPARATOR . $defaultConfig->getVendorDirectory() . DIRECTORY_SEPARATOR . $package;
            }
        }

        return $path;
    }
}
