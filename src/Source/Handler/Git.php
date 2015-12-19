<?php

namespace PixelPolishers\Resolver\Source\Handler;

use PixelPolishers\Resolver\Package\PackageInterface;
use PixelPolishers\Resolver\Package\Source;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessUtils;

class Git implements HandlerInterface
{
    public function download(OutputInterface $output, PackageInterface $package, $path)
    {
        $source = $package->getSource();

        $gitPath = $path . '/.git';

        if (!is_dir($gitPath)) {
            $this->cloneRepository($source, $path);
        }

        $this->checkoutReference($source, $path);
    }

    private function cloneRepository(Source $source, $path)
    {
        $process = new Process(sprintf(
            'git clone --no-checkout %s %s',
            ProcessUtils::escapeArgument($source->getUrl()),
            ProcessUtils::escapeArgument($path)
        ));

        $process->run();
    }

    private function checkoutReference(Source $source, $path)
    {
        $process = new Process(sprintf(
            'git checkout -f %s -- && git reset --hard %1$s --',
            ProcessUtils::escapeArgument($source->getReference())
        ), realpath($path));

        $process->run();

        if (preg_match('/fatal: reference is not a tree: (.+)/', $process->getErrorOutput(), $matches)) {
            throw new RuntimeException('Failed to checkout ' . $source->getReference());
        }
    }
}
