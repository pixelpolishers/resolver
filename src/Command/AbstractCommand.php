<?php

namespace PixelPolishers\Resolver\Command;

use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Config\Lock\ConfigInterface as LockConfigInterface;
use PixelPolishers\Resolver\Config\Reader\Json as ConfigReader;
use PixelPolishers\Resolver\Config\Lock\Reader\Json as LockConfigReader;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LockConfigInterface
     */
    private $lockConfig;

    /**
     * @return ConfigInterface
     */
    protected function getConfig()
    {
        if (!$this->config) {
            $reader = new ConfigReader();

            $this->config = $reader->read(getcwd() . DIRECTORY_SEPARATOR . 'resolver.json');
        }

        return $this->config;
    }

    /**
     * @return LockConfigInterface
     */
    protected function getLockConfig()
    {
        if (!$this->lockConfig) {
            $reader = new LockConfigReader();

            $this->lockConfig = $reader->read(getcwd() . DIRECTORY_SEPARATOR . 'resolver.lock');
        }

        return $this->lockConfig;
    }
}
