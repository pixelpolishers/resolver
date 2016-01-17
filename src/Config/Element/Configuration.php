<?php

namespace PixelPolishers\Resolver\Config\Element;

use PixelPolishers\Resolver\Config\Element\Traits\DefinitionsTrait;
use PixelPolishers\Resolver\Config\Element\Traits\PathsTrait;
use PixelPolishers\Resolver\Config\Element\Traits\PrecompiledHeaderTrait;
use PixelPolishers\Resolver\Config\Type\Platform;

class Configuration
{
    use DefinitionsTrait;
    use PrecompiledHeaderTrait;
    use PathsTrait;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var string
     */
    private $characterSet;

    /**
     * @var boolean
     */
    private $debug;

    /**
     * @var ConfigurationDependency[]
     */
    private $dependencies;

    /**
     * @var string
     */
    private $intermediateDirectory;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $outputPath;

    /**
     * @var string
     */
    private $platform;

    /**
     * @var int
     */
    private $warningLevel;

    /**
     * @var bool
     */
    private $warningsAsErrors;

    /**
     * @var bool
     */
    private $msvcErrorReport;

    /**
     * Initializes a new instance of this class.
     *
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->dependencies = [];
    }

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return string
     */
    public function getCharacterSet()
    {
        return $this->characterSet;
    }

    /**
     * @param string $characterSet
     */
    public function setCharacterSet($characterSet)
    {
        $this->characterSet = $characterSet;
    }

    /**
     * @return boolean
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * @param boolean $debug
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }

    /**
     * @return ConfigurationDependency[]
     */
    public function getDependencies()
    {
        return $this->dependencies;
    }

    /**
     * @param ConfigurationDependency[] $dependencies
     */
    public function setDependencies($dependencies)
    {
        $this->dependencies = $dependencies;
    }

    /**
     * @return string
     */
    public function getIntermediateDirectory()
    {
        return $this->intermediateDirectory;
    }

    /**
     * @param string $intermediateDirectory
     */
    public function setIntermediateDirectory($intermediateDirectory)
    {
        $this->intermediateDirectory = $intermediateDirectory;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getOutputPath()
    {
        return $this->outputPath;
    }

    /**
     * @param string $outputPath
     */
    public function setOutputPath($outputPath)
    {
        $this->outputPath = $outputPath;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return int
     */
    public function getWarningLevel()
    {
        return $this->warningLevel;
    }

    /**
     * @param int $warningLevel
     */
    public function setWarningLevel($warningLevel)
    {
        $this->warningLevel = $warningLevel;
    }

    public function getParsedExtension()
    {
        switch ($this->getProject()->getType()) {
            case 'application':
                $outputExt = Platform::isWindows($this->getPlatform()) ? 'exe' : '';
                break;

            case 'dynamic-library':
                $outputExt = Platform::isWindows($this->getPlatform()) ? 'dll' : 'so';
                break;

            case 'static-library':
                $outputExt = Platform::isWindows($this->getPlatform()) ? 'lib' : 'a';
                break;

            default:
                $outputExt = null;
                break;
        }

        return $outputExt;
    }

    /**
     * @return boolean
     */
    public function getWarningsAsErrors()
    {
        return $this->warningsAsErrors;
    }

    /**
     * @param boolean $warningsAsErrors
     */
    public function setWarningsAsErrors($warningsAsErrors)
    {
        $this->warningsAsErrors = $warningsAsErrors;
    }

    /**
     * @return boolean
     */
    public function getMsvcErrorReport()
    {
        return $this->msvcErrorReport;
    }

    /**
     * @param boolean $msvcErrorReport
     */
    public function setMsvcErrorReport($msvcErrorReport)
    {
        $this->msvcErrorReport = $msvcErrorReport;
    }
}
