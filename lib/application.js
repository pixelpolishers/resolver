/**
 * The logger.
 *
 * @var Object
 */
var logger = require('./logger');

/**
 * The commander module used to define the application.
 * https://github.com/visionmedia/commander.js
 *
 * @var Object
 */
var program = require('commander');

/**
 * The loaded configuration.
 * 
 * @type Object
 */
var config;

var createCommand = function(name, args) {
    args = args || '';
    
    program.on(name, function() {
        if (program.workingDir !== undefined) {
            process.chdir(program.workingDir);
        }
        
        logger.log('');
        
        var data = require('fs').readFileSync('resolver.json', 'utf-8');
        
        try {
            config = JSON.parse(data);
        } catch (e) {
            logger.fail('Failed to parse config file. ' + e);
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
    command.description('updates the dependencies that this project has')
    command.action(function() {
        var self = this;
        require('./dependency/installer').run(config, function() {
            self.emit('finished');
        });
    });
    
    program.on('finished', function() {
        logger.info('');
        logger.info('The computing scientist\'s main challenge is not to get confused by the complexities of his own making.');
        logger.info('(E. W. Dijkstra)');
    });
    
    program.parse(process.argv);
};
