/**
 * The DependencyInstaller will collect all dependencies and installs or updates them.
 *
 * @class DependencyInstaller
 * @constructor
 * @param {Object} application The application.
 */
function Class(application) {
    var logger = require('../logger');
    var config = application.getConfig();

    var downloadDependency = function(dependency, callback) {
        var driver, DriverClass, outputPath;

        switch (dependency.source.type) {
            case 'git':
                DriverClass = require('./../driver/git').Class;
                break;

            default:
                throw 'Invalid source type provided: ' + dependency.source.type;
        }

        application.getLockFile().setDependency(dependency.package.fullname, dependency.version);

        outputPath = config.getVendorDirectory() + '/' + dependency.package.fullname;

        driver = new DriverClass();
        driver.handle(dependency, outputPath, callback);
    };

    var removeDependency = function(dependency, callback) {
        var depPath = config.getVendorDirectory() + '/' + dependency;

        logger.log('Removing dependency "' + dependency + '"...');
        require('./../utils').rmdir(depPath);

        callback();
    };

    var hasDependency = function(dependencies, name) {
        for (var i = 0; i < dependencies.length; ++i) {
            if (dependencies[i].package.fullname === name) {
                return true;
            }
        }
        return false;
    };

    var createDependencyCommands = function(container, dependencies) {
        var commands = [], requestedDependencies = container.getDependencies();

        // Remove all non-existing dependencies:
        for (var i = 0; i < requestedDependencies.length; ++i) {
            var dependency = requestedDependencies[i];

            if (!hasDependency(dependencies, dependency.getName())) {
                commands.push({
                    'type': 'remove',
                    'dependency': dependency.getName()
                });
            }
        }

        // Now add or update all the existing dependencies:
        for (var i = 0; i < dependencies.length; ++i) {
            commands.push({
                'type': 'download',
                'dependency': dependencies[i]
            });
        }

        return commands;
    };

    var executeCommands = function(commands, callback) {
        var counter = 0;

        var onActionCompleted = function() {
            counter++;

            if (counter === commands.length) {
                callback();
            }
        };

        for (var i = 0; i < commands.length; ++i) {
            switch (commands[i].type) {
                case 'download':
                    downloadDependency(commands[i].dependency, onActionCompleted);
                    break;

                case 'remove':
                    removeDependency(commands[i].dependency, onActionCompleted);
                    break;
            }
        }
    };

    this.execute = function(callback) {
        var FinderClass = require('./finder').Class;
        var finder = new FinderClass(config.getRepositories());

        // Collect all configured dependencies:
        var ContainerClass = require('./container').Class;
        var container = new ContainerClass();
        container.loadFromConfig(config);

        if (!container.hasDependencies()) {
            logger.error('No dependencies configured.');
            callback();
            return;
        }

        logger.log('Updating dependencies...');

        // Clear the current dependencies:
        application.getLockFile().clearDependencies();

        // Make a request to get the dependency information and its subdependencies.
        // We do this request at once to save time.
        finder.loadDependencies(container.getDependencies(), function(dependencies) {
            if (dependencies) {
                var commands = createDependencyCommands(container, dependencies);
                executeCommands(commands, callback);
            } else {
                callback();
            }
        });
    };
}

module.exports.Class = Class;
