<?php

namespace PixelPolishers\Resolver;

use PixelPolishers\Resolver\Command\Compile;
use PixelPolishers\Resolver\Command\Generate;
use PixelPolishers\Resolver\Command\Install;
use PixelPolishers\Resolver\Command\SelfUpdate;
use PixelPolishers\Resolver\Command\Update;
use PixelPolishers\Resolver\Command\Validate;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Application extends BaseApplication
{
    const VERSION = '@package_version@';

    public function __construct()
    {
        parent::__construct('Resolver', self::VERSION);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if (extension_loaded('xdebug') && !getenv('RESOLVER_DISABLE_XDEBUG_WARN')) {
            $output->writeln(sprintf(
                '<question>%s</question>',
                'You are running resolver with xdebug enabled. This has a major impact on runtime performance.'
            ));
            $output->writeln('');
        }

        $oldWorkingDir = getcwd();
        $newWorkingDir = $this->getNewWorkingDir($input);

        if ($newWorkingDir) {
            chdir($newWorkingDir);
        }

        $result = parent::doRun($input, $output);

        chdir($oldWorkingDir);

        return $result;
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new Compile();
        $commands[] = new Generate();
        $commands[] = new Install();
        $commands[] = new SelfUpdate();
        $commands[] = new Update();
        $commands[] = new Validate();

        return $commands;
    }

    protected function getDefaultInputDefinition()
    {
        $definition = parent::getDefaultInputDefinition();
        $definition->addOption(new InputOption(
            '--working-dir',
            '-w',
            InputOption::VALUE_REQUIRED,
            'If specified, use the given directory as working directory.'
        ));

        return $definition;
    }

    private function getNewWorkingDir(InputInterface $input)
    {
        $workingDir = $input->getParameterOption(array('--working-dir', '-w'));

        if (false !== $workingDir && !is_dir($workingDir)) {
            throw new \RuntimeException('Invalid working directory specified, ' . $workingDir . ' does not exist.');
        }

        return $workingDir;
    }
}
