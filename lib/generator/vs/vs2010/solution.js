/**
 * The VS2010 solution generator.
 *
 * @class VS2010SolutionGenerator
 * @constructor
 * @param VS2010Generator generator The VS2010Generator instance.
 */
function Class(generator) {
    var fs = require('fs');
    var variables = require('../../../variables');
    var vsHelper = require('../vshelper');
    var config = generator.getConfig();
    var stream;

    function write(line, depth) {
        var i, spaces = '';
        for (i = 0; i < depth; ++i) {
            spaces += '    ';
        }
        stream.write(spaces + line + '\r\n')
    }

    function writeHeader() {
        write(String.fromCharCode(65279));
        write('Microsoft Visual Studio Solution File, Format Version 11.00');
        write('# Visual Studio 2010');
    }

    function writeProject(project) {
        var projectGuid = '8BC9CEB8-8B4A-11D0-8D11-00A0C91BC942';

        write('Project("{' + projectGuid + '}") = "'
                + project.getName() + '", "' + project.getName()
                + '.vcxproj", "{' + project.getUuid() + '}"');

        write('EndProject');
    }

    function writeProjectList() {
        var i, projects = config.getProjects();

        for (i = 0; i < projects.length; ++i) {
            writeProject(projects[i]);
        }
    }

    function writeSolutionConfigurationPlatform(configuration) {
        var platform = configuration.getPlatform() || 'win32';

        var typeStr = configuration.getName() + '|' + vsHelper.convertPlatform(platform);

        write(typeStr + ' = ' + typeStr, 2);
    }

    function writeSolutionConfiguration(project) {
        var i, configurations = project.getConfigurations();
        for (i = 0; i < configurations.length; ++i) {
            writeSolutionConfigurationPlatform(configurations[i]);
        }
    }

    function writeSolutionConfigurationPlatforms() {
        var i, projects = config.getProjects();

        write('GlobalSection(SolutionConfigurationPlatforms) = preSolution', 1);
        for (i = 0; i < projects.length; ++i) {
            writeSolutionConfiguration(projects[i]);
        }
        write('EndGlobalSection', 1);
    }

    function writeProjectConfigurationPlatform(configuration) {
        var i, projects = config.getProjects(), typeStr, projStr;

        for (i = 0; i < projects.length; ++i) {
            typeStr = configuration.getName() + '|'
                    + vsHelper.convertPlatform(configuration.getPlatform());
            projStr = '{' + projects[i].getUuid() + '}.' + typeStr;

            write(projStr + '.ActiveCfg = ' + typeStr, 2);
            write(projStr + '.Build.0 = ' + typeStr, 2);
        }
    }

    function writeProjectConfiguration(project) {
        var i, configurations = project.getConfigurations();
        for (i = 0; i < configurations.length; ++i) {
            writeProjectConfigurationPlatform(configurations[i]);
        }
    }

    function writeProjectConfigurationPlatforms() {
        var i, projects = config.getProjects();

        write('GlobalSection(ProjectConfigurationPlatforms) = postSolution', 1);
        for (i = 0; i < projects.length; ++i) {
            writeProjectConfiguration(projects[i]);
        }
        write('EndGlobalSection', 1);
    }

    function writeSolutionProperties() {
        write('GlobalSection(SolutionProperties) = preSolution', 1);
        if (config.getHideSolutionNode()) {
            write('HideSolutionNode = TRUE', 2);
        } else {
            write('HideSolutionNode = FALSE', 2);
        }
        write('EndGlobalSection', 1);
    }

    function writeGlobal() {
        write('Global');

        writeSolutionConfigurationPlatforms();
        writeProjectConfigurationPlatforms();
        writeSolutionProperties();

        write('EndGlobal');
    }

    this.generate = function() {
        var solutionName = variables.parse(config.getName()).replace(/[^a-z0-9_-]+/g, '-');
        var path = generator.getProjectDirectory() + '/' + solutionName + '.sln';

        stream = fs.createWriteStream(path, {
            'encoding': 'utf8'
        });

        stream.once('open', function() {
            writeHeader();
            writeProjectList();
            writeGlobal();

            stream.end();
        });
    };
}

module.exports.Class = Class;
