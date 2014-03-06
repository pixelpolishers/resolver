function Class() {
    var dependencies = {};
    var DependencyClass = require('./dependency').Class;

    this.clearDependencies = function() {
        dependencies = {};
    };

    this.getDependencies = function() {
        return dependencies;
    };

    this.setDependency = function(name, version) {
        dependencies[name] = new DependencyClass(this);
        dependencies[name].setName(name);
        dependencies[name].setVersion(version);
    };

    this.setDependencies = function(value) {
        dependencies = {};
        for (var name in value) {
            this.setDependency(name, value[name]);
        }
    };
}

module.exports.Class = Class;