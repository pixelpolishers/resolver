<?php

namespace PixelPolishers\Resolver\Repository;

use Composer\Semver\Constraint\Constraint;
use Composer\Semver\Constraint\ConstraintInterface;
use Composer\Semver\VersionParser;
use PixelPolishers\Resolver\Package\Package as PackageEntity;
use PixelPolishers\Resolver\Package\PackageInterface;
use PixelPolishers\Resolver\Package\Source;
use RuntimeException;

class Package implements RepositoryInterface
{
    private $url;
    private $packages;
    private $versionParser;

    public function __construct($url)
    {
        $this->url = $url;
        $this->versionParser = new VersionParser();
    }

    /**
     * Finds the package with the given name.
     *
     * @param string $name The name of the package to lookup.
     * @param ConstraintInterface $constraint The constraint of the package to lookup.
     * @return PackageInterface|null Returns null when no package has been found.
     */
    public function findPackage($name, ConstraintInterface $constraint)
    {
        $this->load();

        foreach ($this->packages as $packageName => $packageVersions) {
            if ($packageName !== $name) {
                continue;
            }

            foreach ($packageVersions as $packageVersion => $package) {
                $pkgConstraint = $this->versionParser->parseConstraints($packageVersion);

                if ($constraint->matches($pkgConstraint)) {
                    $package['name'] = $packageName;
                    $package['version'] = $packageVersion;
                    return $this->buildPackage($package);
                }
            }
        }

        return null;
    }

    public function findPackages($name, ConstraintInterface $constraint = null)
    {
    }

    public function getPackages()
    {
    }

    public function hasPackage(PackageInterface $package)
    {
        return false;
    }

    public function search($query, $mode = 0)
    {
    }

    private function load()
    {
        if ($this->packages) {
            return;
        }

        if (!extension_loaded('openssl') && 'https' === substr($this->url, 0, 5)) {
            throw new RuntimeException(sprintf(
                'You must enable the openssl extension in your php.ini to load information from %s',
                $this->url
            ));
        }

        $result = @file_get_contents($this->url, false, stream_context_create([
            'http' => [
                'method' => "GET",
                'header' => "Accept: application/json"
            ],
        ]));

        // When the server cannot be reached, we initialize the packages to a default value.
        if (!$result) {
            $this->packages = [];
            return;
        }

        $data = json_decode($result, true);

        $this->packages = $data['packages'];
    }

    private function buildPackage(array $data)
    {
        $result = new PackageEntity();
        $result->setName($data['name']);
        $result->setVersion($this->versionParser->parseConstraints($data['version']));
        $result->setSource(new Source($data['source']['type'], $data['source']['url'], $data['source']['reference']));

        if (array_key_exists('dependencies', $data)) {
            $result->setDependencies((array)$data['dependencies']);
        }

        if (array_key_exists('dependencies-dev', $data)) {
            $result->setDevelopmentDependencies((array)$data['dependencies-dev']);
        }

        return $result;
    }
}
