<?php

namespace PixelPolishers\Resolver\Repository;

use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\ConstraintInterface;
use PixelPolishers\Resolver\Package\PackageInterface;

class Manager
{
    /**
     * @var RepositoryInterface[]
     */
    private $repositories;

    public function __construct()
    {
        $this->repositories = [];
    }

    public function addRepository(RepositoryInterface $repository)
    {
        $this->repositories[] = $repository;
    }

    public function getRepositories()
    {
        return $this->repositories;
    }

    /**
     * @param string $name
     * @param ConstraintInterface $constraint
     * @return PackageInterface|null
     */
    public function findPackage($name, ConstraintInterface $constraint)
    {
        foreach ($this->getRepositories() as $repository) {
            $package = $repository->findPackage($name, $constraint);

            if ($package) {
                return $package;
            }
        }

        return null;
    }

    public function findPackages($name, $constraint)
    {
        $packages = array();

        foreach ($this->getRepositories() as $repository) {
            $packages = array_merge($packages, $repository->findPackages($name, $constraint));
        }

        return $packages;
    }
}
