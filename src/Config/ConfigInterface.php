<?php

namespace PixelPolishers\Resolver\Config;

use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Config\Element\Repository;

interface ConfigInterface
{
    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return boolean
     */
    public function getHideSolutionNode();

    /**
     * @return string
     */
    public function getLicense();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return Project[]
     */
    public function getProjects();

    /**
     * @return Repository[]
     */
    public function getRepositories();

    /**
     * @return string
     */
    public function getVendor();

    /**
     * @return string
     */
    public function getVendorDirectory();

    /**
     * @return string
     */
    public function getProjectsDirectory();
}
