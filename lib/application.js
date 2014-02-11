/**
 * The logger.
 */
var logger = require('./logger');

/**
 * The commander module used to define the application.
 * https://github.com/visionmedia/commander.js
 */
var program = require('commander');

/**
 * The loaded configuration.
 *
 * @type Object
 */
var config;

/**
 *
 * @param {type} name
 * @param {type} args
 * @returns {@exp;program@call;command}
 */
var createCommand = function(name, args) {
    args = args || '';

    program.on(name, function() {
        if (program.workingDir !== undefined) {
            process.chdir(program.workingDir);
        }

        logger.log('');

        try {
            var data = require('fs').readFileSync('resolver.json', 'utf-8');

            config = JSON.parse(data);

            if (config.variables) {
                require('./variables').pushVariableSet(config.variables);
            }
        } catch (e) {
            logger.error('Failed to parse the config file. (' + e + ')');
        }
    });

    return program.command(name + ' ' + args);
};

/**
 * Runs the application.
 *
 * @return void
 */
exports.run = function() {
    program.version('0.0.1');
    program.option('-w, --working-dir [path]', 'specifies the working directory');
    program.option('-s, --silent', 'disables all log messages');

    command = createCommand('update');
    command.description('updates the dependencies that this project has');
    command.action(function() {
        if (config) {
            var self = this;
            require('./dependency/installer').run(config, function() {
                self.emit('finished');
            });
        }
    });

    command = createCommand('compile');
    command.option('-n, --nodependencies', 'ignores the compilation of the dependencies');
    command.description('compiles the ide projects that this project has');
    command.action(function() {
        if (config) {
            var self = this,
                compilerFinder = require('./compiler/compiler'),
                compiler = compilerFinder.findCompiler();

            if (!compiler) {
                logger.error('No compiler available.');
                self.emit('finished');
            } else {
                compilerFinder.compileProject(compiler, config, function() {
                    if (!program.args[0].nodependencies) {
                        compilerFinder.compileDependencies(compiler, config, config.dependencies, function() {
                            self.emit('finished');
                        });
                    } else {
                        self.emit('finished');
                    }
                });
            }
        }
    });

    command = createCommand('generate', '[ide]');
    command.description('generates the project files for the provided ide');
    command.action(function(ide) {
        if (config) {
            require('./variables').pushProject(config);

            var self = this;
            require('./generator/generator').generate(ide, config, function() {
                self.emit('finished');
            });
        }
    });

    program.on('finished', function() {
        logger.info('');
        logger.info('The computing scientist\'s main challenge is not to get confused by the complexities of his own making.');
        logger.info('(E. W. Dijkstra)');
    });

    program.parse(process.argv);

    if (process.argv.length < 3) {
        logger.info('Usage:');
        logger.info('  resolver command arguments');
    }
};
