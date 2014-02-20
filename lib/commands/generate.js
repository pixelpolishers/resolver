/**
 * The "generate" command.
 *
 * @class GenerateCommand
 * @constructor
 * @param {Object} application The application that executes the command.
 */
function Class(application) {
    var logger = require('../logger');
    var variables = require('../variables');
    var config = application.getConfig();

    function findCustomGenerator(name) {
        throw 'Generator "' + name + '" does not exist';
    }

    function findGenerator(name) {
        var result;
        switch (name) {
            case 'vs2010':
                result = require('../generator/vs/vs2010').Class;
                logger.info('Creating project files for Visual Studio 2010...');
                break;

            default:
                result = findCustomGenerator(name);
                break;
        }
        return result;
    }

    function createGenerator(GeneratorClass) {
        // Create the instance of the generator:
        var generator = new GeneratorClass(config);
        variables.setGeneratorName(generator.getName());

        // Create and set the output directory:
        var projectDir = config.getProjectDirectory() || 'build';
        require('mkdirp').mkdirp.sync(projectDir, 0777);
        generator.setProjectDirectory(variables.parse(projectDir));

        return generator;
    }

    this.execute = function(ide) {
        variables.pushProject(config);

        if (!config.hasProjects()) {
            throw 'There are no projects configured.';
        }

        var GeneratorClass = findGenerator(ide);
        if (!GeneratorClass) {
            throw 'The generator ' + ide + ' does not exist.';
        }

        var generator = createGenerator(GeneratorClass);
        generator.generate(function() {
            application.getProgram().emit('finished');
        });

        variables.popProject();
    };
}

module.exports.Class = Class;

module.exports.createCommand = function(application) {
    return function(ide) {
        var command = new Class(application);
        command.execute(ide);
    };
};
