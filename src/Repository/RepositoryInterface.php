<?php

namespace PixelPolishers\Resolver\Repository;

use Composer\Semver\Constraint\ConstraintInterface;
use PixelPolishers\Resolver\Package\PackageInterface;

interface RepositoryInterface
{
    public function hasPackage(PackageInterface $package);

    /**
     * Finds the package with the given name.
     *
     * @param string $name The name of the package to lookup.
     * @param ConstraintInterface $constraint The constraint of the package to lookup.
     * @return PackageInterface|null Returns null when no package has been found.
     */
    public function findPackage($name, ConstraintInterface $constraint);

    public function findPackages($name, ConstraintInterface $constraint = null);

    public function getPackages();

    public function search($query, $mode = 0);
}
