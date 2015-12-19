<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio\Vs2010;

use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Generator\VisualStudio\AbstractGenerator;
use PixelPolishers\Resolver\Utils\UUID;

class Generator extends AbstractGenerator
{
    public function generate($targetDirectory)
    {
        $solutionPath = sprintf('%s/%s.sln', $targetDirectory, $this->getConfig()->getName());
        $solutionGenerator = new SolutionGenerator($solutionPath, $this->getConfig());

        foreach ($this->getConfig()->getProjects() as $project) {
            $project->setUuid(UUID::createV4());

            $this->getVariableParser()->push('ide.project', $project);

            $this->generateProjectFile($project, $targetDirectory);
            $this->generateFilterFile($project, $targetDirectory);

            $this->getVariableParser()->pop('ide.project');
        }

        $solutionGenerator->generate();
    }

    protected function generateProjectFile(Project $project, $targetDirectory)
    {
        $path = sprintf('%s/%s.vcxproj', $targetDirectory, $project->getName());

        $generator = new ProjectGenerator(
            $path,
            $project,
            $this->getConfig(),
            $this->getVariableParser()
        );

        $generator->generate();
    }

    protected function generateFilterFile(Project $project, $targetDirectory)
    {
        $path = sprintf('%s/%s.vcxproj.filters', $targetDirectory, $project->getName());

        $generator = new FilterGenerator(
            $path,
            $project,
            $this->getConfig(),
            $this->getVariableParser()
        );

        $generator->generate();
    }
}
