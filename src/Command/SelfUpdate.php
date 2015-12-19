<?php

namespace PixelPolishers\Resolver\Command;

use Exception;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdate extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('self-update');
        $this->setDescription('Updates the binary with the latest version.');

        $this->addOption(
            'rollback',
            null,
            InputOption::VALUE_NONE,
            'Rollsback the updated binrary to the last version.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exitCode = 0;

        $updater = new Updater();
        $updater->getStrategy()->setPharUrl('https://pixelpolishers.github.io/resolver/resolver.phar');
        $updater->getStrategy()->setVersionUrl('https://pixelpolishers.github.io/resolver/resolver.phar.version');

        try {
            if ($input->getOption('rollback')) {
                $result = $updater->rollback();
            } else {
                $result = $updater->update();
            }

            if ($result) {
                $new = $updater->getNewVersion();
                $old = $updater->getOldVersion();

                $output->writeln(sprintf('Updated from %s to %s', $old, $new));
            } else {
                $exitCode = 1;
            }

        } catch (Exception $e) {
            $exitCode = 1;

            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
        }

        return $exitCode;
    }
}
