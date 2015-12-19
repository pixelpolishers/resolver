<?php

namespace PixelPolishers\Resolver\Dependency;

use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\VersionParser;
use PixelPolishers\Resolver\Repository\Manager;
use RuntimeException;

class DependencyResolver
{
    /**
     * @var Manager
     */
    private $manager;

    /**
     * @var PackageInterface[]
     */
    private $dependencies;

    /**
     * @var VersionParser
     */
    private $versionParser;

    /**
     * Initializes a new instance of this class.
     *
     * @param Manager $manager
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        $this->dependencies = [];
        $this->versionParser = new VersionParser();
    }

    public function resolve($name, $constraint, $isDevelopment)
    {
        $name = strtolower($name);

        if (array_key_exists($name, $this->dependencies)) {
            return;
        }

        if (!$constraint instanceof ConstraintInterface) {
            $constraint = $this->versionParser->parseConstraints($constraint);
        }

        $package = $this->manager->findPackage($name, $constraint);
        if (!$package) {
            throw new RuntimeException(sprintf('Unable to find package "%s"', $name));
        }

        foreach ($package->getDependencies() as $dependencyName => $dependencyVersion) {
            $this->resolve($dependencyName, $dependencyVersion, false);
        }

        foreach ($package->getDevelopmentDependencies() as $dependencyName => $dependencyVersion) {
            $this->resolve($dependencyName, $dependencyVersion, true);
        }

        $package->setDevelopmentPackage($isDevelopment);

        $this->dependencies[$name] = $package;
    }

    /**
     * @return bool
     */
    public function hasResolvedDependencies()
    {
        return count($this->dependencies) !== 0;
    }

    /**
     * @return PackageInterface[]
     */
    public function getResolvedDependencies()
    {
        return $this->dependencies;
    }
}
