/**
 * The "update" command.
 *
 * @class GenerateCommand
 * @constructor
 * @param {Object} application The application that executes the command.
 */
function Class(application) {
    var logger = require('../logger');
    var config = application.getConfig();

    this.execute = function() {
        var DependencyUpdaterClass = require('../dependency/updater').Class;

        var dependencyUpdater = new DependencyUpdaterClass(application);
        dependencyUpdater.execute(function() {
            application.getProgram().emit('finished');
        });
    };
}

module.exports.Class = Class;

module.exports.createCommand = function(application) {
    return function() {
        var command = new Class(application);
        command.execute();
    };
};
