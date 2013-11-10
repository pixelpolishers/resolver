/**
 * The child process module that is used to start child processes.
 *
 * @var Object
 */
var child_process = require('child_process');

var fs = require('fs');
var path = require('path');
var logger = require('../../logger');
var variables = require('../../variables');

var paths = [
    'c:\\Program Files\\Microsoft Visual Studio 10.0\\VC\\vcvarsall.bat',
    'c:\\Program Files (x86)\\Microsoft Visual Studio 10.0\\VC\\vcvarsall.bat'
];

var vcVarsPath;
var objectFiles = [];

var buildDefineList = function(config) {
    var i, result = [];
    
    config['defines'] = config['defines'] || [];
    
    if (config['defines'].indexOf('WIN32') == -1) {
        config['defines'].push('WIN32');
    }
    
    if (config['defines'].indexOf('_WINDOWS') == -1) {
        config['defines'].push('_WINDOWS');
    }
    
    if (config['defines'].indexOf('UNICODE') == -1) {
        config['defines'].push('UNICODE');
    }
    
    if (config['defines'].indexOf('_UNICODE') == -1) {
        config['defines'].push('_UNICODE');
    }
    
    if (config['defines'].indexOf('_MBCS') == -1) {
        config['defines'].push('_MBCS');
    }
    
    if (config.debug) {
        if (config['defines'].indexOf('_DEBUG') == -1) {
            config['defines'].push('_DEBUG');
        }
    } else {
        if (config['defines'].indexOf('NDEBUG') == -1) {
            config['defines'].push('NDEBUG');
        }
    }

    if (config['defines']) {
        for (i = 0; i < config['defines'].length; ++i) {
            result.push('/D "' + config['defines'][i] + '"');
        }
    }

    return result;
};

var buildIncludeList = function(config) {
    var i, result = [];

    if (config['include-directories']) {
        for (i = 0; i < config['include-directories'].length; ++i) {
            result.push('/I"' + config['include-directories'][i] + '"');
        }
    }

    return result;
};

var buildIntermediateArgument = function(config) {
    var intermediateDir = variables.parse(config.intermediate);

    return ['/Fo"' + intermediateDir + '/"'];
};

var buildPDBArgument = function(config) {
    var intermediateDir = variables.parse(config.intermediate);

    return ['/Fd"' + intermediateDir + '/' + config.name + '.pdb"'];
};

