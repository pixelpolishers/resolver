/**
 * The file system module used to the config files.
 *
 * @var Object
 */
var fs = require('fs');

/**
 * The logger.
 */
var logger = require('../../../logger');

/**
 * The path module used to work with paths.
 *
 * @var Object
 */
var path = require('path');

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

var loadConfig = function(path) {
    var result, data;
    
    try {
        data = fs.readFileSync(path, 'utf-8');
        result = JSON.parse(data);
    } catch (e) {
        logger.error('Failed to parse the config file. (' + e + ')');
    }
    
    return result;
};

var createProjectConfig = function(element, config) {
    var child = element.ele('ProjectConfiguration');
    child.att('Include', config.name + '|' + vsHelper.convertPlatform(config.platform));
    child.ele('Configuration', config.name);
    child.ele('Platform', vsHelper.convertPlatform(config.platform));
};

var buildCondition = function(config) {
    var result = '\'$(Configuration)|$(Platform)\'==\'';
    result += config.name;
    result += '|';
    result += vsHelper.convertPlatform(config.platform);
    result += '\'';
    return result;
}

var buildImportProject = function(parentElement, project) {
    var element;

    element = parentElement.ele('Import');
    element.att('Project', project);

    return element;
};

var buildItemGroupProjectConfig = function(parentElement, settings) {
    var i, element, configurations;

    element = parentElement.ele('ItemGroup').att('Label', 'ProjectConfigurations');

    configurations = settings.configurations || [];
    for (i = 0; i < configurations.length; ++i) {
        createProjectConfig(element, configurations[i]);
    }
};

var buildItemGroupFilemap = function(parentElement, filter, incGroup, srcGroup, ignGroup) {
    var child, i;
    
    if (filter.source.include.length) {
        incGroup = incGroup || parentElement.ele('ItemGroup');
        for (i = 0; i < filter.source.include.length; ++i) {
            child = incGroup.ele('ClInclude');
            child.att('Include', filter.source.include[i]);
        }
    }

    if (filter.source.compile.length) {
        srcGroup = srcGroup || parentElement.ele('ItemGroup');
        for (i = 0; i < filter.source.compile.length; ++i) {
            child = srcGroup.ele('ClCompile');
            child.att('Include', filter.source.compile[i]);
        }
    }

    if (filter.source.ignore.length) {
        ignGroup = ignGroup || parentElement.ele('ItemGroup');
        for (i = 0; i < filter.source.ignore.length; ++i) {
            child = ignGroup.ele('None');
            child.att('Include', filter.source.ignore[i]);
        }
    }

    if (filter.filters.length) {
        for (i = 0; i < filter.filters.length; ++i) {
            buildItemGroupFilemap(parentElement, filter.filters[i], incGroup, srcGroup, ignGroup);
        }
    }
};

var buildPropertyGroupGlobals = function(parentElement, project, settings) {
    var element;

    element = parentElement.ele('PropertyGroup').att('Label', 'Globals');

    element.ele('ProjectGuid', project.uuid);
    element.ele('Keyword', 'Win32Proj');
    element.ele('RootNamespace', project.name);
};

var buildPropertyGroupConfiguration = function(parentElement, config) {
    var element;

    element = parentElement.ele('PropertyGroup');
    element.att('Condition', buildCondition(config));
    element.att('Label', 'Configuration');
    element.ele('ConfigurationType', vsHelper.convertConfigurationType(config.type));
    element.ele('UseDebugLibraries', config.debug ? 'true' : 'false');
    element.ele('CharacterSet', vsHelper.convertCharacterSet(config.characterSet));
};

var buildImportGroupExtensionSettings = function(parentElement) {
    var element;

    element = parentElement.ele('ImportGroup');
    element.att('Label', 'ExtensionSettings');
};

var buildImportGroupExtensionTargets = function(parentElement) {
    var element;

    element = parentElement.ele('ImportGroup');
    element.att('Label', 'ExtensionTargets');
};

var buildImportGroupPropertySheets = function(parentElement, config) {
    var element;

    element = parentElement.ele('ImportGroup');
    element.att('Label', 'PropertySheets');
    element.att('Condition', buildCondition(config));

    element = buildImportProject(element, '$(UserRootDir)\\Microsoft.Cpp.$(Platform).user.props');
    element.att('Condition', 'exists(\'$(UserRootDir)\\Microsoft.Cpp.$(Platform).user.props\')');
    element.att('Label', 'LocalAppDataPlatform');
};

var buildPropertyGroupUserMacros = function(parentElement) {
    var element;

    element = parentElement.ele('PropertyGroup');
    element.att('Label', 'UserMacros');
};

