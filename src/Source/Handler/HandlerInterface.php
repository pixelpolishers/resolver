<?php

namespace PixelPolishers\Resolver\Source\Handler;

use PixelPolishers\Resolver\Package\PackageInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface HandlerInterface
{
    public function download(OutputInterface $output, PackageInterface $package, $path);
}
