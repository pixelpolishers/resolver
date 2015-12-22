<?php

namespace PixelPolishers\ResolverTest\Config;

use PHPUnit_Framework_TestCase;
use PixelPolishers\Resolver\Config\Config;
use PixelPolishers\Resolver\Config\Element\Project;

class ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PixelPolishers\Resolver\Config\Config::__construct
     */
    public function testConstructorInitializesVendorDirectory()
    {
        // Arrange
        // ...

        // Act
        $config = new Config();

        // Assert
        $this->assertEquals('vendor', $config->getVendorDirectory());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::__construct
     */
    public function testConstructorInitializesProjectsDirectory()
    {
        // Arrange
        // ...

        // Act
        $config = new Config();

        // Assert
        $this->assertEquals('projects', $config->getProjectsDirectory());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getDescription
     * @covers PixelPolishers\Resolver\Config\Config::setDescription
     */
    public function testDescriptionGetterSetter()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setDescription('test');

        // Assert
        $this->assertEquals('test', $config->getDescription());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getHideSolutionNode
     * @covers PixelPolishers\Resolver\Config\Config::setHideSolutionNode
     */
    public function testHideSolutionNodeGetterSetterWithTrue()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setHideSolutionNode(true);

        // Assert
        $this->assertTrue($config->getHideSolutionNode());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getHideSolutionNode
     * @covers PixelPolishers\Resolver\Config\Config::setHideSolutionNode
     */
    public function testHideSolutionNodeGetterSetterWithFalse()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setHideSolutionNode(false);

        // Assert
        $this->assertFalse($config->getHideSolutionNode());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getHideSolutionNode
     * @covers PixelPolishers\Resolver\Config\Config::setHideSolutionNode
     */
    public function testHideSolutionNodeGetterSetterWithStringTrue()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setHideSolutionNode('true');

        // Assert
        $this->assertTrue($config->getHideSolutionNode());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getHideSolutionNode
     * @covers PixelPolishers\Resolver\Config\Config::setHideSolutionNode
     */
    public function testHideSolutionNodeGetterSetterWithStringFalse()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setHideSolutionNode('false');

        // Assert
        $this->assertFalse($config->getHideSolutionNode());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getLicense
     * @covers PixelPolishers\Resolver\Config\Config::setLicense
     */
    public function testLicenseGetterSetter()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setLicense('MIT');

        // Assert
        $this->assertEquals('MIT', $config->getLicense());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getName
     * @covers PixelPolishers\Resolver\Config\Config::setName
     */
    public function testNameGetterSetter()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setName('test');

        // Assert
        $this->assertEquals('test', $config->getName());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getProjects
     * @covers PixelPolishers\Resolver\Config\Config::setProjects
     */
    public function testProjectsGetterSetter()
    {
        // Arrange
        $config = new Config();
        $projects = [];

        // Act
        $config->setProjects($projects);

        // Assert
        $this->assertEquals($projects, $config->getProjects());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::findProject
     */
    public function testFindProjectWithValidProject()
    {
        // Arrange
        $project = new Project();
        $project->setName('project');

        $config = new Config();
        $config->setProjects([$project]);

        // Act
        $result = $config->findProject('project');

        // Assert
        $this->assertInstanceOf(Project::class, $result);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::findProject
     */
    public function testFindProjectWithInvalidProject()
    {
        // Arrange
        $config = new Config();

        // Act
        $result = $config->findProject('unknown');

        // Assert
        $this->assertNull($result);
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getRepositories
     * @covers PixelPolishers\Resolver\Config\Config::setRepositories
     */
    public function testRepositoriesGetterSetter()
    {
        // Arrange
        $config = new Config();
        $repositories = [];

        // Act
        $config->setRepositories($repositories);

        // Assert
        $this->assertEquals($repositories, $config->getRepositories());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getVendor
     * @covers PixelPolishers\Resolver\Config\Config::setVendor
     */
    public function testVendorGetterSetter()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setVendor('test');

        // Assert
        $this->assertEquals('test', $config->getVendor());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getVendorDirectory
     * @covers PixelPolishers\Resolver\Config\Config::setVendorDirectory
     */
    public function testVendorDirectoryGetterSetter()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setVendorDirectory('test');

        // Assert
        $this->assertEquals('test', $config->getVendorDirectory());
    }

    /**
     * @covers PixelPolishers\Resolver\Config\Config::getProjectsDirectory
     * @covers PixelPolishers\Resolver\Config\Config::setProjectsDirectory
     */
    public function testProjectsDirectoryGetterSetter()
    {
        // Arrange
        $config = new Config();

        // Act
        $config->setProjectsDirectory('test');

        // Assert
        $this->assertEquals('test', $config->getProjectsDirectory());
    }
}
