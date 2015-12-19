<?php

namespace PixelPolishers\Resolver\Generator\VisualStudio;

use DOMDocument;
use DOMElement;
use PixelPolishers\Resolver\Config\ConfigInterface;
use PixelPolishers\Resolver\Config\Element\Configuration;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Generator\VisualStudio\Traits\CharacterSetTrait;
use PixelPolishers\Resolver\Generator\VisualStudio\Traits\ConfigurationTypeTrait;
use PixelPolishers\Resolver\Generator\VisualStudio\Traits\PlatformTrait;
use PixelPolishers\Resolver\Generator\VisualStudio\Traits\WarningLevelTrait;
use PixelPolishers\Resolver\Source\Filter;
use PixelPolishers\Resolver\Source\FilterParser;
use PixelPolishers\Resolver\Utils\FileSystem;
use PixelPolishers\Resolver\Utils\XmlDom;
use PixelPolishers\Resolver\Variable\Parser;
use RuntimeException;

abstract class AbstractProjectGenerator
{
    use CharacterSetTrait;
    use ConfigurationTypeTrait;
    use PlatformTrait;
    use WarningLevelTrait;

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
     * @var DOMDocument
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

    /**
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }

    public function generate()
    {
        $root = $this->dom->appendChild($this->dom->createElement('Project'));

        $this->writeRootAttributes($root);
        $this->writeItemGroupProjectConfig($root);
        $this->writePropertyGroupGlobals($root);
        $this->writeImportProject($root, '$(VCTargetsPath)\\Microsoft.Cpp.Default.props');

        foreach ($this->project->getConfigurations() as $configuration) {
            $this->variableParser->push('ide.config', $configuration);
            $this->writePropertyGroupConfiguration($root, $configuration);
            $this->variableParser->pop('ide.config');
        }

        $this->writeImportProject($root, '$(VCTargetsPath)\\Microsoft.Cpp.props');
        $this->writeImportGroupExtensionSettings($root);

        foreach ($this->project->getConfigurations() as $configuration) {
            $this->variableParser->push('ide.config', $configuration);
            $this->writeImportGroupPropertySheets($root, $configuration);
            $this->variableParser->pop('ide.config');
        }

        $this->writePropertyGroupUserMacros($root);

        foreach ($this->project->getConfigurations() as $configuration) {
            $this->variableParser->push('ide.config', $configuration);
            $this->writePropertyGroup($root, $configuration);
            $this->variableParser->pop('ide.config');
        }

        foreach ($this->project->getConfigurations() as $configuration) {
            $this->variableParser->push('ide.config', $configuration);
            $this->writeItemDefinitionGroup($root, $configuration);
            $this->variableParser->pop('ide.config');
        }

        $filterParser = new FilterParser();
        $this->writeItemGroupFilemap($root, $filterParser->parse($this->project->getSource()));

        $this->writeImportProject($root, '$(VCTargetsPath)\\Microsoft.Cpp.targets');
        $this->writeImportGroupExtensionTargets($root);

        $this->dom->formatOutput = true;
        $this->dom->save($this->path);
    }

    protected function writeImportGroupExtensionTargets(DOMElement $parent)
    {
        XmlDom::createElement($parent, 'ImportGroup', null, [
            'Label' => 'ExtensionTargets'
        ]);
    }

    protected function writeItemGroupFilemap(
        DOMElement $parent,
        Filter $filter,
        DOMElement $incGroup = null,
        DOMElement $srcGroup = null,
        DOMElement $resGroup = null,
        DOMElement $ignGroup = null
    ) {
        if ($filter->getIncludeFiles()) {
            $incGroup = $incGroup ?: $parent->appendChild($this->dom->createElement('ItemGroup'));

            foreach ($filter->getIncludeFiles() as $path) {
                XmlDom::createElement($incGroup, 'ClInclude', null, [
                    'Include' => $path,
                ]);
            }
        }

        if ($filter->getCompileFiles()) {
            $srcGroup = $srcGroup ?: $parent->appendChild($this->dom->createElement('ItemGroup'));

            foreach ($filter->getCompileFiles() as $path) {
                $element = XmlDom::createElement($srcGroup, 'ClCompile', null, [
                    'Include' => $path,
                ]);

                $this->writePrecompiledHeader($element, $path);
            }
        }

        if ($filter->getResourceFiles()) {
            $resGroup = $resGroup ?: $parent->appendChild($this->dom->createElement('ItemGroup'));

            foreach ($filter->getResourceFiles() as $path) {
                XmlDom::createElement($resGroup, 'ResourceCompile', null, [
                    'Include' => $path,
                ]);
            }
        }

        if ($filter->getIgnoreFiles()) {
            $ignGroup = $ignGroup ?: $parent->appendChild($this->dom->createElement('ItemGroup'));

            foreach ($filter->getIgnoreFiles() as $path) {
                XmlDom::createElement($resGroup, 'ResourceCompile', null, [
                    'Include' => $path,
                ]);
            }
        }

        if ($filter->getFilters()) {
            foreach ($filter->getFilters() as $subFilter) {
                $this->writeItemGroupFilemap($parent, $subFilter, $incGroup, $srcGroup, $resGroup, $ignGroup);
            }
        }
    }

    protected function writePrecompiledHeader(DOMElement $parent, $path)
    {
        foreach ($this->project->getConfigurations() as $configuration) {
            $this->variableParser->push('ide.config', $configuration);

            $this->writePrecompiledHeaderConfiguration($parent, $path, $configuration);

            $this->variableParser->pop('ide.config');
        }
    }

    protected function writePrecompiledHeaderConfiguration(DOMElement $parent, $path, Configuration $configuration)
    {
        $pch = $configuration->getPrecompiledHeader();
        if (!$pch) {
            $pch = $this->project->getPrecompiledHeader();
        }

        if (!$pch) {
            return;
        }

        $src = realpath($pch->getSource());
        if (!is_file($src)) {
            throw new RuntimeException(sprintf('Failed to find file "%s"', $pch->getSource()));
        }

        if ($src === $path) {
            XmlDom::createElement($parent, 'PrecompiledHeader', 'Create', [
                'Condition' => $this->buildCondition($configuration),
            ]);
        }
    }

    protected function writeItemDefinitionGroup(DOMElement $parent, Configuration $configuration)
    {
        $element = XmlDom::createElement($parent, 'ItemDefinitionGroup', null, [
            'Condition' => $this->buildCondition($configuration)
        ]);

        $this->writeCompileGroup($element, $configuration);

        if ($this->project->getType() === 'application') {
            $this->writeLinkGroup($element, $configuration);
        } else {
            $this->writeLibGroup($element, $configuration);
        }
    }

    protected function writeLibGroup(DOMElement $parent, Configuration $configuration)
    {
        $libElement = XmlDom::createElement($parent, 'Lib');

        $this->writeAdditionalDependencies($libElement, $configuration);
    }

    protected function writeLinkGroup(DOMElement $parent, Configuration $configuration)
    {
        $linkElement = XmlDom::createElement($parent, 'Link');

        $this->writeSubSystem($linkElement, $this->project);

        XmlDom::createElement($linkElement, 'GenerateDebugInformation', $configuration->getDebug() ? 'true' : 'false');

        if (!$configuration . getDebug()) {
            XmlDom::createElement($linkElement, 'EnableCOMDATFolding', 'true');
            XmlDom::createElement($linkElement, 'OptimizeReferences', 'true');
        }

        if ($configuration->getWarningsAsErrors()) {
            XmlDom::createElement($linkElement, 'TreatLinkerWarningAsErrors', 'true');
        }

        $this->writeAdditionalDependencies($linkElement, $configuration);
    }

    protected function writeSubSystem(DOMElement $parent, Project $project)
    {
        switch ($project->getSubsystem()) {
            case 'boot':
                XmlDom::createElement($parent, 'SubSystem', 'Boot');
                break;

            case 'console':
                XmlDom::createElement($parent, 'SubSystem', 'Console');
                break;

            case 'native':
                XmlDom::createElement($parent, 'SubSystem', 'Native');
                break;

            case 'posix':
                XmlDom::createElement($parent, 'SubSystem', 'POSIX');
                break;

            case 'windows':
                XmlDom::createElement($parent, 'SubSystem', 'Windows');
                break;

            default:
                throw new RuntimeException(sprintf(
                    'The subsystem "%s" is not supported.',
                    $project->getSubsystem()
                ));
        }
    }

    protected function isSystemDependency($name)
    {
        //var index = name.indexOf('.lib');

        //return index !== -1 && index === name.length - 4;

        return false;
    }

    protected function writeAdditionalDependencies(DOMElement $parent, Configuration $configuration)
    {
        $dependencies = [];

        // TODO

        if (count($dependencies) !== 0) {
            $dependencies[] = '%(AdditionalDependencies)';
            XmlDom::createElement($parent, 'AdditionalDependencies', implode(';', $dependencies));
        }

        // TODO: Implement this:
        //<AdditionalLibraryDirectories>dir;dir;dir</AdditionalLibraryDirectories>
    }

    protected function writePropertyGroup(DOMElement $parent, Configuration $configuration)
    {
        $element = XmlDom::createElement($parent, 'PropertyGroup', null, [
            'Condition' => $this->buildCondition($configuration),
        ]);
        
        XmlDom::createElement($element, 'LinkIncremental', $configuration->getDebug() ? 'true' : 'false');

        $this->writePathList($element, 'IncludePath', 'include', $configuration);
        $this->writePathList($element, 'LibraryPath', 'library', $configuration);
        $this->writePathList($element, 'ExecutablePath', 'executable', $configuration);
        $this->writePathList($element, 'SourcePath', 'source', $configuration);
        $this->writePathList($element, 'ReferencePath', 'reference', $configuration);
        $this->writePathList($element, 'ExcludePath', 'exclude', $configuration);

        $outputExt = $configuration->getParsedExtension();
        $outputExtWithDot = '.' . $outputExt;
        $outputPath = $this->variableParser->parse($configuration->getOutputName()) . $outputExt;
        $outputDirectory = dirname($outputPath);

        $intDir = $configuration->getIntermediateDirectory();
        if (!$intDir) {
            $intDir = sprintf('intermediate\\%s', $configuration->getName());
        } else {
            $intDir = $this->variableParser->parse($intDir);
        }

        FileSystem::createDirectory($outputDirectory);
        FileSystem::createDirectory($intDir);

        XmlDom::createElement($element, 'OutDir', FileSystem::getRelativePath(
            $this->config->getProjectsDirectory() . '/vs2010',
            $outputDirectory . '/bla/bla2/'
        ) . '\\');

        XmlDom::createElement($element, 'IntDir', FileSystem::getRelativePath(
            $this->config->getProjectsDirectory(),
            $intDir
        ) . '\\');

        XmlDom::createElement($element, 'TargetName', basename($outputPath, $outputExtWithDot));
        XmlDom::createElement($element, 'TargetExt', $outputExt);
    }

    protected function writePathList(DOMElement $parent, $name, $key, Configuration $configuration)
    {
        $projectDir = $this->config->getProjectsDirectory();
        $paths = [$this->project->getPaths(), $configuration->getPaths()];
    }

    protected function writePropertyGroupUserMacros(DOMElement $parent)
    {
        XmlDom::createElement($parent, 'PropertyGroup', null, [
            'Label' => 'UserMacros',
        ]);
    }

    protected function writeImportGroupPropertySheets(DOMElement $parent, Configuration $configuration)
    {
        $element = XmlDom::createElement($parent, 'ImportGroup', null, [
            'Label' => 'PropertySheets',
            'Condition' => $this->buildCondition($configuration),
        ]);

        $import = $this->writeImportProject($element, '$(UserRootDir)\\Microsoft.Cpp.$(Platform).user.props');
        $import->setAttribute('Condition', 'exists(\'$(UserRootDir)\\Microsoft.Cpp.$(Platform).user.props\')');
        $import->setAttribute('Label', 'LocalAppDataPlatform');
    }

    protected function writeImportGroupExtensionSettings(DOMElement $parent)
    {
        XmlDom::createElement($parent, 'ImportGroup', null, [
            'Label' => 'ExtensionSettings'
        ]);
    }

    protected function writePropertyGroupConfiguration(DOMElement $parent, Configuration $configuration)
    {
        $element = XmlDom::createElement($parent, 'PropertyGroup', null, [
            'Condition' => $this->buildCondition($configuration),
            'Label' => 'Configuration',
        ]);

        $this->writePropertyGroupConfigurationElements($element, $configuration);
    }

    protected function writePropertyGroupConfigurationElements(DOMElement $parent, Configuration $configuration)
    {
        XmlDom::createElement($parent, 'ConfigurationType', $this->convertConfigurationType($this->project->getType()));
        XmlDom::createElement($parent, 'UseDebugLibraries', $configuration->getDebug() ? 'true' : 'false');

        if (!$configuration->getDebug()) {
            XmlDom::createElement($parent, 'WholeProgramOptimization', 'true');
        }

        XmlDom::createElement($parent, 'CharacterSet', $this->convertCharacterSet($configuration->getCharacterSet()));
    }

    protected function writeImportProject(DOMElement $parent, $value)
    {
        return XmlDom::createElement($parent, 'Import', null, [
            'Project' => $value,
        ]);
    }

    protected function writePropertyGroupGlobals(DOMElement $parent)
    {
        $element = XmlDom::createElement($parent, 'PropertyGroup', null, [
            'Label' => 'Globals',
        ]);

        $this->writePropertyGroupGlobalsElements($element);
    }

    protected function writePropertyGroupGlobalsElements(DOMElement $parent)
    {
        XmlDom::createElement($parent, 'ProjectGuid', $this->project->getUuid());
        XmlDom::createElement($parent, 'Keyword', 'Win32Proj');
        XmlDom::createElement($parent, 'RootNamespace', $this->project->getName());
    }

    protected function writeItemGroupProjectConfig(DOMElement $parent)
    {
        /** @var DOMElement $element */
        $element = $parent->appendChild($this->dom->createElement('ItemGroup'));
        $element->setAttribute('Label', 'ProjectConfigurations');

