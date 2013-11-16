/**
 * The logger.
 *
 * @var Object
 */
var logger = require('../logger');

/**
 * The file system module used to communicate with the file system.
 *
 * @var Object
 */
var fs = require('fs');

/**
 * The path module that can resolve paths.
 *
 * @var Object
 */
var path = require('path');

/**
 * Installs the given dependencies.
 *
 * @param dependencies The dependencies to install.
 * @return void
 */
var installDependencies = function(config, dependencies, callback) {
    var dependency;
	
    if (dependencies && dependencies.length) {
        dependency = dependencies.pop();

        installDependency(config, dependency, function(dependency, success) {
            installDependencies(config, dependencies, callback);
        });
    } else if (callback) {
        callback();
    }
};

/**
 * Installs the given dependency.
 *
 * @param dependency The dependency configuration.
 * @return void
 */
var installDependency = function(config, dependency, callback) {
    var vendorDir, outputPath;

    vendorDir = config.vendorDir || 'vendor';
    outputPath = path.resolve(vendorDir + '/' + dependency.vendor + '/' + dependency.name);
	
	switch (dependency.type) {
		case 'git':
			require('../driver/git').handle(dependency, outputPath, function(dependency, success) {
				onDependencyInstalled(dependency, success);
				callback(dependency, success);
			});
			break;
		default:
			logger.error('Repository type not implemented: ' + dependency.type);
			break;
	}
};

/**
 * Called after the dependency has been installed.
 *
 * @param dependency The dependency.
 * @param success Whether or not the dependency was installed successfully.
 * @return void
 */
var onDependencyInstalled = function(dependency, success) {
    if (success) {
        logger.ok('Installed ' + dependency.name);
    } else {
        logger.error('Failed to install ' + dependency.name);
    }
};

/**
 * Runs the installer.
 *
 * @return void
 */
exports.run = function(config, callback) {
    require('./finder').loadDependencies(config, function(dependencies) {
        installDependencies(config, dependencies, function(success) {
            if (callback) {
                callback();
            }
        });
    });
};