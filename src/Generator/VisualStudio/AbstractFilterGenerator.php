<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio;

use DOMDocument;
use DOMElement;
use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Source\Filter;
use PixelPolishers\Resolver\Source\FilterParser;
use PixelPolishers\Resolver\Utils\UUID;
use PixelPolishers\Resolver\Utils\XmlDom;
use PixelPolishers\Resolver\Variable\Parser;

abstract class AbstractFilterGenerator
{
    /**
     * @var path
     */
    private $path;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Parser
     */
    private $variableParser;

    /**
     * @var DOMDoscument
     */
    private $dom;

    public function __construct($path, Project $project, ConfigInterface $config, Parser $variableParser)
    {
        $this->path = $path;
        $this->project = $project;
        $this->config = $config;
        $this->variableParser = $variableParser;
        $this->dom = new DOMDocument('1.0', 'UTF-8');
    }

    public function generate()
    {
        $root = $this->dom->appendChild($this->dom->createElement('Project'));
        $root->setAttribute('ToolsVersion', '4.0');
        $root->setAttribute('xmlns', 'http://schemas.microsoft.com/developer/msbuild/2003');

        $filterParser = new FilterParser();
        $filter = $filterParser->parse($this->project->getSource());

        $this->writeItemGroupFilters($root, $filter);
        $this->writeItemGroupNone($root, $filter);
        $this->writeItemGroupIncludes($root, $filter);
        $this->writeItemGroupSource($root, $filter);
        $this->writeItemGroupResources($root, $filter);

        $this->dom->formatOutput = true;
        $this->dom->save($this->path);
    }

    protected function writeItemGroupFilters(DOMElement $parent, Filter $filter)
    {
        $element = XmlDom::createElement($parent, 'ItemGroup');

        $this->writeItemGroupFilter($element, $filter);
    }

    protected function writeItemGroupFilter(DOMElement $parent, Filter $filter)
    {
        if ($filter->getName()) {
            $filterElement = XmlDom::createElement($parent, 'Filter');
            $filterElement->setAttribute('Include', $this->writeFilterName($filter));

            XmlDom::createElement($filterElement, 'UniqueIdentifier', UUID::createV4());
            XmlDom::createElement($filterElement, 'SourceControlFiles', 'True');
            XmlDom::createElement($filterElement, 'ParseFiles', 'True');

            if ($filter->getExtensions()) {
                XmlDom::createElement($filterElement, 'Extensions', implode(';', $filter->getExtensions()));
            }
        }

        foreach ($filter->getFilters() as $subFilter) {
            $this->writeItemGroupFilter($parent, $subFilter);
        }
    }

    protected function writeItemGroupNone(DOMElement $parent, Filter $filter)
    {
        $element = XmlDom::createElement($parent, 'ItemGroup');

        $this->writeItemGroupContent($element, $filter, 'None', 'ignore');
    }

    protected function writeItemGroupIncludes(DOMElement $parent, Filter $filter)
    {
        $element = XmlDom::createElement($parent, 'ItemGroup');

        $this->writeItemGroupContent($element, $filter, 'ClInclude', 'include');
    }

    protected function writeItemGroupSource(DOMElement $parent, Filter $filter)
    {
        $element = XmlDom::createElement($parent, 'ItemGroup');

        $this->writeItemGroupContent($element, $filter, 'ClCompile', 'compile');
    }

    protected function writeItemGroupResources(DOMElement $parent, Filter $filter)
    {
        $element = XmlDom::createElement($parent, 'ItemGroup');

        $this->writeItemGroupContent($element, $filter, 'ResourceCompile', 'resource');
    }

    protected function writeItemGroupContent(DOMElement $parent, Filter $filter, $elementName, $key)
    {
        foreach ($filter->getFiles($key) as $item) {
            $element = XmlDom::createElement($parent, $elementName);
            $element->setAttribute('Include', $item);

            if ($filter->getName()) {
                XmlDom::createElement($element, 'Filter', $this->writeFilterName($filter));
            }
        }

        foreach ($filter->getFilters() as $subFilter) {
            $this->writeItemGroupContent($parent, $subFilter, $elementName, $key);
        }
    }

    protected function writeFilterName(Filter $filter)
    {
        $filterName = $filter->getName();

        while ($filter && $filter->getParent() && $filter->getParent()->getName()) {
            $filterName = $filter->getParent()->getName() . '\\' . $filterName;

            $filter = $filter->getParent();
        }

        return $filterName;
    }
}
