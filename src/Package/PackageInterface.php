<?php

namespace PixelPolishers\Resolver\Package;

use Composer\Semver\Constraint\ConstraintInterface;

interface PackageInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return ConstraintInterface
     */
    public function getVersion();

    /**
     * @return Source
     */
    public function getSource();

    /**
     * @return bool
     */
    public function getDevelopmentPackage();

    /**
     * @return string[]
     */
    public function getDependencies();

    /**
     * @return string[]
     */
    public function getDevelopmentDependencies();
}
