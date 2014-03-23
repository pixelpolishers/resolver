function Class() {
    var name;
    var config;
    var project;
    var dynamic;

    this.getName = function() {
        return name;
    };

    this.setName = function(value) {
        name = value;
    };

    this.getConfig = function() {
        return config;
    };

    this.setConfig = function(value) {
        config = value;
    };

    this.getProject = function() {
        return project;
    };

    this.setProject = function(value) {
        project = value;
    };

    this.getDynamic = function() {
        return dynamic;
    };

    this.setDynamic = function(value) {
        dynamic = value;
    };
}

module.exports.Class = Class;