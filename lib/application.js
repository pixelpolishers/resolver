function Class() {
    var pathLib = require('path');
    var logger = require('./logger');
    var program = require('commander');
    var variables = require('./variables');

    var ConfigReaderClass = require('./config/reader/json').Class;
    var configReader = new ConfigReaderClass();

    var LockReaderClass = require('./lock/reader/json').Class;
    var lockReader = new LockReaderClass();

    var config;
    var lockFile;

    var configCache = {};

    function loadInternalConfig(path) {
        path = pathLib.resolve(path + '/resolver.json');

        if (!require('fs').existsSync(path)) {
            return null;
        }

        if (!configCache[path]) {
            var cwd = process.cwd();
            process.chdir(pathLib.dirname(path));

            configCache[path] = configReader.read(pathLib.basename(path));

            process.chdir(cwd);
        }

        return configCache[path];
    }

    function loadConfigAndLock() {
        config = loadInternalConfig('./');
        if (config.getVariables()) {
            variables.pushVariableSet(config.getVariables());
        }

        lockFile = lockReader.read('resolver.lock');
    }

    function createCommand(name, args) {
        args = args || '';

        program.on(name, function() {
            if (program.workingDir !== undefined) {
                process.chdir(program.workingDir);
            }

            logger.log('');

            loadConfigAndLock();
        });

        return program.command(name + ' ' + args);
    };

    this.getConfig = function() {
        return config;
    };

    this.getConfigReader = function() {
        return configReader;
    };

    this.getLockFile = function() {
        return lockFile;
    };

    this.getProgram = function() {
        return program;
    };

    this.loadConfig = function(path) {
        return loadInternalConfig(path);
    };

    this.getDependencyDirectory = function(name) {
        return pathLib.resolve(config.getVendorDirectory() + '/' + name);
    };

    this.loadDependencyConfig = function(name) {
        var path = this.getDependencyDirectory(name);

        return this.loadConfig(path);
    };

    this.saveLockFile = function() {
        var WriterClass = require('./lock/writer/json').Class;
        var writer = new WriterClass();
        writer.write(lockFile, 'resolver.lock')
    };

    /**
     * Runs the application.
     *
     * @return void
     */
    this.run = function() {
        var command;

        // A hack to show the name of the application:
        program._name = 'resolver';

        program.option('-w, --working-dir [path]', 'specifies the working directory');
        program.option('-s, --silent', 'disables all log messages');

        command = createCommand('update');
        command.description('updates the configured dependencies');
        command.action(require('./commands/update').createCommand(this));

        command = createCommand('compile');
        command.description('compiles all projects that are configured');
        command.option('-p, --projects', 'compiles only the project');
        command.option('-d, --dependencies', 'compiles only the dependencies');
        command.action(require('./commands/compile').createCommand(this));

        command = createCommand('generate', '[ide]');
        command.description('generates the project files for the provided ide');
        command.action(require('./commands/generate').createCommand(this));

        program.on('finished', function() {
            logger.info('');
            logger.info('The computing scientist\'s main challenge is not to get confused by the complexities of his own making.');
            logger.info('(E. W. Dijkstra)');
        });

        if (process.argv.length < 3) {
            program.help();
        } else {
            try {
                program.parse(process.argv);
            } catch (e) {
                if (e instanceof Error) {
                    throw e;
                } else if (typeof(e) === 'string') {
                    logger.error(e);
                } else {
                    console.log(e);
                }
                process.exit(1);
            }
        }
    };
}

module.exports.Class = Class;
