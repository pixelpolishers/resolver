/**
 * The file system module used to create files.
 *
 * @var Object
 */
var fs = require('fs');

/**
 * The variable parser.
 *
 * @var Object
 */
var variables = require('../../../variables');

/**
 * The visual studio helper.
 *
 * @var Object
 */
var vsHelper = require('../vshelper');

var write = function(stream, line, depth) {
    var i, spaces = '';
    for (i = 0; i < depth; ++i) {
        spaces += '    ';
    }
    stream.write(spaces + line + '\r\n')
};

var writeHeader = function(stream) {
    write(stream, String.fromCharCode(65279));
    write(stream, 'Microsoft Visual Studio Solution File, Format Version 11.00');
    write(stream, '# Visual Studio 2010');
};

var writeProjectList = function(stream, projects) {
    var i, project;
    
    for (i = 0; i < projects.length; ++i) {
        project = projects[i];
        
        if (project.source) {
            write(stream, 'Project("{8BC9CEB8-8B4A-11D0-8D11-00A0C91BC942}") = "' + project.name + '", "' + project.name + '.vcxproj", "{' + project.uuid + '}"');
            write(stream, 'EndProject');
        }
    }
};

var writeSolutionConfigurationPlatforms = function(stream, settings) {
    var i, j, config, typeStr;
    
    write(stream, 'GlobalSection(SolutionConfigurationPlatforms) = preSolution', 1);
    for (i = 0; i < settings.projects.length; ++i) {
        if (settings.projects[i].source && settings.projects[i].configurations) {
            for (j = 0; j < settings.projects[i].configurations.length; ++j) {
                config = settings.projects[i].configurations[j];
                config.platform = config.platform || 'win32';

                typeStr = config.name + '|' + vsHelper.convertPlatform(config.platform);
                write(stream, typeStr + ' = ' + typeStr, 2);
            }
        }
    }
    write(stream, 'EndGlobalSection', 1);
};

var writeProjectConfigurationPlatforms = function(stream, settings) {
    var i, j, k, project, config, typeStr, projStr;
    write(stream, 'GlobalSection(ProjectConfigurationPlatforms) = postSolution', 1);
    for (i = 0; i < settings.projects.length; ++i) {
        if (settings.projects[i].source && settings.projects[i].configurations) {
            for (j = 0; j < settings.projects[i].configurations.length; ++j) {
                config = settings.projects[i].configurations[j];
        
                for (k = 0; k < settings.projects.length; ++k) {
                    project = settings.projects[k];
                    if (project.source) {
                        typeStr = config.name + '|' + vsHelper.convertPlatform(config.platform);
                        projStr = '{' + project.uuid + '}.' + typeStr;

                        write(stream, projStr + '.ActiveCfg = ' + typeStr, 2);
                        write(stream, projStr + '.Build.0 = ' + typeStr, 2);
                    }
                }
            }
        }
    }
    write(stream, 'EndGlobalSection', 1);
};

var writeSolutionProperties = function(stream, settings) {
    write(stream, 'GlobalSection(SolutionProperties) = preSolution', 1);
    write(stream, 'HideSolutionNode = ' + (settings.hideSolutionNode ? 'TRUE' : 'FALSE'), 2);
    write(stream, 'EndGlobalSection', 1);
};

var writeGlobal = function(stream, settings) {
    write(stream, 'Global');
    writeSolutionConfigurationPlatforms(stream, settings);
    writeProjectConfigurationPlatforms(stream, settings);
    writeSolutionProperties(stream, settings);
    write(stream, 'EndGlobal');
};

var writeSolutionFile = function(stream, settings) {
    writeHeader(stream);
    writeProjectList(stream, settings.projects);
    writeGlobal(stream, settings);
};

/**
 * Creates the solution file.
 *
 * @param settings The settings used to create the solution file.
 * @param projectDir The directory to write to.
 */
exports.create = function(settings, projectDir) {
    var stream, solutionName;
    
    solutionName = settings.name.replace(/[^a-z0-9_-]+/g, '-');
    
    stream = fs.createWriteStream(projectDir + '/' + solutionName + '.sln', {
        'encoding': 'utf8'
    });

    stream.once('open', function() {
        writeSolutionFile(stream, settings);
        stream.end();
    });
};
