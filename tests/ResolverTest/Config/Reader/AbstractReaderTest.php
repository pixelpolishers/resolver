<?php

namespace PixelPolishers\ResolverTest\Config\Reader;

use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use PixelPolishers\Resolver\Config\Element\Dependency;
use PixelPolishers\Resolver\Config\Element\Project;
use PixelPolishers\Resolver\Config\Element\Repository;
use PixelPolishers\Resolver\Config\Config;
use PixelPolishers\Resolver\Config\Reader\Json;
use PixelPolishers\Resolver\Config\Type\ProjectType;
use PixelPolishers\Resolver\Config\Type\Subsystem;
use PixelPolishers\ResolverTestAsset\Config\Reader\DummyArray;

class AbstractReaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::read
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::readFile
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testReadWithNonExistingFile()
    {
        // Assert
        $this->setExpectedException('InvalidArgumentException', 'The path "invalid" does not exists.');

        // Arrange
        $reader = new Json();

        // Act
        $reader->read('invalid');
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::read
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::readFile
     */
    public function testReadWithValidFile()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertInstanceOf(Config::class, $result);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseName
     */
    public function testParseDataWithMissingVendor()
    {
        // Assert
        $this->setExpectedException('RuntimeException', 'Invalid name, missing vendor.');

        // Arrange
        $reader = new Json();

        // Act
        $reader->read(__DIR__ . '/../../../ResolverTestAsset/missing-vendor-in-name.json');
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataWithMissingName()
    {
        // Assert
        $this->setExpectedException('RuntimeException', 'The name property is not set.');

        // Arrange
        $reader = new Json();

        // Act
        $reader->read(__DIR__ . '/../../../ResolverTestAsset/missing-name.json');
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseName
     */
    public function testParseDataName()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('name', $result->getName());
        $this->assertEquals('vendor', $result->getVendor());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseDefinitions
     */
    public function testParseDataDefinitions()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals(['test1', 'test2'], $result->getDefinitions());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseDescription
     */
    public function testParseDataDescription()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('description', $result->getDescription());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseLicense
     */
    public function testParseDataLicense()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('MIT', $result->getLicense());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectsDirectory
     */
    public function testParseDataProjectsDir()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('my-projects', $result->getProjectsDirectory());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjects
     */
    public function testParseDataProjects()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');
        $projects = $result->getProjects();

        // Assert
        $this->assertCount(2, $projects);
        $this->assertInstanceOf(Project::class, $projects[0]);
        $this->assertInstanceOf(Project::class, $projects[1]);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectConfigurationsData
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectConfigurations
     */
    public function testParseProjectConfigurationsData()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'configurations' => [
                        [
                            'debug' => true,
                            'definitions' => ['a', 'b', 'c'],
                            'intermediate-dir' => 'my-intermediate',
                            'name' => 'my-name',
                            'output-name' => 'my-output-name',
                            'output-ext' => 'my-output-ext',
                            'paths' => [
                                'exclude' => ['my-exclude'],
                                'executable' => ['my-executable'],
                                'include' => ['my-include'],
                                'library' => ['my-library'],
                                'reference' => ['my-reference'],
                                'source' => ['my-source'],
                            ],
                            'platform' => 'win32',
                            'warning-level' => 4,
                        ],
                    ],
                ]
            ],
        ]);

        // Act
        $configurations = $reader->read('')->findProject('project1')->getConfigurations();
        $configuration = $configurations[0];

        // Assert
        $this->assertTrue($configuration->getDebug());
        $this->assertEquals(['a', 'b', 'c'], $configuration->getDefinitions());
        $this->assertEquals('my-intermediate', $configuration->getIntermediateDirectory());
        $this->assertEquals('my-name', $configuration->getName());
        $this->assertEquals('my-output-name', $configuration->getOutputPath());
        $this->assertEquals('my-output-ext', $configuration->getOutputExtension());
        $this->assertEquals('win32', $configuration->getPlatform());
        $this->assertEquals(4, $configuration->getWarningLevel());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectConfigurations
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parsePrecompiledHeader
     */
    public function testParseProjectPrecompiledHeaderData()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'configurations' => [
                        [
                            'pch' => [
                                'header' => 'pch-header',
                                'memory' => 1337,
                                'name' => 'pch-name',
                                'source' => 'pch-source',
                            ],
                        ],
                    ],
                ]
            ],
        ]);

        // Act
        $configurations = $reader->read('')->findProject('project1')->getConfigurations();
        $pch = $configurations[0]->getPrecompiledHeader();

        // Assert
        $this->assertEquals('pch-header', $pch->getHeader());
        $this->assertEquals(1337, $pch->getMemory());
        $this->assertEquals('pch-name', $pch->getName());
        $this->assertEquals('pch-source', $pch->getSource());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectConfigurations
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseConfigurationDependencies
     */
    public function testParseConfigurationDependencies()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'configurations' => [
                        [
                            'dependencies' => [
                                'a',
                                'b',
                                [
                                    'name' => 'my-name',
                                    'config' => 'my-config',
                                ],
                                'c',
                                'd',
                            ],
                        ],
                    ],
                ]
            ],
        ]);

        // Act
        $configurations = $reader->read('')->findProject('project1')->getConfigurations();
        $configuration = $configurations[0];

        // Assert
        $this->assertCount(5, $configuration->getDependencies());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectName
     */
    public function testParseProjectName()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $projects = $result->getProjects();

        // Assert
        $this->assertEquals('project1', $projects[0]->getName());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectDefinitions
     */
    public function testParseProjectDefinitions()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'definitions' => ['a', 'b', 'c'],
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $projects = $result->getProjects();

        // Assert
        $this->assertEquals(['a', 'b', 'c'], $projects[0]->getDefinitions());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectSubsystem
     */
    public function testParseProjectSubsystem()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'subsystem' => 'test',
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $subsystem = $result->findProject("project1")->getSubsystem();

        // Assert
        $this->assertEquals('test', $subsystem);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectSubsystem
     */
    public function testParseProjectSubsystemDefaultValue()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $subsystem = $result->findProject("project1")->getSubsystem();

        // Assert
        $this->assertEquals(Subsystem::CONSOLE, $subsystem);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectType
     */
    public function testParseProjectType()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'type' => 'test',
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $type = $result->findProject("project1")->getType();

        // Assert
        $this->assertEquals('test', $type);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectType
     */
    public function testParseProjectTypeDefaultValue()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $type = $result->findProject("project1")->getType();

        // Assert
        $this->assertEquals(ProjectType::APPLICATION, $type);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectDependenciesData
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectDependencies
     */
    public function testParseProjectDependenciesData()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');
        $dependencies = $result->findProject("project1")->getDependencies();

        // Assert
        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(Dependency::class, $dependencies[0]);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectDevelopmentDependenciesData
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectDependencies
     */
    public function testParseProjectDevelopmentDependenciesData()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');
        $dependencies = $result->findProject("project1")->getDevelopmentDependencies();

        // Assert
        $this->assertCount(1, $dependencies);
        $this->assertInstanceOf(Dependency::class, $dependencies[0]);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectDependencies
     */
    public function testParseProjectDependenciesWithMissingName()
    {
        // Assert
        $this->setExpectedException(InvalidArgumentException::class);

        // Arrange
        $reader = new Json();

        // Act
        $reader->read(__DIR__ . '/../../../ResolverTestAsset/missing-project-dependency-name.json');
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectDependencies
     */
    public function testParseProjectDependenciesWithMissingVersion()
    {
        // Assert
        $this->setExpectedException(InvalidArgumentException::class);

        // Arrange
        $reader = new Json();

        // Act
        $reader->read(__DIR__ . '/../../../ResolverTestAsset/missing-project-dependency-version.json');
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseData
     */
    public function testParseDataRepositories()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertCount(2, $result->getRepositories());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseVendorDirectory
     */
    public function testParseDataVendorDir()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');

        // Assert
        $this->assertEquals('my-vendor', $result->getVendorDirectory());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseRepositories
     */
    public function testParseRepositories()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/full.json');
        $repositories = $result->getRepositories();
        $repositoriy = $repositories[0];

        // Assert
        $this->assertInstanceOf(Repository::class, $repositoriy);
        $this->assertEquals('repository', $repositoriy->getType());
        $this->assertEquals(['param1' => 'value1', 'param2' => 'value2'], $repositoriy->getParams());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseRepositories
     */
    public function testParseRepositoriesWithMissingType()
    {
        // Arrange
        $reader = new Json();

        // Act
        $result = $reader->read(__DIR__ . '/../../../ResolverTestAsset/missing-repository-type.json');

        // Assert
        $this->assertCount(0, $result->getRepositories());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectPathsData
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parsePaths
     */
    public function testParsePaths()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'paths' => [
                        'exclude' => ['my-exclude'],
                        'executable' => ['my-executable'],
                        'include' => ['my-include'],
                        'library' => ['my-library'],
                        'reference' => ['my-reference'],
                        'source' => ['my-source'],
                    ],
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $paths = $result->findProject('project1')->getPaths();

        // Assert
        $this->assertNotNull($paths);
        $this->assertEquals(['my-exclude'], $paths->getExclude());
        $this->assertEquals(['my-executable'], $paths->getExecutable());
        $this->assertEquals(['my-include'], $paths->getInclude());
        $this->assertEquals(['my-library'], $paths->getLibrary());
        $this->assertEquals(['my-reference'], $paths->getReference());
        $this->assertEquals(['my-source'], $paths->getSource());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parsePrecompiledHeader
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectPrecompiledHeaderData
     */
    public function testParsePrecompiledHeader()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'pch' => [
                        'header' => 'pch-header',
                        'memory' => 1337,
                        'name' => 'pch-name',
                        'source' => 'pch-source',
                    ],
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $pch = $result->findProject('project1')->getPrecompiledHeader();

        // Assert
        $this->assertNotNull($pch);
        $this->assertEquals('pch-header', $pch->getHeader());
        $this->assertEquals(1337, $pch->getMemory());
        $this->assertEquals('pch-name', $pch->getName());
        $this->assertEquals('pch-source', $pch->getSource());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectSourceData
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseSource
     */
    public function testParseSource()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'source' => [
                        'extensions' => ['cpp', 'hpp', 'h'],
                        'name' => 'source-name',
                        'paths' => ['my-path'],
                    ],
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $source = $result->findProject('project1')->getSource();

        // Assert
        $this->assertNotNull($source);
        $this->assertEquals(['cpp', 'hpp', 'h'], $source->getExtensions());
        $this->assertEquals('source-name', $source->getName());
        $this->assertEquals(['my-path'], $source->getPaths());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectSourceData
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseSource
     */
    public function testParseSourceWithExpand()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'source' => [
                        'sources' => true,
                    ],
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $source = $result->findProject('project1')->getSource();

        // Assert
        $this->assertNotNull($source);
        $this->assertTrue($source->getExpand());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseProjectSourceData
     * @covers PixelPolishers\Resolver\Config\Reader\AbstractReader::parseSource
     */
    public function testParseSourceWithRecursion()
    {
        // Arrange
        $reader = new DummyArray([
            'name' => 'vendor/name',
            'projects' => [
                [
                    'name' => 'project1',
                    'source' => [
                        'sources' => [
                            [
                                'extensions' => ['cpp', 'hpp', 'h'],
                                'name' => 'source-name',
                                'paths' => ['my-path'],
                            ]
                        ],
                    ],
                ]
            ],
        ]);

        // Act
        $result = $reader->read('');
        $source = $result->findProject('project1')->getSource();

        // Assert
        $this->assertNotNull($source);
        $this->assertCount(1, $source->getSources());
    }
}
