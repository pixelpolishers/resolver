<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio\Vs2015;

use DOMElement;
use PixelPolishers\Resolver\Config\Element\Configuration;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Generator\VisualStudio\AbstractProjectGenerator;
use PixelPolishers\Resolver\Utils\XmlDom;

class ProjectGenerator extends AbstractProjectGenerator
{
    protected function writePropertyGroupConfigurationElements(DOMElement $parent, Configuration $configuration)
    {
        parent::writePropertyGroupConfigurationElements($parent, $configuration);

        XmlDom::createElement($parent, 'PlatformToolset', 'v140');
    }

    protected function writePropertyGroupGlobalsElements(DOMElement $parent)
    {
        XmlDom::createElement($parent, 'ProjectGuid', '{' . $this->getProject()->getUuid() . '}');
        XmlDom::createElement($parent, 'Keyword', 'Win32Proj');
        XmlDom::createElement($parent, 'RootNamespace', $this->getProject()->getName());
    }
}
