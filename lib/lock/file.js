function Class() {
    var dependencies = {};

    this.clearDependencies = function() {
        dependencies = {};
    };

    this.getDependencies = function() {
        return dependencies;
    };

    this.setDependency = function(name, version) {
        dependencies[name] = version;
    };

    this.setDependencies = function(value) {
        dependencies = value;
    };
}

module.exports.Class = Class;