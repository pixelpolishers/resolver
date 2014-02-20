function Class() {
    var logger = require('./logger');
    var program = require('commander');
    var variables = require('./variables');
    var config;

    function loadConfig() {
        config = require('./config/reader-config').load();

        if (config.getVariables()) {
            variables.pushVariableSet(config.getVariables());
        }
    }

    function createCommand(name, args) {
        args = args || '';

        program.on(name, function() {
            if (program.workingDir !== undefined) {
                process.chdir(program.workingDir);
            }

            logger.log('');

            loadConfig();
        });

        return program.command(name + ' ' + args);
    };

    this.getConfig = function() {
        return config;
    };

    this.getProgram = function() {
        return program;
    };

    /**
     * Runs the application.
     *
     * @return void
     */
    this.run = function() {
        var command;

        program.option('-w, --working-dir [path]', 'specifies the working directory');
        program.option('-s, --silent', 'disables all log messages');

        command = createCommand('update');
        command.description('updates the configured dependencies');
        command.action(require('./commands/update').createCommand(this));

        command = createCommand('compile', '[type]');
        command.description('compiles all projects that are configured');
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
