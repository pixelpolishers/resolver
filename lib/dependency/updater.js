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

        logger.log('');
        logger.log('Installing dependency ' + dependency.package.fullname);

        switch (dependency.source.type) {
            case 'git':
                DriverClass = require('./../driver/git').Class;
                break;

            default:
                throw 'Invalid source type provided: ' + dependency.source.type;
        }

        outputPath = config.getVendorDirectory() + '/' + dependency.package.fullname;

        driver = new DriverClass();
        driver.handle(dependency, outputPath, callback);
    };

    var removeDependency = function(dependency, callback) {
        logger.log('');
        logger.log('Removing dependency ' + dependency.package);
        callback();
    };

    var createDependencyCommands = function(json) {
        var commands = [];

        for (var i = 0; i < json.packages.length; ++i) {
            switch (json.packages[i].status) {
                case 200:
                    commands.push({
                        'type': 'download',
                        'dependency': json.packages[i]
                    });
                    break;

                default:
                    commands.push({
                        'type': 'remove',
                        'dependency': json.packages[i]
                    });
                    break;
            }
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

        // Make a request to get the dependency information and its subdependencies.
        // We do this request at once to save time.
        finder.loadDependencies(container.getDependencies(), function(json) {
            if (json) {
                var commands = createDependencyCommands(json);
                executeCommands(commands, callback);
            } else {
                callback();
            }
        });
    };
}

module.exports.Class = Class;
