<?php

namespace PixelPolishers\Resolver\Compiler;

use ArrayObject;
use PixelPolishers\Resolver\Config\Element\Configuration;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Config\Element\Source;
use PixelPolishers\Resolver\Config\Loader;
use PixelPolishers\Resolver\Config\Type\Platform;
use PixelPolishers\Resolver\Config\Type\ProjectType;
use PixelPolishers\Resolver\Config\Type\Subsystem;
use PixelPolishers\Resolver\Utils\FileSystem;
use PixelPolishers\Resolver\Variable\Parser;
use RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class Msvc implements CompilerInterface
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var Parser
     */
    private $variableParser;

    /**
     * @var Loader
     */
    private $configLoader;

    /**
     * @var string[]
     */
    private $objectFiles;

    /**
     * Initializes a new instance of this class.
     *
     * @param $basePath The base class of where the tools are located.
     * @param Parser $variableParser The variable parser used to get correct values for configuration options.
     * @param Loader $configLoader The config loader.
     */
    public function __construct($basePath, Parser $variableParser, Loader $configLoader)
    {
        $this->basePath = $basePath;
        $this->variableParser = $variableParser;
        $this->configLoader = $configLoader;
        $this->objectFiles = [];
    }

    /**
     * @return Parser
     */
    public function getVariableParser()
    {
        return $this->variableParser;
    }

    /**
     * @param Project $project The project to compile.
     * @param Configuration $configuration The project configuration to compile.
     */
    public function compile(Project $project, Configuration $configuration)
    {
        $result = new ArrayObject();

        // Set the default configuration values for the given configuration object:
        $this->setDefaultConfigurationValues($configuration);

        // Create the intermediate directory:
        $intermediateDirectory = getcwd() . '\\' . $configuration->getIntermediateDirectory();
        FileSystem::createDirectory($intermediateDirectory);

        // Reset the list with object files since we're going to compile a new set of files.
        $this->objectFiles = [];

        // Compile and register the precompiled header if there is one:
        $precompiledHeader = $this->findPrecompiledHeader($project, $configuration);
        if ($precompiledHeader) {
            $cmd = ['cl', '/c', '/nologo'];
            $cmd = array_merge($cmd, $this->buildStdArguments($project, $configuration));
            $cmd = array_merge($cmd, $this->buildPchArguments($project, $configuration, false));
            $cmd[] = sprintf('"%s"', realpath($precompiledHeader->getSource()));

            $this->registerObjectFile($configuration, $precompiledHeader->getSource());

            $result[] = implode(' ', $cmd);
        }

        $this->buildCompileCommandsForSource($result, $project, $configuration, $project->getSource());
        $this->buildLinkCommand($result, $project, $configuration);

        $this->runCommands($result);
    }

    private function runCommands($commands)
    {
        $script = sprintf('call "%s\\vcvarsall.bat"', $this->basePath);
        foreach ($commands as $command) {
            $script .= ' && ' . $command;
        }

        $process = new Process($script);
        $process->start();
        $process->wait(function ($type, $buffer) {
            if (Process::OUT === $type) {
                echo $buffer;
            }
        });
    }

    private function registerObjectFile(Configuration $configuration, $path)
    {
        $fileExt = pathinfo($path, PATHINFO_EXTENSION);
        $fileName = basename($path, '.' . $fileExt);

        $objFile = getcwd() . '\\' . $configuration->getIntermediateDirectory() . '\\' . $fileName . '.obj';

        $this->objectFiles[] = $objFile;
    }

    private function buildLinkCommand(ArrayObject $result, Project $project, Configuration $configuration)
    {
        switch ($project->getType()) {
            case 'application':
                $cmd = $this->buildLinkApplicationCommand($project, $configuration);
                break;

            case 'dynamic-library':
                $cmd = $this->buildLinkDynamicLibraryCommand($project, $configuration);
                break;

            case 'static-library':
                $cmd = $this->buildLinkStaticLibraryCommand($project, $configuration);
                break;

            default:
                throw new RuntimeException('The project type "' . $project->getType() . '" is not implemented yet.');
        }

        foreach ($this->objectFiles as $path) {
            $cmd[] = $path;
        }

        $result[] = implode(' ', $cmd);
    }

    private function buildLinkApplicationCommand(Project $project, Configuration $configuration)
    {
        return $this->buildLinkExeCommand($project, $configuration, false);
    }

    private function buildLinkDynamicLibraryCommand(Project $project, Configuration $configuration)
    {
        return $this->buildLinkExeCommand($project, $configuration, true);
    }

    private function buildLinkExeCommand(Project $project, Configuration $configuration, $isDLL)
    {
        $outputPath = sprintf(
            '%s\\%s.%s',
            getcwd(),
            $this->variableParser->parse($configuration->getOutputPath()),
            $configuration->getParsedExtension()
        );

        $outputDir = dirname($configuration->getOutputPath());
        FileSystem::createDirectory($outputDir);

        $cmd = ['link', '/nologo', '"/OUT:' . $outputPath . '"'];

        if ($configuration->getDebug()) {
            $cmd[] = '/INCREMENTAL';
        } else {
            $cmd[] = '/INCREMENTAL:NO';
        }

        $cmd[] = 'kernel32.lib';
        $cmd[] = 'user32.lib';
        $cmd[] = 'gdi32.lib';
        $cmd[] = 'winspool.lib';
        $cmd[] = 'comdlg32.lib';
        $cmd[] = 'advapi32.lib';
        $cmd[] = 'shell32.lib';
        $cmd[] = 'ole32.lib';
        $cmd[] = 'oleaut32.lib';
        $cmd[] = 'uuid.lib';
        $cmd[] = 'odbc32.lib';
        $cmd[] = 'odbccp32.lib';

        $intDir = getcwd() . '\\' . $this->variableParser->parse($configuration->getIntermediateDirectory());
        FileSystem::createDirectory($intDir);

        $vendorDir = $this->configLoader->getConfig()->getVendorDirectory();
        foreach ($configuration->getDependencies() as $dependency) {
            $dependencyDir = $vendorDir . '/' . $dependency->getName();
            $dependencyConf = $this->configLoader->getConfig($dependency->getName());

            if (!$dependencyConf) {
                continue;
            }

            foreach ($dependencyConf->getProjects() as $project) {
                if ($project->getType() === ProjectType::APPLICATION) {
                    continue;
                }

                $this->getVariableParser()->push('ide.project', $project);

                $cfgName = $dependency->getConfig() ? $dependency->getConfig() : $configuration->getName();
                foreach ($project->getConfigurations() as $projConf) {
                    if ($projConf->getName() !== $cfgName) {
                        continue;
                    }

                    $this->setDefaultConfigurationValues($projConf);

                    $depOutputPath = sprintf(
                        '%s/%s.%s',
                        $dependencyDir,
                        $this->getVariableParser()->parse($projConf->getOutputPath()),
                        $projConf->getParsedExtension()
                    );

                    $cmd[] = realpath($depOutputPath);
                }

                $this->getVariableParser()->pop('ide.project');
            }
        }

        $cmd = array_merge($cmd, $this->buildLinkManifest($configuration));
        $cmd[] = '/DEBUG';
        $cmd[] = '"/PDB:' . $intDir . '/' . $project->getName() . '.pdb"';
        $cmd[] = $this->buildLinkSubsystem($project);

        if (!$configuration->getDebug()) {
            $cmd[] = '/OPT:REF';
            $cmd[] = '/OPT:ICF';
            $cmd[] = '/LTCG';
        }

        if ($isDLL) {
            $cmd[] = '/DLL';
        }

        $cmd[] = '/TLBID:1';
        $cmd[] = '/DYNAMICBASE';
        $cmd[] = '/NXCOMPAT';
        $cmd[] = '"/IMPLIB:' . $intDir . '/' . $project->getName() . '.lib"';
        $cmd[] = $this->buildLinkPlatform($configuration);

        return $cmd;
    }

    private function buildLinkStaticLibraryCommand(Project $project, Configuration $configuration)
    {
        $outputPath = sprintf(
            '%s\\%s.%s',
            getcwd(),
            $this->variableParser->parse($configuration->getOutputPath()),
            $configuration->getParsedExtension()
        );

        $outputDir = dirname($outputPath);
        FileSystem::createDirectory($outputDir);

        $cmd = ['lib'];
        $cmd[] = '/NOLOGO';
        $cmd[] = '"/OUT:' . $outputPath . '"';

        if (!$configuration->getDebug()) {
            $cmd[] = '/LTCG';
        }

        return $cmd;
    }

    private function buildLinkManifest(Configuration $configuration)
    {
        $outputPath = sprintf(
            '%s\\%s.%s',
            getcwd(),
            $this->variableParser->parse($configuration->getOutputPath()),
            $configuration->getParsedExtension()
        );

        $result = [];
        $result[] = '/MANIFEST';
        $result[] = '"/ManifestFile:' . $outputPath . '.intermediate.manifest"';
        $result[] = '"/MANIFESTUAC:level=\'asInvoker\' uiAccess=\'false\'"';

        return $result;
    }

    private function buildLinkPlatform(Configuration $configuration)
    {
        switch ($configuration->getPlatform()) {
            case Platform::WIN64:
                $result = '/MACHINE:X64';
                break;

            case Platform::WIN32:
                $result = '/MACHINE:X86';
                break;

            default:
                throw new RuntimeException('The platform "' . $configuration->getPlatform() . '" is not supported.');
        }

        return $result;
    }

    private function buildLinkSubsystem(Project $project)
    {
        switch ($project->getSubsystem()) {
            case Subsystem::BOOT:
                $result = '/SUBSYSTEM:BOOT_APPLICATION';
                break;

            case Subsystem::CONSOLE:
                $result = '/SUBSYSTEM:CONSOLE';
                break;

            case Subsystem::NATIVE:
                $result = '/SUBSYSTEM:NATIVE';
                break;

            case Subsystem::POSIX:
                $result = '/SUBSYSTEM:POSIX';
                break;

            case Subsystem::WINDOWS:
                $result = '/SUBSYSTEM:WINDOWS';
                break;

            default:
                throw new RuntimeException('The subsystem "' . $project->getSubsystem() . '" is not supported.');
        }

        return $result;
    }

    private function setDefaultConfigurationValues(Configuration $configuration)
    {
        if (!$configuration->getIntermediateDirectory()) {
            $configuration->setIntermediateDirectory('intermediate/' . $configuration->getName());
        }

        if (!$configuration->getPlatform()) {
            $configuration->setPlatform(Platform::WIN32);
        }
    }

    private function buildCompileCommandsForSource(
        ArrayObject $result,
        Project $project,
        Configuration $configuration,
        Source $source
    )
    {
        foreach ($source->getPaths() as $path) {
            if (is_dir($path)) {
                $this->buildCompileCommandsForDirectory($result, $project, $configuration, $path, $source->getExpand());
            } elseif (is_file($path)) {
                $this->buildCompileCommandForFile($result, $project, $configuration, $path);
            } else {
                throw new RuntimeException('Invalid path provided: ' . $path);
            }
        }
    }

    private function buildCompileCommandsForDirectory(
        ArrayObject $result,
        Project $project,
        Configuration $configuration,
        $path,
        $expand
    )
    {
        foreach (new \DirectoryIterator($path) as $item) {
            if ($item->isDir() && $item->isDot()) {
                continue;
            }

            if ($item->isDir()) {
                if ($expand) {
                    $this->buildCompileCommandsForDirectory(
                        $result,
                        $project,
                        $configuration,
                        $item->getRealPath(),
                        $expand
                    );
                }
            } elseif ($item->isFile()) {
                $this->buildCompileCommandForFile($result, $project, $configuration, $item->getRealPath());
            } else {
                throw new RuntimeException('Invalid path provided: ' . $item->getRealPath());
            }
        }
    }

    private function buildCompileCommandForFile(
        ArrayObject $result,
        Project $project,
        Configuration $configuration,
        $path
    )
    {
        $validExtensions = ['c', 'cpp', 'cxx'];
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (!in_array($extension, $validExtensions)) {
            return;
        }

        // When this is the precompiled header file, we skip it:
        $precompiledHeader = $this->findPrecompiledHeader($project, $configuration);
        if ($precompiledHeader && realpath($precompiledHeader->getSource()) === $path) {
            return;
        }

        $cmd = ['cl', '/c'];
        $cmd = array_merge($cmd, $this->buildStdArguments($project, $configuration));
        if ($precompiledHeader) {
            $cmd = array_merge($cmd, $this->buildPchArguments($project, $configuration, true));
        }
        $cmd[] = sprintf('"%s"', realpath($path));

        $this->registerObjectFile($configuration, $path);

        $result[] = implode(' ', $cmd);
    }

    private function buildStdArguments(Project $project, Configuration $configuration)
    {
        $result = [];

        $result[] = $configuration->getDebug() ? '/ZI' : '/Zi';
        $result[] = $this->buildWarningsArguments($configuration);

        if ($configuration->getDebug()) {
            $result[] = '/Od';
        } else {
            $result[] = '/O2';
            $result[] = '/Oi';
            $result[] = '/GL';
        }
        $result[] = '/Oy-';

        $result = array_merge($result, $this->buildDefineList($configuration));
        $result = array_merge($result, $this->buildIncludeList($configuration));

        if ($configuration->getDebug()) {
            $result[] = '/Gm';
        } else {
            $result[] = '/Gm-';
        }

        $result[] = '/EHsc';

        if ($configuration->getDebug()) {
            $result[] = '/RTC1';
            $result[] = '/MDd';
        } else {
            $result[] = '/MD';
        }

        $result[] = '/GS';
        if (!$configuration->getDebug()) {
            $result[] = '/Gy';
        }

        $result[] = '/fp:precise';
        $result[] = '/Zc:wchar_t';
        $result[] = '/Zc:forScope';

        $result = array_merge($result, $this->buildIntermediateArgument($configuration));
        $result = array_merge($result, $this->buildPDBArgument($project, $configuration));

        $result[] = '/Gd';
        $result[] = '/TP';
        $result[] = '/analyze-';
        $result[] = $this->buildErrorReportingArgument($configuration);

        return $result;
    }

    private function registerDef(&$definitions, $value)
    {
        if (!in_array($value, $definitions)) {
            $definitions[] = $value;
        }
    }

    private function buildDefineList(Configuration $configuration)
    {
        $configType = $configuration->getProject()->getType();
        $definitions = $configuration->getDefinitions();

        // TODO: This is a quick implementation for the proof of concept. We should properly implement this.

        $this->registerDef($definitions, 'WIN32');

        if ($configType === 'application' || $configType === 'dynamic-library') {
            $this->registerDef($definitions, '_WINDOWS');
        }

        if ($configType === 'application') {
            $this->registerDef($definitions, '_MBCS');
        }

        if ($configuration->getDebug()) {
            $this->registerDef($definitions, '_DEBUG');
        } else {
            $this->registerDef($definitions, 'NDEBUG');
        }

        if ($configuration->getProject()->getType() === 'static-library') {
            $this->registerDef($definitions, '_LIB');
        }

        if ($configuration->getProject()->getType() === 'dynamic-library') {
            $this->registerDef($definitions, '_USRDLL');
            $this->registerDef($definitions, 'DLLTEST_EXPORTS');
            $this->registerDef($definitions, '_WINDLL');
        }

        $this->registerDef($definitions, 'UNICODE');
        $this->registerDef($definitions, '_UNICODE');

        $result = [];
        foreach ($definitions as $definition) {
            $result[] = '/D "' . $definition . '"';
        }
        return $result;
    }


    private function buildErrorReportingArgument(Configuration $configuration)
    {
        if ($configuration->getMsvcErrorReport()) {
            switch ($configuration->getMsvcErrorReport()) {
                case 'none':
                    return '/ERRORREPORT:NONE';

                case 'prompt':
                    return '/ERRORREPORT:PROMPT';

                case 'queue':
                    return '/ERRORREPORT:QUEUE';

                case 'send':
                    return '/ERRORREPORT:SEND';

                default:
                    throw new RuntimeException('Invalid msvcErrorReport provided.');
            }
        }
        return '';
    }

    private function buildIncludeList(Configuration $configuration)
    {
        $result = [];

        if ($configuration->getProject()->getPaths()) {
            foreach ($configuration->getProject()->getPaths()->getInclude() as $path) {
                $result[] = sprintf('/I"%s"', realpath($path));
            }
        }

        if ($configuration->getPaths()) {
            foreach ($configuration->getPaths()->getInclude() as $path) {
                $result[] = sprintf('/I"%s"', realpath($path));
            }
        }

        // TODO: Add the dependency include paths:

        return $result;
    }

    private function buildIntermediateArgument(Configuration $configuration)
    {
        $intermediateDir = getcwd() . '\\' . $configuration->getIntermediateDirectory();

        return ['/Fo"' . $intermediateDir . '/"'];
    }

    private function buildPDBArgument(Project $project, Configuration $configuration)
    {
        $intermediateDir = getcwd() . '\\' . $configuration->getIntermediateDirectory();

        return ['/Fd"' . $intermediateDir . '/' . $project->getName() . '.pdb"'];
    }

    private function buildPchArguments(Project $project, Configuration $configuration, $use)
    {
        $result = [];
        $intDir = $configuration->getIntermediateDirectory();

        $precompiledHeader = $this->findPrecompiledHeader($project, $configuration);
        $precompiledHeaderName = $precompiledHeader->getName() ?: $project->getName() . '.pch';

        $result[] = '/Fp"' . $intDir . '\\' . $precompiledHeaderName . '"';

        if ($precompiledHeader->getMemory()) {
            $result[] = '/Zm' . $precompiledHeader->getMemory();
        }

        if ($use) {
            $result[] = '/Yu"' . $precompiledHeader->getHeader() . '"';
        } else {
            $result[] = '/Yc"' . $precompiledHeader->getHeader() . '"';
        }

        return $result;
    }

    private function buildWarningsArguments(Configuration $configuration)
    {
        $result = [];

        if ($configuration->getWarningsAsErrors()) {
            $result[] = '/WX-';
        }

        $configuration->setWarningLevel($configuration->getWarningLevel() || 'level3');

        switch ($configuration->getWarningLevel()) {
            case 'all':
                $result[] = '/Wall';
                break;
            case 'none':
                $result[] = '/W0';
                break;
            case 'level1':
                $result[] = '/W1';
                break;
            case 'level2':
                $result[] = '/W2';
                break;
            case 'level3':
                $result[] = '/W3';
                break;
            case 'level4':
                $result[] = '/W4';
                break;
        }

        return implode(' ', $result);
    }

    private function findPrecompiledHeader(Project $project, Configuration $configuration)
    {
        $pch = $configuration->getPrecompiledHeader();
        if (!$pch) {
            $pch = $project->getPrecompiledHeader();
        }
        return $pch;
    }
}
