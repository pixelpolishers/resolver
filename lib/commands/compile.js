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

    this.execute = function(cmd) {
        var compiler = findCompiler();

        if (!cmd.dependencies && !cmd.projects) {
            cmd.dependencies = true;
            cmd.projects = true;
        }

        if (cmd.dependencies) {
            compiler.compileDependencies(function() {
                if (cmd.projects) {
                    compiler.compileProjects(completedCallback);
                }
            });
        } else if (cmd.projects) {
            compiler.compileProjects(completedCallback);
        }
    };
}

module.exports.Class = Class;

module.exports.createCommand = function(application) {
    return function(cmd) {
        var command = new Class(application);
        command.execute(cmd);
    };
};
