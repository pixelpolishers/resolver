/**
 * The DependencyContainer contains a collection with all dependencies.
 *
 * @class DependencyContainer
 * @constructor
 */
function Class() {
    var dependencies = [];

    this.loadFromConfig = function(config) {
        var projects = config.getProjects();

        for (var i = 0; i < projects.length; ++i) {
            var dependencies = projects[i].getDependencies();

            for (var i = 0; i < dependencies.length; ++i) {
                this.add(dependencies[i]);
            }
        }
    };

    this.add = function(dependency) {
        dependencies.push(dependency);
    };

    this.getDependencies = function() {
        return dependencies;
    };

    this.hasDependencies = function() {
        return dependencies.length !== 0;
    };
}

module.exports.Class = Class;
