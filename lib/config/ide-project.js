function Class() {
    var name;
    var type;
    var subsystem;
    var uuid;
    var source;
    var configurations = [];
    var paths;
    var precompiledHeader;
    var dependencies = [];

    this.getName = function() {
        return name;
    };

    this.setName = function(value) {
        name = value;
    };

    this.getType = function() {
        return type;
    };

    this.setType = function(value) {
        type = value;
    };

    this.getSubsystem = function() {
        return subsystem;
    };

    this.setSubsystem = function(value) {
        subsystem = value;
    };

    this.getUuid = function() {
        return uuid;
    };

    this.setUuid = function(value) {
        uuid = value;
    };

    this.getSource = function() {
        return source;
    };

    this.setSource = function(value) {
        source = value;
    };

    this.addConfiguration = function(configuration) {
        configurations.push(configuration);
    };

    this.getConfiguration = function(i) {
        return configurations[i];
    };

    this.getConfigurations = function() {
        return configurations;
    };

    this.getPaths = function() {
        return paths;
    };

    this.setPaths = function(value) {
        paths = value;
    };

    this.getPrecompiledHeader = function() {
        return precompiledHeader;
    };

    this.setPrecompiledHeader = function(value) {
        precompiledHeader = value;
    };

    this.addDependency = function(dependency) {
        dependencies.push(dependency);
    };

    this.getDependencies = function() {
        return dependencies;
    };
}

module.exports.Class = Class;