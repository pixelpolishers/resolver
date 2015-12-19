<?php

namespace PixelPolishers\Resolver\Source;

class Filter
{
    private $parent;
    private $name;
    private $extensions;
    private $filters;
    private $compileFiles;
    private $includeFiles;
    private $resourceFiles;
    private $ignoreFiles;

    public function __construct(Filter $parent = null, $name = null, array $extensions = [])
    {
        $this->parent = $parent;
        $this->name = $name;
        $this->extensions = $extensions;
        $this->filters = [];
        $this->compileFiles = [];
        $this->includeFiles = [];
        $this->resourceFiles = [];
        $this->ignoreFiles = [];
    }

    /**
     * @return Filter
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param Filter $filter
     */
    public function addFilter(Filter $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return string[]
     */
    public function getFiles($key)
    {
        switch ($key) {
            case 'compile':
                $result = $this->getCompileFiles();
                break;

            case 'ignore':
                $result = $this->getIgnoreFiles();
                break;

            case 'include':
                $result = $this->getIncludeFiles();
                break;

            case 'resource':
                $result = $this->getResourceFiles();
                break;

            default:
                $result = [];
                break;
        }

        return $result;
    }

    /**
     * @param string $path
     */
    public function addCompileFile($path)
    {
        $this->compileFiles[] = $path;
    }

    /**
     * @return string[]
     */
    public function getCompileFiles()
    {
        return $this->compileFiles;
    }

    /**
     * @param string $path
     */
    public function addIncludeFile($path)
    {
        $this->includeFiles[] = $path;
    }

    /**
     * @return string[]
     */
    public function getIncludeFiles()
    {
        return $this->includeFiles;
    }

    /**
     * @param string $path
     */
    public function addResourceFile($path)
    {
        $this->resourceFiles[] = $path;
    }

    /**
     * @return string[]
     */
    public function getResourceFiles()
    {
        return $this->resourceFiles;
    }

    /**
     * @param string $path
     */
    public function addIgnoreFile($path)
    {
        $this->ignoreFiles[] = $path;
    }

    /**
     * @return string[]
     */
    public function getIgnoreFiles()
    {
        return $this->ignoreFiles;
    }
}
