/**
 * The "compile" command.
 *
 * @class CompileCommand
 * @constructor
 * @param {Object} application The application that executes the command.
 */
function Class(application) {
    var CompilerFinderClass = require('../compiler/finder').Class;

    var findCompiler = function() {
        var compilerFinder = new CompilerFinderClass(application),
            CompilerClass = compilerFinder.find();

        if (!CompilerClass) {
            throw 'No compiler available.';
        }

        return new CompilerClass(application);
    };

    var completedCallback = function() {
        application.getProgram().emit('finished');
    };

    this.execute = function(type) {
        var compiler = findCompiler();

        switch (type) {
            case 'projects':
                compiler.compileProjects(completedCallback);
                break;

            case 'dependencies':
                compiler.compileDependencies(completedCallback);
                break;

            default:
                compiler.compileAll(completedCallback);
                break;
        }
    };
}

module.exports.Class = Class;

module.exports.createCommand = function(application) {
    return function(type) {
        var command = new Class(application);
        command.execute(type);
    };
};
