var vcVarsPath;

function Class(application) {
    var logger = require('../../logger');
    var variables = require('../../variables');
    var objectFiles = [];

    var findPch = function(project, configuration) {
        var pch = configuration.getPrecompiledHeader();
        if (!pch) {
            pch = project.getPrecompiledHeader();
        }
        return pch;
    };

    var registerObjectFile = function(configuration, filePath) {
        var fileExt, fileName, objFile;

        fileExt = require('path').extname(filePath);
        fileName = require('path').basename(filePath, fileExt);

        objFile = configuration.getIntermediateDirectory() + '/' + fileName + '.obj';
        objectFiles.push(objFile);
    };

    var buildLinkManifest = function(configuration) {
        var result = [], outputPath;

        outputPath = variables.parse(configuration.getOutputPath());

        result.push('/MANIFEST');
        result.push('"/ManifestFile:' + outputPath + '.intermediate.manifest"');
        result.push('"/MANIFESTUAC:level=\'asInvoker\' uiAccess=\'false\'"');

        return result;
    };

    var buildLinkPlatform = function(configuration) {
        var result = [];

        switch (configuration.getPlatform()) {
            case 'win64':
                result.push('/MACHINE:X64');
                break;

            case 'win32':
                result.push('/MACHINE:X86');
                break;

            default:
                throw 'The platform "' + configuration.getPlatform() + '" is not supported.';
        }

        return result;
    };

    var buildLinkSubsystem = function(project) {
        var result = [];

        switch (project.getSubsystem()) {
            case 'boot':
                result.push('/SUBSYSTEM:BOOT_APPLICATION');
                break;

            case 'console':
                result.push('/SUBSYSTEM:CONSOLE');
                break;

            case 'native':
                result.push('/SUBSYSTEM:NATIVE');
                break;

            case 'posix':
                result.push('/SUBSYSTEM:POSIX');
                break;

            case 'windows':
                result.push('/SUBSYSTEM:WINDOWS');
                break;

            default:
                throw 'The subsystem "' + project.getSubsystem() + '" is not supported.';
        }

        return result;
    };

    var buildLinkApplicationAction = function(project, configuration) {
        var result = ['link'], outputPath, outputDir, intDir;

        outputPath = variables.parse(configuration.getOutputPath());
        result.push('"/OUT:' + outputPath + '"');

        outputDir = require('path').dirname(configuration.getOutputPath());
        require('mkdirp').mkdirp(outputDir, 0777);

        intDir = variables.parse(configuration.getIntermediateDirectory());

        if (configuration.getDebug()) {
            result.push('/INCREMENTAL');
        } else {
            result.push('/INCREMENTAL:NO');
        }

        result.push('kernel32.lib');
        result.push('user32.lib');
        result.push('gdi32.lib');
        result.push('winspool.lib');
        result.push('comdlg32.lib');
        result.push('advapi32.lib');
        result.push('shell32.lib');
        result.push('ole32.lib');
        result.push('oleaut32.lib');
        result.push('uuid.lib');
        result.push('odbc32.lib');
        result.push('odbccp32.lib');

        result = result.concat(buildLinkManifest(configuration));
        result.push('/DEBUG');
        result.push('"/PDB:' + intDir + '/' + project.getName() + '.pdb"');
        result.push(buildLinkSubsystem(project));

        if (!configuration.getDebug()) {
            result.push('/OPT:REF');
            result.push('/OPT:ICF');
            result.push('/LTCG');
        }

        result.push('/TLBID:1');
        result.push('/DYNAMICBASE');
        result.push('/NXCOMPAT');
        result.push('"/IMPLIB:' + intDir + '/' + project.getName() + '.lib"');
        result.push(buildLinkPlatform(configuration));

        for (var i = 0; i < objectFiles.length; ++i) {
            result.push(objectFiles[i]);
        }

        return result.join(' ');
    };

    var buildLinkDynamicAction = function(project, configuration) {
        var result = [];

        return result;
    };

    var buildLinkStaticAction = function(project, configuration) {
        var result = [];

        return result;
    };

    var buildLinkAction = function(project, configuration) {
        var result = [];

        switch (project.getType()) {
            case 'application':
                result = result.concat(buildLinkApplicationAction(project, configuration));
                break;

            case 'dynamic-library':
                result = result.concat(buildLinkDynamicAction(project, configuration));
                break;

            case 'static-library':
                result = result.concat(buildLinkStaticAction(project, configuration));
                break;

            default:
                throw 'The project type "' + project.getType() + '" is not implemented yet.';
        }

        return result;
    };

    var buildWarningsArguments = function(configuration) {
        var result = [];

        if (configuration.getWarningsAsErrors()) {
            result.push('/WX-');
        }

        configuration.setWarningLevel(configuration.getWarningLevel() || 'level3');

        switch (configuration.getWarningLevel()) {
            case 'all':
                result.push('/Wall');
                break;
            case 'none':
                result.push('/W0');
                break;
            case 'level1':
                result.push('/W1');
                break;
            case 'level2':
                result.push('/W2');
                break;
            case 'level3':
                result.push('/W3');
                break;
            case 'level4':
                result.push('/W4');
                break;
        }

        return result.join(' ');
    };

    var buildDefineList = function(configuration) {
        var i, result = [], definitions = configuration.getDefinitions();

        if (definitions.indexOf('WIN32') === -1) {
            definitions.push('WIN32');
        }

        if (definitions.indexOf('_WINDOWS') === -1) {
            definitions.push('_WINDOWS');
        }

        if (definitions.indexOf('UNICODE') === -1) {
            definitions.push('UNICODE');
        }

        if (definitions.indexOf('_UNICODE') === -1) {
            definitions.push('_UNICODE');
        }

        if (definitions.indexOf('_MBCS') === -1) {
            definitions.push('_MBCS');
        }

        if (configuration.getDebug() && definitions.indexOf('_DEBUG') === -1) {
            definitions.push('_DEBUG');
        } else if (!configuration.getDebug() && definitions.indexOf('NDEBUG') === -1) {
            definitions.push('NDEBUG');
        }

        for (i = 0; i < definitions.length; ++i) {
            result.push('/D "' + definitions[i] + '"');
        }

        return result;
    };

    var buildIncludeList = function(configuration) {
        var i, result = [], paths = configuration.getPaths().getIncludePaths();

        for (i = 0; i < paths.length; ++i) {
            result.push('/I"' + paths[i] + '"');
        }

        return result;
    };

    var buildIntermediateArgument = function(configuration) {
        var intermediateDir = variables.parse(configuration.getIntermediateDirectory());

        return ['/Fo"' + intermediateDir + '/"'];
    };

    var buildPDBArgument = function(project, configuration) {
        var intermediateDir = variables.parse(configuration.getIntermediateDirectory());

        return ['/Fd"' + intermediateDir + '/' + project.getName() + '.pdb"'];
    };

    var buildErrorReportingArgument = function(configuration) {
        if (configuration.getMsvcErrorReport()) {
            switch (configuration.getMsvcErrorReport())
            {
            case 'none':
                return '/ERRORREPORT:NONE';

            case 'prompt':
                return '/ERRORREPORT:PROMPT';

            case 'queue':
                return '/ERRORREPORT:QUEUE';

            case 'send':
                return '/ERRORREPORT:SEND';
            }

            throw 'Invalid msvcErrorReport provided.';
        }
        return '';
    };

    var buildPchArguments = function(project, configuration, use) {
        var result = [], pchName, pch, intDir = configuration.getIntermediateDirectory();

        pch = findPch(project, configuration)
        pchName = pch.getName() || (project.getName() + '.pch');

        result.push('/Fp"' + variables.parse(intDir + '/' + pchName) + '"');

        if (pch.getMemory()) {
            result.push('/Zm' + variables.parse(pch.getMemory()));
        }

        if (use) {
            result.push('/Yu"' + variables.parse(pch.getHeader()) + '"');
        } else {
            result.push('/Yc"' + variables.parse(pch.getHeader()) + '"');
        }

        return result;
    };

    var buildMSCallAction = function(platform) {
        switch (platform) {
            case 'win32':
                return 'call "' + vcVarsPath + '" x86';

            case 'win64':
                return 'call "' + vcVarsPath + '" x64';

            default:
                throw 'The platform "' + platform + '" is not implemented.';
        }
    };

    var buildStdArguments = function(project, configuration) {
        var result = [];

        if (configuration.getDebug()) {
            result.push('/ZI');
        } else {
            result.push('/Zi');
        }

        result.push(buildWarningsArguments(configuration));

        if (configuration.getDebug()) {
            result.push('/Od');
        } else {
            result.push('/O2');
            result.push('/Oi');
            result.push('/GL');
        }
        result.push('/Oy-');

        result = result.concat(buildDefineList(configuration));
        result = result.concat(buildIncludeList(configuration));

        if (configuration.getDebug()) {
            result.push('/Gm');
        } else {
            result.push('/Gm-');
        }
        result.push('/EHsc');

        if (configuration.getDebug()) {
            result.push('/RTC1');
            result.push('/MDd');
        } else {
            result.push('/MD');
        }

        result.push('/GS');
        if (!configuration.getDebug()) {
            result.push('/Gy');
        }

        result.push('/fp:precise');
        result.push('/Zc:wchar_t');
        result.push('/Zc:forScope');

        result = result.concat(buildIntermediateArgument(configuration));
        result = result.concat(buildPDBArgument(project, configuration));

        result.push('/Gd');
        result.push('/TP');
        result.push('/analyze-');
        result.push(buildErrorReportingArgument(configuration));

        return result;
    };

    var buildCompileIdeSourceFileAction = function(project, configuration, path) {
        var result = ['cl', '/c'], pch = findPch(project, configuration);

        if (pch && require('path').resolve(pch.getSource()) === path) {
            return '';
        }

        result = result.concat(buildStdArguments(project, configuration));
        result = result.concat(buildPchArguments(project, configuration, true));
        result.push('"' + path + '"');

        registerObjectFile(configuration, path);

        return result.join(' ');
    };

    var buildCompileIdeSourceAction = function(project, configuration, source) {
        var result = [], fileList = [], list, stats, cmd;

        var registerCompilableFile = function(filePath) {
            filePath = require('path').resolve(filePath);

            switch (require('path').extname(filePath)) {
                case '.cpp':
                case '.c':
                case '.cxx':
                    fileList.push(filePath);
                    return true;
            }

            return false;
        };

        list = source.getPaths();
        for (var i = 0; i < list.length; ++i) {
            var files = require('./../../utils').getFilesFromDirectory(list[i]);
            for (var j = 0; j < files.length; ++j) {
                registerCompilableFile(files[j]);
            }
        }

        list = source.getFiles();
        for (var i = 0; i < list.length; ++i) {
            registerCompilableFile(list[i]);
        }

        for (var i = 0; i < fileList.length; ++i) {
            var filePath = fileList[i];
            try {
                stats = require('fs').statSync(filePath);
                if (stats.isFile()) {
                    cmd = buildCompileIdeSourceFileAction(project, configuration, filePath);
                    if (cmd) {
                        result.push(cmd);
                    }
                }
            } catch (e) {
            }
        }

        list = source.getSources();
        for (var i = 0; i < list.length; ++i) {
            result = result.concat(buildCompileIdeSourceAction(project, configuration, list[i]));
        }

        return result;
    };

    var buildIdeProjectPlatformAction = function(project, configuration) {
        var cmd = [], result = [], pch = findPch(project, configuration), intDir;

        // Reset the object files:
        objectFiles = [];

        // The intermediate directory:
        intDir = configuration.getIntermediateDirectory();

        result.push(buildMSCallAction(configuration.getPlatform()));

        if (pch) {
            cmd = ['cl', '/c'];
            cmd = cmd.concat(buildStdArguments(project, configuration));
            cmd = cmd.concat(buildPchArguments(project, configuration, false));
            cmd.push(pch.getSource());

            registerObjectFile(configuration, pch.getSource());
            result.push(cmd.join(' '));
        }

        result = result.concat(buildCompileIdeSourceAction(project, configuration, project.getSource()));
        result = result.concat(buildLinkAction(project, configuration));

        return result;
    };

    var buildIdeProjectConfigurationAction = function(project, configuration) {
        // If this configuration has no intermediate directory, let's set it:
        if (!configuration.getIntermediateDirectory()) {
            configuration.setIntermediateDirectory('intermediate/' + configuration.getName());
        }

        var intDir = variables.parse(configuration.getIntermediateDirectory());
        require('mkdirp').mkdirp(intDir, 0777);

        if (!configuration.getPlatform()) {
            configuration.setPlatform('win32');
        }

        return buildIdeProjectPlatformAction(project, configuration);
    };

    var buildIdeProjectAction = function(project) {
        var result = [], configurations = project.getConfigurations() || [];

        for (var i = 0; i < configurations.length; ++i) {
            variables.pushIdeConfiguration(configurations[i]);

            result = result.concat(buildIdeProjectConfigurationAction(project, configurations[i]));
            result.push('echo.');
            result.push('');

            variables.popIdeConfiguration();
        }

        return result;
    };

    this.compileAll = function(callback) {
        throw 'Cannot compile the project and dependencies yet.';
    };

    this.compileProjects = function(callback) {
        var content = [], projects = application.getConfig().getProjects();

        content.push('@echo off');
        content.push('');
        for (var i = 0; i < projects.length; ++i) {
            variables.pushIdeProject(projects[i]);
            content = content.concat(buildIdeProjectAction(projects[i]));
            variables.popIdeProject();
        }

        require('../../utils').shellExecuteArray({
            'content': content,
            'keepFile': false,
            'callback': function(error, stdout, stderr) {
                var lines = stdout.split('\r\n');
                for (var i = 0; i < lines.length; ++i ){
                    logger.log(lines[i]);
                }

                var lines = stderr.split('\r\n');
                for (var i = 0; i < lines.length; ++i ){
                    //logger.log(lines[i]);
                }

                callback();
            }
        });
    };

    this.compileDependencies = function(callback) {
        callback();
    };
}

module.exports.Class = Class;

module.exports.checkAvailability = function() {
    var paths = [
        'C:\\Program Files\\Microsoft Visual Studio 10.0\\VC\\vcvarsall.bat',
        'C:\\Program Files (x86)\\Microsoft Visual Studio 10.0\\VC\\vcvarsall.bat'
    ];

    for (var i = 0; i < paths.length; ++i) {
        if (require('fs').existsSync(paths[i])) {
            vcVarsPath = paths[i];
            return true;
        }
    }
    return false;
};
