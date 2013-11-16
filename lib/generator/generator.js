/**
 * The logger.
 *
 * @var Object
 */
var logger = require('../logger');

/**
 * The variable parser.
 *
 * @var Object
 */
var variables = require('../variables');

/**
 * Generates the project files for the given generator.
 *
 * @param generators A string containing generators splitted by spaces.
 * @param config The configuration of the project.
 * @param callback The callback method that is called once generating is done.
 * @return void
 */
exports.generate = function(generators, config, callback) {
    var counter = 0, generatorList, finished;
    
    if (!generators) {
        logger.error('No generator provided.');
        callback();
        return;
    }
    
    generatorList = (generators || '').split(' ');
    finished = function() {
        counter++;
        if (counter === generatorList.length) {
            callback();
        }
    };
        
    for (var i = 0; i < generatorList.length; ++i) {
        variables.setGeneratorName('');
        
        switch (generatorList[i]) {
            case 'vs2010':
				logger.info('Creating project files for Visual Studio 2010...');
				require('./vs/vs2010').generate(config, finished);
                break;
                
            default:
                logger.error('Skipped project generation for "' + generatorList[i] + '", unknown generator');
                finished();
                break;
        }
    }
};
