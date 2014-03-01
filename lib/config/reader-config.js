function loadJson(path) {
    var data = require('fs').readFileSync(path, 'utf-8');

    return JSON.parse(data);
}

function loadIdePCH(result, json) {
    if (!json.header) {
        throw 'No header set for the Precompiled Header settings.';
    }

    if (!json.source) {
        throw 'No source set for the Precompiled Header settings.';
    }

    var Class = require('./ide-pch').Class;
    var pch = new Class;

    pch.setHeader(json.header);
    pch.setSource(json.source);

    if (json.memory) {
        pch.setMemory(json.memory);
    }

    result.setPrecompiledHeader(pch);
}

function loadIdeSource(json) {
    var Class = require('./ide-source').Class;
    var result = new Class;

    if (!json.files && !json.paths) {
        throw 'Your IDE project source has no paths or files.';
    }

    if (json.name) {
        result.setName(json.name);
    }

    if (json.extensions) {
        for (var i = 0; i < json.extensions.length; ++i) {
            result.addExtension(json.extensions[i]);
        }
    }

    if (json.files) {
        for (var i = 0; i < json.files.length; ++i) {
            result.addFile(json.files[i]);
        }
    }

    if (json.paths) {
        for (var i = 0; i < json.paths.length; ++i) {
            result.addPath(json.paths[i]);
        }
    }

    return result;
}

function loadIdePaths(result, json) {
    var Class = require('./ide-paths').Class;
    var paths = new Class;

    for (var i = 0; i < json.include.length; ++i) {
        paths.addIncludePath(json.include[i]);
    }

    result.setPaths(paths);
}

function loadIdeConfig(project, json) {
    var Class = require('./ide-config').Class;
    var result = new Class(project);

    if (!json.name) {
        throw 'Your IDE project configuration has no name.';
    }

    if (!json.outputPath) {
        throw 'Your IDE project configuration has output path.';
    }

    if (json.platform) {
        result.setPlatform(json.platform);
    }

    result.setName(json.name);
    result.setOutputPath(json.outputPath);
    result.setDebug(!!json.debug);
    result.setCharacterSet(json.characterSet || 'unicode');
    result.setIntermediateDirectory(json.intermediateDirectory);

    if (json.paths) {
        loadIdePaths(result, json.paths);
    }

    if (json.pch) {
        loadIdePCH(result, json.pch);
    }

    return result;
}

function loadRepository(json) {
    if (!json.type) {
        throw "No repository type set.";
    }

    var Class = require('./project-repository').Class;
    var result = new Class;
    result.setType(json.type);

    switch (json.type) {
        case 'repository':
            result.setParam('host', json.host);
            result.setParam('path', json.path);
            break;

        default:
            throw 'Invalid repository type "' + json.type + '" provided.';
    }

    return result;
}

function loadIdeProject(json) {
    var Class = require('./ide-project').Class;
    var result = new Class;

    if (typeof(json) === 'string') {
        json = loadJson(json + '.json');
    }

    if (!json.name) {
        throw 'Your IDE project has no name.';
    }

    if (!json.configurations) {
        throw 'The IDE project ' + json.name + ' has no "configurations" configured.';
    }

    if (!json.source) {
        throw 'The IDE project ' + json.name + ' has no "source" configured.';
    }

    result.setName(json.name);
    result.setType(json.type || 'application');
    result.setSubsystem(json.subsystem || 'windows');

    for (var i = 0; i < json.configurations.length; ++i) {
        var ideConfig = loadIdeConfig(result, json.configurations[i]);

        result.addConfiguration(ideConfig);
    }

    if (json.paths) {
        loadIdePaths(result, json.paths);
    }

    var source = loadIdeSource(json.source);
    result.setSource(source);

    if (json.pch) {
        loadIdePCH(result, json.pch);
    }

    if (json.dependencies) {
        var DependencyClass = require('./project-dependency').Class;
        for (var name in json.dependencies) {
            var dependency = new DependencyClass;

            dependency.setName(name);
            dependency.setVersion(json.dependencies[name]);

            result.addDependency(dependency);
        }
    }

    return result;
}

function loadConfig(json) {
    var ConfigClass = require('./project-config').Class;
    var result = new ConfigClass;

    if (!json.name) {
        throw 'Your project configuration has no name.';
    }

    result.setName(json.name);
    result.setVendorDirectory(json.vendor || 'vendor');
    result.setHideSolutionNode(!!json.hideSolutionNode);

    if (json.repositories) {
        for (var i = 0; i < json.repositories.length; ++i) {
            var repository = loadRepository(json.repositories[i]);

            result.addRepository(repository);
        }
    }

    if (json.projects) {
        for (var i = 0; i < json.projects.length; ++i) {
            var ideProject = loadIdeProject(json.projects[i]);

            result.addProject(ideProject);
        }
    }

    return result;
}

exports.load = function() {
    var json;

    try {
        json = loadJson('resolver.json');
    } catch (e) {
        throw 'Failed to parse the config file. (' + e + ')';
    }

    return loadConfig(json);
};