        foreach ($this->project->getConfigurations() as $configuration) {
            $this->variableParser->push('ide.config', $configuration);

            $this->writeProjectConfig($element, $configuration);

            $this->variableParser->pop('ide.config');
        }
    }

    protected function writeProjectConfig(DOMElement $parent, Configuration $configuration)
    {
        $platform = $this->convertPlatform($configuration->getPlatform());

        $element = $parent->appendChild($this->dom->createElement('ProjectConfiguration'));
        $element->setAttribute('Include', $configuration->getName() . '|' . $platform);

        XmlDom::createElement($element, 'Configuration', $configuration->getName());
        XmlDom::createElement($element, 'Platform', $platform);
    }

    protected function writeCompileGroup(DOMElement $parent, Configuration $configuration)
    {
        $compileElement = XmlDom::createElement($parent, 'ClCompile');

        XmlDom::createElement($compileElement, 'WarningLevel', $this->convertWarningLevel($configuration->getWarningLevel()));
        XmlDom::createElement($compileElement, 'Optimization', $configuration->getDebug() ? 'Disabled' : 'MaxSpeed');

        if (!$configuration->getDebug()) {
            XmlDom::createElement($compileElement, 'FunctionLevelLinking', 'true');
            XmlDom::createElement($compileElement, 'IntrinsicFunctions', 'true');
        }

        $this->writePreprocessorDefinitions($compileElement, $configuration);

        if ($configuration->getPrecompiledHeader()) {
            $pch = $configuration->getPrecompiledHeader();
        } elseif ($this->project->getPrecompiledHeader()) {
            $pch = $this->project->getPrecompiledHeader();
        } else {
            $pch = null;
        }

        if ($pch) {
            XmlDom::createElement($compileElement, 'PrecompiledHeader', 'Use');
            XmlDom::createElement($compileElement, 'PrecompiledHeaderFile', $pch->getHeader());
        } else {
            XmlDom::createElement($compileElement, 'PrecompiledHeader', 'NotUsing');
        }
    }

    protected function writePreprocessorDefinitions(DOMElement $parent, Configuration $configuration)
    {
        $definitions = array_merge($this->project->getDefinitions(), $configuration->getDefinitions());

        if ($configuration->getDebug()) {
            $definitions[] = '_DEBUG';
        } else {
            $definitions[] = 'NDEBUG';
        }

        $definitions[] = '%(PreprocessorDefinitions)';

        $uniqueDefinitions = array_unique($definitions);

        XmlDom::createElement($parent, 'PreprocessorDefinitions', implode(';', $uniqueDefinitions));
    }

    protected function writeRootAttributes(DOMElement $parent)
    {
        $parent->setAttribute('DefaultTargets', 'Build');
        $parent->setAttribute('ToolsVersion', '4.0');
        $parent->setAttribute('xmlns', 'http://schemas.microsoft.com/developer/msbuild/2003');
    }

    protected function buildCondition(Configuration $configuration)
    {
        $result = '\'$(Configuration)|$(Platform)\'==\'';
        $result .= $configuration->getName();
        $result .= '|';
        $result .= $this->convertPlatform($configuration->getPlatform());
        $result .= '\'';
        return $result;
    }
}
