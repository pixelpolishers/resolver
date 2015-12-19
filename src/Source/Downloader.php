<?php

namespace PixelPolishers\Resolver\Source;

use PixelPolishers\Resolver\Package\PackageInterface;
use PixelPolishers\Resolver\Source\Handler\Git;
use PixelPolishers\Resolver\Source\Handler\Zip;
use PixelPolishers\Resolver\Source\Handler\HandlerInterface;
use PixelPolishers\Resolver\Utils\FileSystem;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class Downloader
{
    private $handlers;
    private $vendorDirectory;

    public function __construct($vendorDirectory)
    {
        $this->handlers = [];
        $this->vendorDirectory = $vendorDirectory;

        $this->addHandler('git', new Git());
        $this->addHandler('zip', new Zip());
    }

    public function addHandler($name, HandlerInterface $handler)
    {
        $this->handlers[$name] = $handler;
    }

    public function downloadPackage(OutputInterface $output, PackageInterface $package)
    {
        $source = $package->getSource();

        if (!array_key_exists($source->getType(), $this->handlers)) {
            throw new RuntimeException(sprintf(
                'Unknown source type "%s", cannot download package "%s".',
                $source->getType(),
                $package->getName()
            ));
        }

        $targetDirectory = $this->getTargetDirectory($package);

        $this->handlers[$source->getType()]->download($output, $package, $targetDirectory);
    }

    private function getTargetDirectory(PackageInterface $package)
    {
        if (!is_dir($this->vendorDirectory)) {
            if (file_exists($this->vendorDirectory)) {
                throw new RuntimeException($this->vendorDirectory . ' exists and is not a directory.');
            }

            if (!@mkdir($this->vendorDirectory, 0777, true)) {
                throw new RuntimeException($this->vendorDirectory . ' does not exist and could not be created.');
            }
        }

        $targetDirectory = $this->vendorDirectory . DIRECTORY_SEPARATOR . $package->getName();

        FileSystem::createDirectory($targetDirectory);

        return $targetDirectory;
    }
}