var buildPathList = function(element, name, key, lists, projectDir) {
    var i, paths = [];

    for (i = 0; i < lists.length; ++i) {
        if (lists[i] && lists[i][key]) {
            paths = paths.concat(lists[i][key]);
        }
    }

    if (paths.length) {
        for (i = 0; i < paths.length; ++i) {
            paths[i] = require('path').relative(projectDir, paths[i]);
        }
        paths.push('$(' + name + ')');
        element.ele(name, paths.join(';'));
    }
};

var buildPropertyGroup = function(parentElement, project, config, projectDir) {
    var element, outputPath, outDir, intDir;

    element = parentElement.ele('PropertyGroup');
    element.att('Condition', buildCondition(config));
    element.ele('LinkIncremental', config.debug ? 'true' : 'false');
    
    buildPathList(element, 'IncludePath', 'include', [project.paths, config.paths], projectDir);
    buildPathList(element, 'LibraryPath', 'library', [project.paths, config.paths], projectDir);
    buildPathList(element, 'ExecutablePath', 'executable', [project.paths, config.paths], projectDir);
    buildPathList(element, 'SourcePath', 'source', [project.paths, config.paths], projectDir);
    buildPathList(element, 'ReferencePath', 'reference', [project.paths, config.paths], projectDir);
    buildPathList(element, 'ExcludePath', 'exclude', [project.paths, config.paths], projectDir);
    
    outputPath = variables.parse(config.outputPath);
    
    outDir = require('path').dirname(outputPath);
    outDir = require('path').relative(projectDir, outDir) + '\\';
    
    intDir = variables.parse(config.intermediate || 'intermediate');
    intDir = require('path').relative(projectDir, intDir) + '\\';
    
    element.ele('OutDir', outDir);
    element.ele('IntDir', intDir);
    
    element.ele('TargetName', path.basename(outputPath, path.extname(outputPath)));
    element.ele('TargetExt', path.extname(outputPath));
};

var buildPreprocessorDefinitions = function(compileElement, project, config) {
    var tmpList = config.definitions || [];
    if (config.debug) {
        tmpList.push('_DEBUG');
    } else {
        tmpList.push('NDEBUG');
    }
    tmpList.push('%(PreprocessorDefinitions)');
    
    // TODO: Add project definitions.
    
    compileElement.ele('PreprocessorDefinitions', tmpList.join(';'));
};

var buildAdditionalIncludeDirectoriesForProject = function(project, configName) {
    var result = [], i;
    
    if (project.configurations) {
        for (i = 0; i < project.configurations.length; ++i) {
            if (project.configurations[i].name === configName) {
                if (project.configurations[i].paths && project.configurations[i].paths.include) {
                    result = result.concat(project.configurations[i].paths.include);
                }
            }
        }
    }
    
    return result;
};

var buildAdditionalIncludeDirectoriesForDependency = function(settings, config, dependency) {
    var i, j, result = [], list, vendorDir, dependencyDir, depConfig;
    
    vendorDir = settings.vendorDir || 'vendor';
    dependencyDir = path.resolve(vendorDir + '/' + dependency.name);
    depConfig = loadConfig(path.resolve(dependencyDir + '/resolver.json'));
    
    if (depConfig && depConfig.projects) {
        for (i = 0; i < depConfig.projects.length; ++i) {
            list = buildAdditionalIncludeDirectoriesForProject(depConfig.projects[i], config.name);
            for (j = 0; j < list.length; ++j) {
                result.push(path.resolve(dependencyDir + '/' + list[j]));
            }
        }
    }
    
    return result;
};

var buildAdditionalIncludeDirectories = function(compileElement, settings, project, config) {
    var result = [], i;
    
    if (config.dependencies) {
        for (i = 0; i < config.dependencies.length; ++i) {
            if (typeof(config.dependencies[i]) === 'object') {
                result = result.concat(buildAdditionalIncludeDirectoriesForDependency(
                        settings, config, config.dependencies[i]));
            }
        }
    }
    
    if (result.length) {
        compileElement.ele('AdditionalIncludeDirectories', result.join(';'));
    }
};

var buildCompileGroup = function(element, settings, project, config) {
    var compileElement = element.ele('ClCompile');
    compileElement.ele('WarningLevel', vsHelper.convertWarningLevel(config.warningLevel));
    compileElement.ele('Optimization', config.debug ? 'Disabled' : 'MaxSpeed');

    if (!config.debug) {
        compileElement.ele('FunctionLevelLinking', 'true');
        compileElement.ele('IntrinsicFunctions', 'true');
    }

    buildPreprocessorDefinitions(compileElement, project, config);

    if (project.precompiledHeader) {
        compileElement.ele('PrecompiledHeader', 'Use');
        compileElement.ele('PrecompiledHeaderFile', project.precompiledHeader);
    } else {
        compileElement.ele('PrecompiledHeader', 'NotUsing');
    }
    
    buildAdditionalIncludeDirectories(compileElement, settings, project, config);
};

