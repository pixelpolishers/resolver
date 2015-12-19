<?php

namespace PixelPolishers\Resolver\Source;

use DirectoryIterator;
use PixelPolishers\Resolver\Config\Element\Source;

class FilterParser
{
    private $compileExtensions;
    private $includeExtensions;
    private $resourceExtensions;

    public function __construct()
    {
        $this->compileExtensions = ['c', 'cpp'];
        $this->includeExtensions = ['h', 'hpp', 'inl'];
        $this->resourceExtensions = ['rc'];
    }

    public function parse(Source $source)
    {
        return $this->parseSource($source);
    }

    private function parseSource(Source $source, Filter $parentFilter = null)
    {
        $filter = new Filter($parentFilter, $source->getName(), $source->getExtensions());

        foreach ($source->getSources() as $subSource) {
            $this->parseSource($subSource, $filter);
        }

        foreach ($source->getPaths() as $path) {
            if (is_dir($path)) {
                $this->parseDirectory($path, $filter, $source->getExpand());
            } elseif (is_file($path)) {
                $filter->addFile($path);
            } else {
                throw new RuntimeException(sprintf('The path "%s" does not exists.', $path));
            }
        }

        return $filter;
    }

    private function parseDirectory($path, Filter $filter, $expand)
    {
        foreach (new DirectoryIterator($path) as $item) {
            if ($item->isFile()) {
                $this->parseFile($filter, $item->getRealPath());
                continue;
            }

            if (!$item->isDot() && $item->isDir() && $expand) {
                $subFilter = new Filter($filter, $item->getFilename(), $filter->getExtensions());

                $this->parseDirectory($item->getPathname(), $subFilter, $expand);

                $filter->addFilter($subFilter);
            }
        }

        return $filter;
    }

    private function parseFile(Filter $filter, $itemPath)
    {
        $extension = pathinfo($itemPath, PATHINFO_EXTENSION);
        
        if (in_array($extension, $this->compileExtensions)) {
            $filter->addCompileFile($itemPath);
        } elseif (in_array($extension, $this->includeExtensions)) {
            $filter->addIncludeFile($itemPath);
        } elseif (in_array($extension, $this->resourceExtensions)) {
            $filter->addResourceFile($itemPath);
        } else {
            $filter->addIgnoreFile($itemPath);
        }
    }
}
