var fs = require('fs');
var path = require('path');
var logger = require('../logger');
var variables = require('../variables');

exports.findCompiler = function() {
    var i, compilers = [], isWin = !!process.platform.match(/^win/);
    
    if (isWin) {
        compilers.push(require('./msvc/msvc2010'));
    }
    
    for (i = 0; compilers.length; ++i) {
        if (compilers[i].available()) {
            return compilers[i];
        }
    }
};

exports.compileDependencies = function(compiler, config, dependencies, callback) {
    var name, data, dependencyConfig, dependencyDir, dependencyJson, vendorDir, compiledProjects = 0,
        compiledCallback = function() {
            compiledProjects++;
            if (compiledProjects === require('../utils').countObjectMembers(dependencies)) {
                callback();
            }
        };
    
    vendorDir = config.vendorDir || 'vendor';
    for (name in dependencies) {
        
        dependencyDir = path.resolve(vendorDir + '/' + name);
        dependencyJson = path.resolve(dependencyDir + '/resolver.json');
        if (!fs.existsSync(dependencyJson)) {
            continue;
        }
        
        try {
            data = fs.readFileSync(dependencyJson, 'utf-8');
            dependencyConfig = JSON.parse(data);
        } catch (e) {
            logger.error('Failed to parse the config file. (' + e + ')');
        }
        
        try {
            compiler.compileProject({
                config: dependencyConfig,
                cwd: dependencyDir,
                success: compiledCallback
            });
        } catch (e) {
            logger.error('' + e);
        }
    }
};
