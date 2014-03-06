function Class() {
    var name;
    var config;
    var project;

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
}

module.exports.Class = Class;