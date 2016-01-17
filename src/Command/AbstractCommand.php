<?php

namespace PixelPolishers\Resolver\Command;

use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Config\Loader;
use PixelPolishers\Resolver\Config\Lock\ConfigInterface as LockConfigInterface;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @return Loader
     */
    protected function getConfigLoader()
    {
        if ($this->loader === null) {
            $this->loader = new Loader();
        }
        return $this->loader;
    }

    /**
     * @return ConfigInterface
     */
    protected function getConfig()
    {
        return $this->getConfigLoader()->getConfig();
    }

    /**
     * @return LockConfigInterface
     */
    protected function getLockConfig()
    {
        return $this->getConfigLoader()->getLock();
    }
}