var buildErrorReportingArgument = function(config) {
    if (config.msvcErrorReport) {
        switch (config.msvcErrorReport)
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

var buildWarningsArguments = function(config) {
    var result = [];
    
    if (config.warningsAsErrors) {
        result.push('/WX-');
    }
    
    config.warningLevel = config.warningLevel || 'level3';
    
    switch (config.warningLevel) {
        case 'none':
            result.push('/w');
            break;
        case 'all':
            result.push('/Wall');
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

var buildStdArguments = function(options, project, config) {
    var result = [];

    result.push('/nologo');
    result = result.concat(buildDefineList(config));
    result = result.concat(buildIncludeList(config));
    result = result.concat(buildIntermediateArgument(config));
    result = result.concat(buildPDBArgument(config));
    
    // Set the default value for inline expansions:
    if (!config.inlineExpansion)
    {
        config.inlineExpansion = 2;
    }

    if (config.debug) {
        config.inlineExpansion = 0;
        result.push("/Zi"); // Generates debugging information
        result.push("/Od"); // Disable optimizations
        result.push("/MDd"); // Creates a debug multithreaded DLL using MSVCRTD.lib
        result.push("/RTC1"); // Enables run-time error checking.
    } else {
        result.push('/O2 /MD');
    }

    result.push('/Ob' + config.inlineExpansion)
    result.push(buildWarningsArguments(config));
    result.push('/Oy-')
    result.push('/Gm-')
    result.push('/EHsc')
    result.push('/GS')
    result.push('/fp:precise')
    result.push('/Zc:wchar_t')
    result.push('/Zc:forScope')
    result.push('/GR')
    result.push('/Gd')
    result.push('/TP')
    result.push('/analyze-')
    result.push(buildErrorReportingArgument(config))

    return result;
};

var buildPchArguments = function(options, project, config, use) {
    var result = [], pchName;

    pchName = config.pch.name || project.name + '.pch';

    result.push('/Fp"' + variables.parse(config.intermediate + '/' + pchName) + '"');

    if (config.pch.memory) {
        result.push('/Zm' + variables.parse(config.pch.memory));
    }

    if (use) {
        result.push('/Yu"' + variables.parse(config.pch.header) + '"');
    } else {
        result.push('/Yc"' + variables.parse(config.pch.header) + '"');
    }

    return result;
};

var buildCompileIdeSourceFileAction = function(options, project, configuration, path) {
    var result = ['cl', '/c'];
    
    if (configuration.pch && configuration.pch.source && require('path').resolve(configuration.pch.source) == path) {
        return '';
    }
    
    result = result.concat(buildStdArguments(options, project, configuration));
    result = result.concat(buildPchArguments(options, project, configuration, true));
    result.push('"' + path + '"');
    
    registerObjectFile(configuration, path);
    return result.join(' ');
};

var isCompileExt = function(filePath) {
    switch (path.extname(filePath)) {
        case '.cpp':
        case '.c':
        case '.cxx':
            return true;
    }
    
    return false;
};

var buildCompileIdeSourceAction = function(options, project, configuration, source) {
    var result = [], i, j, stats, filePath, fileNames;
    
    if (source.paths) {
        for (i = 0; i < source.paths.length; ++i) {
            fileNames = fs.readdirSync(source.paths[i]);
            for (j = 0; j < fileNames.length; ++j) {
                filePath = path.resolve(source.paths[i] + '/' + fileNames[j]);
                stats = fs.statSync(filePath);
                if (stats.isFile() && isCompileExt(filePath)) {
                    result.push(buildCompileIdeSourceFileAction(options, project, configuration, filePath));
                }
            }
        }
    }
    
    if (source.files) {
        for (i = 0; i < source.files.length; ++i) {
            result.push(buildCompileIdeSourceFileAction(options, project, configuration, source.files[i]));
        }
    }
    
    if (source.sources) {
        for (i = 0; i < source.sources.length; ++i) {
            result = result.concat(buildCompileIdeSourceAction(options, project, configuration, source.sources[i]));
        }
    }
    
    return result;
};

var buildLinkStaticAction = function(options, project, configuration, platform) {
    var result = ['lib'], outputDir;
    
    if (!configuration.outputPath) {
        configuration.outputPath = 'libs/' + configuration.name + '/' + project.name + '.lib';
    } else {
        configuration.outputPath = variables.parse(configuration.outputPath);
    }
    
    outputDir = path.dirname(configuration.outputPath);
    if (!fs.existsSync(outputDir)) {
        fs.mkdirSync(outputDir, 0777);
    }
    
    result.push('/NOLOGO');
    result.push('/OUT:"' + configuration.outputPath + '"');
    result = result.concat(buildErrorReportingArgument(configuration));
    
    for (i = 0; i < objectFiles.length; ++i) {
        result.push(objectFiles[i]);
    }
    
    return result.join(' ');
};

var buildLinkAction = function(options, project, configuration, platform) {
    var result = [];
    
    switch (project.type) {
        case 'application':
            break;
        case 'dynamic-library':
            break;
        case 'static-library':
            result = result.concat(buildLinkStaticAction(options, project, configuration, platform));
            break;
    }
    
    return result;
};

var registerObjectFile = function(configuration, filePath) {
    var fileExt, fileName, objFile;
        
    fileExt = path.extname(filePath);
    fileName = path.basename(filePath, fileExt);
    
    objectFiles.push(configuration.intermediate + '/' + fileName + '.obj');
};
    
var buildIdeProjectPlatformAction = function(options, project, configuration, platform) {
    var cmd = [], result = [];
    
    objectFiles = [];
    
    result.push('');
    result.push('call "' + vcVarsPath + '" ' + platform);
    
    if (configuration.pch && configuration.pch.header && configuration.pch.source) {
        cmd = ['cl', '/c'];
        cmd = cmd.concat(buildStdArguments(options, project, configuration));
        cmd = cmd.concat(buildPchArguments(options, project, configuration, false));
        cmd.push(configuration.pch.source);
        
        registerObjectFile(configuration, configuration.pch.source);
        result.push(cmd.join(' '));
    }
    
    result = result.concat(buildCompileIdeSourceAction(options, project, configuration, project.source));
    result = result.concat(buildLinkAction(options, project, configuration, platform));
    
    return result.join('\r\n');
};

var buildIdeProjectConfigAction = function(options, project, configuration) {
    var result = [], platforms = project.platforms || ['x86'];

    for (var i = 0; i < platforms.length; ++i) {
        result = result.concat(buildIdeProjectPlatformAction(
            options, 
            project, 
            configuration, 
            platforms[i]));
    }
    
    return result;
};

var buildIdeProjectAction = function(options, project) {
    var result = [], configurations = project.configurations || [];

    for (var i = 0; i < configurations.length; ++i) {
        variables.pushIdeConfiguration(configurations[i]);
        
        result = result.concat(buildIdeProjectConfigAction(options, project, configurations[i]));
        
        variables.popIdeConfiguration();
    }
    
    return result;
};

var buildCompileProjectAction = function(options) {
    var result = [];
    
    if (!options.config.projects) {
        throw 'The project "' + options.config.name + '" has no IDE projects configured.';
    }
    
    for (var i = 0; i < options.config.projects.length; ++i) {
        variables.pushIdeProject(options.config.projects[i]);
        
        result = result.concat(buildIdeProjectAction(options, options.config.projects[i]));
        
        variables.popIdeProject();
    }
    
    return result;
};

var execute = function(options, content) {
    var contentFile = options.config.executeFile || 'resolver';
    
    if (!!process.platform.match(/^win/)) {
        contentFile += '.bat';
    } else {
        contentFile += '.sh';
    }
    
    contentFile = path.resolve(options.cwd + '/' + contentFile);
    
    fs.writeFileSync(contentFile, content.join('\r\n'), {
        encoding: 'utf8',
        mode: 0777
    });

    require('child_process').execFile(contentFile, [], {}, function(error, stdout, stderr) {
        var lines = stdout.split('\r\n');
        for (var i = 0; i < lines.length; ++i ){
            logger.log(lines[i]);
        }
        
        //fs.unlinkSync(contentFile);
        options.success();
    });
};

var initialize = function(options) {
    var i, intermediateDir;
    
    process.chdir(options.cwd);
    
    for (i = 0; i < options.config.projects.length; ++i) {
        project = options.config.projects[i];
        for (j = 0; j < project.configurations.length; ++j) {
            project.configurations[j].intermediate = project.configurations[j].intermediate || 
                    'intermediate/' + project.configurations[j].name;
            
            intermediateDir = path.resolve(options.cwd + '/' + project.configurations[j].intermediate);
            if (!fs.existsSync(intermediateDir)) {
                fs.mkdirSync(intermediateDir, 0777);
            }
        }
    }
    
    return options;
};

exports.compileProject = function(options) {
    var content = [];
    
    options = initialize(options);
    
    content.push('@echo off');
    variables.pushVariableSet(options.config.variables || {});
    
    try {
        content = content.concat(buildCompileProjectAction(options));
    } catch (e) {
        throw e;
    } finally {
        variables.popVariableSet();
    }
    
    execute(options, content)
};

exports.available = function() {
    for (var i = 0; i < paths.length; ++i) {
        if (fs.existsSync(paths[i])) {
            vcVarsPath = paths[i];
            return true;
        }
    }
    return false;
};
