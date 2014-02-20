function Class(generator, project) {
    var root, source, fs = require('fs'), logger = require('../../../logger'),
        path = require('path'), variables = require('../../../variables'),
        vsHelper = require('../vshelper');

    var buildCondition = function(config) {
        var result = '\'$(Configuration)|$(Platform)\'==\'';
        result += config.getName();
        result += '|';
        result += vsHelper.convertPlatform(config.getPlatform());
        result += '\'';
        return result;
    };

    var writePCHConfiguration = function(parentElement, path, configuration) {
        var src, element, pch = configuration.getPrecompiledHeader();

        if (!pch) {
            pch = project.getPrecompiledHeader();
        }

        if (!pch) {
            return;
        }

        src = require('path').resolve(pch.getSource());
        if (src === path) {
            element = parentElement.ele('PrecompiledHeader', 'Create');
            element.att('Condition', buildCondition(configuration));
        }
    };

    var writePCH = function(parentElement, path) {
        var configurations = project.getConfigurations();
        for (var i = 0; i < configurations.length; ++i) {
            writePCHConfiguration(parentElement, path, configurations[i]);
        }
    };

    var writeImportProject = function(parentElement, value) {
        var element = parentElement.ele('Import');
        element.att('Project', value);
        return element;
    };

    var writeProjectConfig = function(element, configuration) {
        var platform = vsHelper.convertPlatform(configuration.getPlatform());
        var child = element.ele('ProjectConfiguration');
        child.att('Include', configuration.getName() + '|' + platform);
        child.ele('Configuration', configuration.getName());
        child.ele('Platform', platform);
    };

    var writeItemGroupProjectConfig = function() {
        var i, element, configurations;

        element = root.ele('ItemGroup').att('Label', 'ProjectConfigurations');

        configurations = project.getConfigurations() || [];
        for (i = 0; i < configurations.length; ++i) {
            writeProjectConfig(element, configurations[i]);
        }
    };

    var writeItemGroupFilemap = function(filter, incGroup, srcGroup, ignGroup, resGroup) {
        var child, i;

        if (filter.source.include.length) {
            incGroup = incGroup || root.ele('ItemGroup');
            for (i = 0; i < filter.source.include.length; ++i) {
                child = incGroup.ele('ClInclude');
                child.att('Include', filter.source.include[i]);
            }
        }

        if (filter.source.compile.length) {
            srcGroup = srcGroup || root.ele('ItemGroup');
            for (i = 0; i < filter.source.compile.length; ++i) {
                child = srcGroup.ele('ClCompile');
                child.att('Include', filter.source.compile[i]);

                writePCH(child, filter.source.compile[i]);
            }
        }

        if (filter.source.resource.length) {
            resGroup = resGroup || root.ele('ItemGroup');
            for (i = 0; i < filter.source.resource.length; ++i) {
                child = resGroup.ele('ResourceCompile');
                child.att('Include', filter.source.resource[i]);
            }
        }

        if (filter.filters.length) {
            for (i = 0; i < filter.filters.length; ++i) {
                writeItemGroupFilemap(filter.filters[i], incGroup, srcGroup, ignGroup, resGroup);
            }
        }
    };

    var writePropertyGroupGlobals = function() {
        var element = root.ele('PropertyGroup').att('Label', 'Globals');
        element.ele('ProjectGuid', project.getUuid());
        element.ele('Keyword', 'Win32Proj');
        element.ele('RootNamespace', project.getName());
    };

    var writePropertyGroupConfiguration = function(config) {
        var element = root.ele('PropertyGroup');
        element.att('Condition', buildCondition(config));
        element.att('Label', 'Configuration');
        element.ele('ConfigurationType', vsHelper.convertConfigurationType(config.getProject().getType()));
        element.ele('UseDebugLibraries', config.getDebug() ? 'true' : 'false');
        if (!config.getDebug()) {
            element.ele('WholeProgramOptimization', 'true');
        }
        element.ele('CharacterSet', vsHelper.convertCharacterSet(config.getCharacterSet()));
    };

    var writeImportGroupExtensionSettings = function() {
        var element = root.ele('ImportGroup');
        element.att('Label', 'ExtensionSettings');
    };

    var writeImportGroupExtensionTargets = function() {
        var element = root.ele('ImportGroup');
        element.att('Label', 'ExtensionTargets');
    };

    var writeImportGroupPropertySheets = function(configuration) {
        var element;

        element = root.ele('ImportGroup');
        element.att('Label', 'PropertySheets');
        element.att('Condition', buildCondition(configuration));

        element = writeImportProject(element, '$(UserRootDir)\\Microsoft.Cpp.$(Platform).user.props');
        element.att('Condition', 'exists(\'$(UserRootDir)\\Microsoft.Cpp.$(Platform).user.props\')');
        element.att('Label', 'LocalAppDataPlatform');
    };

    var writePropertyGroupUserMacros = function(parentElement) {
        var element;

        element = parentElement.ele('PropertyGroup');
        element.att('Label', 'UserMacros');
    };

    var writePathList = function(element, name, key, lists, projectDir) {
        var i, paths = [];

        for (i = 0; i < lists.length; ++i) {
            if (!lists[i]) {
                continue;
            }

            switch (key) {
                case 'exclude':
                    paths = paths.concat(lists[i].getExcludePaths());
                    break;

                case 'executable':
                    paths = paths.concat(lists[i].getExecutablePaths());
                    break;

                case 'include':
                    paths = paths.concat(lists[i].getIncludePaths());
                    break;

                case 'library':
                    paths = paths.concat(lists[i].getLibraryPaths());
                    break;

                case 'reference':
                    paths = paths.concat(lists[i].getReferencePaths());
                    break;

                case 'source':
                    paths = paths.concat(lists[i].getSourcePaths());
                    break;

                default:
                    throw key + ' is not implemented!';
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

    var writePropertyGroup = function(configuration) {
        var element, outputPath, outDir, intDir, projectDir;

        projectDir = generator.getProjectDirectory();

        element = root.ele('PropertyGroup');
        element.att('Condition', buildCondition(configuration));
        element.ele('LinkIncremental', configuration.getDebug() ? 'true' : 'false');

        writePathList(element, 'IncludePath', 'include', [project.getPaths(), configuration.getPaths()], projectDir);
        writePathList(element, 'LibraryPath', 'library', [project.getPaths(), configuration.getPaths()], projectDir);
        writePathList(element, 'ExecutablePath', 'executable', [project.getPaths(), configuration.getPaths()], projectDir);
        writePathList(element, 'SourcePath', 'source', [project.getPaths(), configuration.getPaths()], projectDir);
        writePathList(element, 'ReferencePath', 'reference', [project.getPaths(), configuration.getPaths()], projectDir);
        writePathList(element, 'ExcludePath', 'exclude', [project.getPaths(), configuration.getPaths()], projectDir);

        outputPath = variables.parse(configuration.getOutputPath());

        outDir = require('path').dirname(outputPath);
        outDir = require('path').relative(projectDir, outDir) + '\\';

        intDir = configuration.getIntermediateDirectory();
        intDir = intDir || ('intermediate\\' + configuration.getName());
        intDir = variables.parse(intDir);
        intDir = require('path').relative(projectDir, intDir) + '\\';

        element.ele('OutDir', outDir);
        element.ele('IntDir', intDir);

        element.ele('TargetName', path.basename(outputPath, path.extname(outputPath)));
        element.ele('TargetExt', path.extname(outputPath));
    };

    var writePreprocessorDefinitions = function(compileElement, configuration) {
        var tmpList = configuration.getDefinitions() || [];
        if (configuration.getDebug()) {
            tmpList.push('_DEBUG');
        } else {
            tmpList.push('NDEBUG');
        }
        tmpList.push('%(PreprocessorDefinitions)');

        // TODO: Add project definitions.

        compileElement.ele('PreprocessorDefinitions', tmpList.join(';'));
    };

    var writeAdditionalIncludeDirectories = function(compileElement, configuration) {
        var result = [], i, dependencies = configuration.getDependencies();

        // TODO: Handle dependencies

        if (result.length) {
            compileElement.ele('AdditionalIncludeDirectories', result.join(';'));
        }
    };

    var writeCompileGroup = function(element, configuration) {
        var compileElement, pch;

        compileElement = element.ele('ClCompile');
        compileElement.ele('WarningLevel', vsHelper.convertWarningLevel(configuration.getWarningLevel()));
        compileElement.ele('Optimization', configuration.getDebug() ? 'Disabled' : 'MaxSpeed');

        if (!configuration.getDebug()) {
            compileElement.ele('FunctionLevelLinking', 'true');
            compileElement.ele('IntrinsicFunctions', 'true');
        }

        writePreprocessorDefinitions(compileElement, configuration);

        if (configuration.getPrecompiledHeader()) {
            pch = configuration.getPrecompiledHeader();
            compileElement.ele('PrecompiledHeader', 'Use');
            compileElement.ele('PrecompiledHeaderFile', pch.getHeader());
        } else if (project.getPrecompiledHeader()) {
            pch = project.getPrecompiledHeader();
            compileElement.ele('PrecompiledHeader', 'Use');
            compileElement.ele('PrecompiledHeaderFile', pch.getHeader());
        } else {
            compileElement.ele('PrecompiledHeader', 'NotUsing');
        }

        writeAdditionalIncludeDirectories(compileElement, configuration);
    };

    var getOutputPathForDependencyProject = function(project, configName) {
        var i, result;

        variables.pushIdeProject(project);
        if (project.getConfigurations()) {
            for (i = 0; i < project.getConfigurations().length; ++i) {
                if (project.getConfiguration(i).getName() === configName) {
                    result = project.getConfiguration(i).getOutputPath();
                    result = variables.parse(result);
                    break;
                }
            }
        }
        variables.popIdeProject();

        return result;
    };

    var writeAdditionalDependencies = function(linkElement, configuration) {
        var i, dependencies = [], configDeps = configuration.getDependencies();
        if (configDeps) {
            for (i = 0; i < configDeps.length; ++i) {
                if (typeof(configDeps[i]) === 'string' && configDeps[i] !== 'default') {
                    dependencies.push(configDeps[i]);
                } else if (typeof(configDeps[i]) === 'object') {
                    // TODO
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

    var writeLinkGroup = function(element, configuration) {
        var linkElement = element.ele('Link');
        linkElement.ele('SubSystem', 'Console');
        linkElement.ele('GenerateDebugInformation', configuration.getDebug() ? 'true' : 'false');

        if (!configuration.getDebug()) {
            linkElement.ele('EnableCOMDATFolding', 'true');
            linkElement.ele('OptimizeReferences', 'true');
        }

        if (configuration.getWarningsAsErrors()) {
            linkElement.ele('TreatLinkerWarningAsErrors', true);
        }

        writeAdditionalDependencies(linkElement, configuration);
    };

    var writeItemDefinitionGroup = function(configuration) {
        var element = root.ele('ItemDefinitionGroup');
        element.att('Condition', buildCondition(configuration));

        writeCompileGroup(element, configuration);
        writeLinkGroup(element, configuration);
    };

    function writeProjectFile() {
        root = require('xmlbuilder').create('Project', {'version': '1.0', 'encoding': 'UTF-8'});
        root.att('DefaultTargets', 'Build');
        root.att('ToolsVersion', '4.0');
        root.att('xmlns', 'http://schemas.microsoft.com/developer/msbuild/2003');

        writeItemGroupProjectConfig();
        writePropertyGroupGlobals();
        writeImportProject(root, '$(VCTargetsPath)\\Microsoft.Cpp.Default.props');

        for (var i = 0; i < project.getConfigurations().length; ++i) {
            writePropertyGroupConfiguration(project.getConfiguration(i));
        }

        writeImportProject(root, '$(VCTargetsPath)\\Microsoft.Cpp.props');
        writeImportGroupExtensionSettings();

        for (var i = 0; i < project.getConfigurations().length; ++i) {
            writeImportGroupPropertySheets(project.getConfiguration(i));
        }

        writePropertyGroupUserMacros(root);

        for (var i = 0; i < project.getConfigurations().length; ++i) {
            writePropertyGroup(project.getConfiguration(i));
        }

        for (var i = 0; i < project.getConfigurations().length; ++i) {
            writeItemDefinitionGroup(project.getConfiguration(i));
        }

        writeItemGroupFilemap(source);

        writeImportProject(root, '$(VCTargetsPath)\\Microsoft.Cpp.targets');
        writeImportGroupExtensionTargets();

        return root.end({pretty: true}).toString("utf8").replace(/&apos;/g, "'");
    };

    this.setSource = function(value) {
        source = value;
    };

    this.generate = function() {
        var outputPath = generator.getProjectDirectory()
                + '/' + variables.parse(project.getName()) + '.vcxproj';

        fs.writeFileSync(outputPath, writeProjectFile());
    };
}

module.exports.Class = Class;
