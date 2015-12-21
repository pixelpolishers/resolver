<?php

namespace PixelPolishers\Resolver\Command;

use JsonSchema\RefResolver;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Validate extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('validate');
        $this->setDescription('Validates the resolver.json file for validity.');
        $this->addArgument('path', InputArgument::OPTIONAL, 'The path to the resolver.json file to validate.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        if (!$path) {
            $path = getcwd() . DIRECTORY_SEPARATOR . 'resolver.json';
        }

        if (!is_file($path)) {
            $output->writeln(sprintf('<error>The file "%s" does not exists.</error>', $path));
            return 1;
        }

        // Get the schema and data as objects:
        $schema = json_decode(file_get_contents(__DIR__ . '/../../resources/schema.json'));
        $data = json_decode(file_get_contents($path));

        // Validate:
        $validator = new Validator();
        $validator->check($data, $schema);

        if ($validator->isValid()) {
            $output->writeln($path . ' is valid');
            return 0;
        }

        $output->writeln(sprintf('<error>The file "%s" contains errors.</error>', $path));
        foreach ($validator->getErrors() as $error) {
            if ($error['property']) {
                $output->writeln(sprintf('- %s: %s', $error['property'], $error['message']));
            } else {
                $output->writeln(sprintf('- %s', $error['message']));
            }
        }

        return 1;
    }
}