var getOutputPathForDependencyProject = function(project, configName) {
    var i, result;
    
    variables.pushIdeProject(project);
    if (project.configurations) {
        for (i = 0; i < project.configurations.length; ++i) {
            if (project.configurations[i].name === configName) {
                result = project.configurations[i].outputPath;
                result = variables.parse(result);
                break;
            }
        }
    }
    variables.popIdeProject();
    
    return result;
};

var getOutputPathsForDependency = function(settings, dependency, configName) {
    var i, result = [], vendorDir, dependencyDir, jsonPath, config, outputPath;
    
    vendorDir = settings.vendorDir || 'vendor';
    dependencyDir = path.resolve(vendorDir + '/' + dependency.name);
    jsonPath = path.resolve(dependencyDir + '/resolver.json');
    
    config = loadConfig(jsonPath);
    configName = dependency.config || configName;
    
    if (config && config.projects) {
        for (i = 0; i < config.projects.length; ++i) {
            outputPath = getOutputPathForDependencyProject(config.projects[i], configName);
            outputPath = path.resolve(dependencyDir + '/' + outputPath);
            result.push(outputPath);
        }
    }
    
    return result;
};

var buildAdditionalDependencies = function(linkElement, settings, config) {
    var i, dependencies = [];
    if (config.dependencies) {
        for (i = 0; i < config.dependencies.length; ++i) {
            if (typeof(config.dependencies[i]) === 'string' && config.dependencies[i] !== 'default') {
                dependencies.push(config.dependencies[i]);
            } else if (typeof(config.dependencies[i]) === 'object') {
                dependencies = dependencies.concat(getOutputPathsForDependency(settings, config.dependencies[i], config.name));
            }
        }
    }
    
    if (dependencies.length) {
        dependencies.push('%(AdditionalDependencies)');
        linkElement.ele('AdditionalDependencies', dependencies.join(';'));
    }
    
    // TODO: Implement this:
    //<AdditionalLibraryDirectories>dir;dir;dir</AdditionalLibraryDirectories>
};

var buildLinkGroup = function(element, settings, project, config) {
    var linkElement = element.ele('Link');
    linkElement.ele('SubSystem', 'Console');
    linkElement.ele('GenerateDebugInformation', config.debug ? 'true' : 'false');
    if (!config.debug) {
        linkElement.ele('EnableCOMDATFolding', 'true');
        linkElement.ele('OptimizeReferences', 'true');
    }
    
    if (config.warningsAsErrors) {
        linkElement.ele('TreatLinkerWarningAsErrors', true);
    }
    
    buildAdditionalDependencies(linkElement, settings, config);
};

var buildItemDefinitionGroup = function(parentElement, settings, project, config) {
    var element = parentElement.ele('ItemDefinitionGroup');
    element.att('Condition', buildCondition(config));
    
    buildCompileGroup(element, settings, project, config);
    buildLinkGroup(element, settings, project, config);
};

var createProjectFile = function(settings, project, projectSrc, projectDir) {
    var i, root;

    root = require('xmlbuilder').create('Project', {'version': '1.0', 'encoding': 'UTF-8'});
    root.att('DefaultTargets', 'Build');
    root.att('ToolsVersion', '4.0');
    root.att('xmlns', 'http://schemas.microsoft.com/developer/msbuild/2003');

    buildItemGroupProjectConfig(root, project);
    buildItemGroupFilemap(root, projectSrc);

    buildPropertyGroupGlobals(root, project, settings);
    buildImportProject(root, '$(VCTargetsPath)\\Microsoft.Cpp.Default.props');
    
    for (i = 0; i < project.configurations.length; ++i) {
        buildPropertyGroupConfiguration(root, project.configurations[i]);
    }
    buildImportProject(root, '$(VCTargetsPath)\\Microsoft.Cpp.props');

    buildImportGroupExtensionSettings(root);
    for (i = 0; i < project.configurations.length; ++i) {
        buildImportGroupPropertySheets(root, project.configurations[i]);
    }

    buildPropertyGroupUserMacros(root);
    for (i = 0; i < project.configurations.length; ++i) {
        buildPropertyGroup(root, project, project.configurations[i], projectDir);
    }

    for (i = 0; i < project.configurations.length; ++i) {
        buildItemDefinitionGroup(root, settings, project, project.configurations[i]);
    }

    buildImportProject(root, '$(VCTargetsPath)\\Microsoft.Cpp.targets');
    buildImportGroupExtensionTargets(root);

    return root.end({pretty: true}).toString("utf8").replace(/&apos;/g, "'");
};

/**
 * Creates the solution file.
 *
 * @param project The project to create the file for.
 * @param settings The settings used to create the project file.
 * @param projectDir The directory to write to.
 */
exports.create = function(settings, project, projectSrc, projectDir) {
    var outputPath = projectDir + '/' + variables.parse(project.name) + '.vcxproj';
    
    fs.writeFileSync(outputPath, createProjectFile(settings, project, projectSrc, projectDir));
};